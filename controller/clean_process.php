<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . "/../../dB/config.php"; // $conn = mysqli or $pdo = PDO

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// CONFIG
const BATCH_SIZE = 5000; // adjust to 10000–20000 per run
const PY_TIMEOUT = 60;

function now(): string {
    return date('Y-m-d H:i:s');
}

// --- UTIL ---
function getTotalRows(mysqli $conn, int $listId): int {
    $sql = "SELECT COUNT(*) AS c FROM beneficiary WHERE list_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $listId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)$res['c'];
}

function getBatch(mysqli $conn, int $listId, int $offset, int $limit): array {
    $sql = "SELECT id, first_name, middle_name, last_name, dob, gender, address
            FROM beneficiary
            WHERE list_id = ?
            ORDER BY id
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $listId, $limit, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_all(MYSQLI_ASSOC);
}

function startProcessing(mysqli $conn, int $listId): int {
    // create new processing_engine row
    $sql = "INSERT INTO processing_engine (list_id, processing_date, status) VALUES (?, ?, 'running')";
    $stmt = $conn->prepare($sql);
    $dt = now();
    $stmt->bind_param("is", $listId, $dt);
    $stmt->execute();
    return $conn->insert_id;
}

function markStatus(mysqli $conn, int $processingId, string $status): void {
    $sql = "UPDATE processing_engine SET status=? WHERE processing_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $processingId);
    $stmt->execute();
}

function runPython(array $rows): array {
    $cmds = [
        "python3 clean_data.py",
        "python clean_data.py",
        "py -3 clean_data.py"
    ];
    foreach ($cmds as $cmd) {
        $descriptors = [0=>["pipe","r"], 1=>["pipe","w"], 2=>["pipe","w"]];
        $proc = proc_open($cmd, $descriptors, $pipes, __DIR__);
        if (!is_resource($proc)) continue;

        fwrite($pipes[0], json_encode($rows));
        fclose($pipes[0]);

        stream_set_blocking($pipes[1], true);
        stream_set_blocking($pipes[2], true);
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);

        $exit = proc_close($proc);
        if ($exit === 0) {
            $decoded = json_decode($out, true);
            if (is_array($decoded)) return $decoded;
        }
    }
    throw new RuntimeException("Python failed");
}

function insertFlagged(mysqli $conn, int $processingId, int $listId, array $flagged): int {
    if (!$flagged) return 0;
    $sql = "INSERT INTO duplicaterecord (beneficiary_id, processing_id, flagged_reason, status)
            VALUES (?, ?, ?, 'flagged')";
    $stmt = $conn->prepare($sql);

    $count = 0;
    foreach ($flagged as $row) {
        $bid = $row['id'];
        $reason = $row['reason'] ?? 'duplicate';
        $stmt->bind_param("iis", $bid, $processingId, $reason);
        $stmt->execute();
        $count++;
    }
    return $count;
}

// --- MAIN ---
$listId = isset($_GET['list_id']) ? (int)$_GET['list_id'] : 0;
if ($listId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Missing list_id']);
    exit;
}

// Start a new run
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $processingId = startProcessing($conn, $listId);
    echo json_encode(['status'=>'running','processing_id'=>$processingId]);
    exit;
}

// Poll progress
if (isset($_GET['progress'])) {
    // Get latest processing row for this list
    $res = $conn->query("SELECT * FROM processing_engine WHERE list_id={$listId} ORDER BY processing_id DESC LIMIT 1");
    if (!$res || $res->num_rows===0) {
        echo json_encode(['status'=>'error','message'=>'No processing run found']);
        exit;
    }
    $run = $res->fetch_assoc();
    $processingId = (int)$run['processing_id'];

    if ($run['status']==='complete') {
        echo json_encode(['status'=>'complete','percent'=>100]);
        exit;
    }

    // figure out how many already flagged
    $doneRes = $conn->query("SELECT COUNT(*) AS c FROM duplicaterecord WHERE processing_id={$processingId}");
    $doneCount = (int)$doneRes->fetch_assoc()['c'];

    $total = getTotalRows($conn, $listId);
    $offset = $doneCount; // assume processed ~ flagged (approx)
    if ($offset >= $total) {
        markStatus($conn, $processingId, 'complete');
        echo json_encode(['status'=>'complete','percent'=>100]);
        exit;
    }

    $batch = getBatch($conn, $listId, $offset, BATCH_SIZE);
    if (!$batch) {
        markStatus($conn, $processingId, 'complete');
        echo json_encode(['status'=>'complete','percent'=>100]);
        exit;
    }

    // send to python
    try {
        $result = runPython($batch);
    } catch (Throwable $e) {
        markStatus($conn, $processingId, 'error');
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
        exit;
    }

    $flagged = [];
    if (!empty($result['missing_data'])) {
        foreach ($result['missing_data'] as $r) {
            $r['reason'] = 'missing_data';
            $flagged[] = $r;
        }
    }
    foreach (['exact_duplicates','fuzzy_duplicates','sounds_like_duplicates'] as $ptype) {
        if (!empty($result[$ptype])) {
            foreach ($result[$ptype] as $pair) {
                if (isset($batch[$pair['row1_index']])) {
                    $row = $batch[$pair['row1_index']];
                    $row['reason'] = $ptype;
                    $flagged[] = $row;
                }
                if (isset($batch[$pair['row2_index']])) {
                    $row = $batch[$pair['row2_index']];
                    $row['reason'] = $ptype;
                    $flagged[] = $row;
                }
            }
        }
    }

    $flaggedCount = insertFlagged($conn, $processingId, $listId, $flagged);

    $processed = min($offset + count($batch), $total);
    $percent = $total > 0 ? floor(($processed / $total) * 100) : 100;

    if ($processed >= $total) {
        markStatus($conn, $processingId, 'complete');
    }

    echo json_encode([
        'status'=>'running',
        'percent'=>$percent,
        'processed'=>$processed,
        'total'=>$total,
        'flagged'=>$flaggedCount
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['status'=>'error','message'=>'Invalid request']);
