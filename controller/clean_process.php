<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . "/../../dB/config.php"; // $pdo (PDO to MariaDB)

header('Content-Type: application/json');

// --- Config ---
@ini_set('memory_limit', '1024M');
@ini_set('max_execution_time', '0');
const SOURCE_BATCH = 20000;   // 10k–20k recommended
const INSERT_BATCH = 10000;   // insert flagged rows in 10k chunks
const PYTHON_TIMEOUT = 60;

// --- Helpers ---
function now(): string { return date('Y-m-d H:i:s'); }
function int_from($arr, $key, $def=null) { return isset($arr[$key]) && $arr[$key] !== '' ? (int)$arr[$key] : $def; }
function json_body(): array { $r = file_get_contents('php://input'); return $r ? (json_decode($r, true) ?: []) : []; }

function latest_processing_row(PDO $pdo, int $listId): ?array {
    // latest by processing_id (AUTO_INCREMENT)
    $q = $pdo->prepare("SELECT * FROM processing_engine WHERE list_id=? ORDER BY processing_id DESC LIMIT 1");
    $q->execute([$listId]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}

function create_processing_row(PDO $pdo, int $listId): array {
    $ins = $pdo->prepare("INSERT INTO processing_engine (list_id, processing_date, status) VALUES (?, CURDATE(), 'running')");
    $ins->execute([$listId]);
    $pid = (int)$pdo->lastInsertId();
    return ['processing_id'=>$pid,'list_id'=>$listId,'processing_date'=>date('Y-m-d'),'status'=>'running'];
}

function mark_status(PDO $pdo, int $processingId, string $status): void {
    $u = $pdo->prepare("UPDATE processing_engine SET status=? WHERE processing_id=?");
    $u->execute([$status, $processingId]);
}

function run_python(array $rows): array {
    $cmds = ["python3 clean_data.py", "python clean_data.py", "py -3 clean_data.py"];
    $lastErr = null;
    foreach ($cmds as $cmd) {
        $spec = [0=>["pipe","r"], 1=>["pipe","w"], 2=>["pipe","w"]];
        $proc = proc_open($cmd, $spec, $pipes, __DIR__);
        if (!is_resource($proc)) { $lastErr = "failed to start: $cmd"; continue; }

        fwrite($pipes[0], json_encode($rows));
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $start = time(); $out=''; $err='';
        while (true) {
            $out .= stream_get_contents($pipes[1]);
            $err .= stream_get_contents($pipes[2]);
            $st = proc_get_status($proc);
            if (!$st['running']) break;
            if (time() - $start > PYTHON_TIMEOUT) {
                proc_terminate($proc, 9);
                $err .= "\nTimed out after ".PYTHON_TIMEOUT."s";
                break;
            }
            usleep(100000);
        }
        fclose($pipes[1]); fclose($pipes[2]);
        $code = proc_close($proc);

        if ($code === 0) {
            $decoded = json_decode($out, true);
            if (!is_array($decoded)) { $lastErr = "invalid JSON from: $cmd"; continue; }
            return $decoded;
        }
        $lastErr = "python exited $code using: $cmd; stderr: $err";
    }
    throw new RuntimeException($lastErr ?? "cannot run python");
}

function load_source_batch(PDO $pdo, int $listId, int $offset, int $limit): array {
    $sql = "SELECT beneficiary_id, first_name, last_name, middle_name, ext_name,
                   birth_date, region, province, city, barangay, marital_status
            FROM beneficiary
            WHERE list_id=?
            ORDER BY beneficiary_id
            LIMIT ? OFFSET ?";
    $st = $pdo->prepare($sql);
    $st->bindValue(1, $listId, PDO::PARAM_INT);
    $st->bindValue(2, $limit,  PDO::PARAM_INT);
    $st->bindValue(3, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function total_beneficiaries(PDO $pdo, int $listId): int {
    $st = $pdo->prepare("SELECT COUNT(*) FROM beneficiary WHERE list_id=?");
    $st->execute([$listId]);
    return (int)$st->fetchColumn();
}

function analyze_and_flag(array $rows): array {
    // Normalize fields for clean_data.py (expects birth_date, region/province/city/barangay, names)
    foreach ($rows as &$r) {
        // nothing to map: your table already uses `birth_date`
        foreach (['region','province','city','barangay','ext_name','middle_name'] as $k) {
            if (!array_key_exists($k, $r)) $r[$k] = '';
        }
        foreach (['first_name','last_name'] as $k) {
            if (!array_key_exists($k, $r)) $r[$k] = '';
        }
    } unset($r);

    $res = run_python($rows);
    if (isset($res['error'])) throw new RuntimeException("Python error: ".$res['error']);

    // Map: gather beneficiary_ids to flag + reasons
    $flagged = [];         // [beneficiary_id => reason]
    $appendPair = function(int $i, int $j, string $type) use (&$rows,&$flagged) {
        if (isset($rows[$i])) $flagged[(int)$rows[$i]['beneficiary_id']] = $type;
        if (isset($rows[$j])) $flagged[(int)$rows[$j]['beneficiary_id']] = $type;
    };

    // Missing data are full rows; mark them
    if (!empty($res['missing_data'])) {
        // Build a quick index: name+dob etc → beneficiary_id
        // Better: scan originals to find matching by beneficiary_id position
        // Here, we match by a composite when possible; fallback: linear scan (batch-sized).
        foreach ($res['missing_data'] as $mr) {
            // Try to locate same row in $rows by beneficiary_id if present (not provided); fallback slow match:
            $hitId = null;
            foreach ($rows as $idx => $or) {
                if (
                    ($mr['first_name'] ?? '') === ($or['first_name'] ?? '') &&
                    ($mr['last_name']  ?? '') === ($or['last_name']  ?? '') &&
                    ($mr['birth_date'] ?? '') === ($or['birth_date'] ?? '')
                ) { $hitId = (int)$or['beneficiary_id']; break; }
            }
            if ($hitId !== null) $flagged[$hitId] = 'missing_data';
        }
    }

    // Pairs by index refer to the batch order
    foreach (['exact_duplicates'=>'exact_duplicate', 'fuzzy_duplicates'=>'fuzzy_duplicate', 'sounds_like_duplicates'=>'sounds_like'] as $k => $label) {
        if (!empty($res[$k])) {
            foreach ($res[$k] as $pair) {
                $i = (int)($pair['row1_index'] ?? -1);
                $j = (int)($pair['row2_index'] ?? -1);
                if ($i >= 0 && $j >= 0) $appendPair($i, $j, $label);
            }
        }
    }

    return $flagged; // beneficiary_id => reason
}

function insert_flags(PDO $pdo, int $processingId, array $beneficiaryIdToReason): int {
    if (!$beneficiaryIdToReason) return 0;

    // Avoid duplicates in duplicaterecord for the same processing run
    // Fetch already inserted beneficiary_ids for this processing_id
    $existing = [];
    $st = $pdo->prepare("SELECT beneficiary_id FROM duplicaterecord WHERE processing_id=?");
    $st->execute([$processingId]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN, 0) as $bid) $existing[(int)$bid] = true;

    $cols = ['beneficiary_id','processing_id','flagged_reason','status'];
    $tpl  = '(' . rtrim(str_repeat('?,', count($cols)), ',') . ')';
    $sqlPrefix = 'INSERT INTO duplicaterecord ('.implode(',', $cols).') VALUES ';

    $buffer = []; $params = []; $rows = 0;
    $flush = function() use (&$buffer,&$params,&$rows,$sqlPrefix,$pdo) {
        if (!$buffer) return;
        $sql = $sqlPrefix . implode(',', $buffer);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows += count($buffer);
        $buffer = []; $params = [];
    };

    foreach ($beneficiaryIdToReason as $bid => $reason) {
        if (isset($existing[$bid])) continue; // skip already-inserted
        $buffer[] = $tpl;
        $params[] = (int)$bid;
        $params[] = (int)$processingId;
        $params[] = $reason;
        $params[] = 'flagged';
        if (count($buffer) === INSERT_BATCH) $flush();
    }
    $flush();
    return $rows;
}

// --- Entry ---
$qs = $_GET ?? [];
$body = $_SERVER['REQUEST_METHOD']==='POST' ? json_body() : [];
$listId = int_from($_GET, 'list_id', int_from($body, 'list_id', 0));
if ($listId <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Missing list_id']); exit;
}

// Initialize session progress basket
$_SESSION['clean'] ??= [];
$_SESSION['clean'][$listId] ??= [];

// Optional POST {action:start}
if (isset($body['action']) && $body['action'] === 'start') {
    try {
        // Start/continue a run: create new processing row if last one isn't 'running'
        $cur = latest_processing_row($pdo, $listId);
        if (!$cur || ($cur['status'] ?? '') !== 'running') {
            $cur = create_processing_row($pdo, $listId);
            // Clear prior flags for this list’s previous runs? (Keep history by default)
            // If you want fresh-only: $pdo->prepare("DELETE dr FROM duplicaterecord dr JOIN processing_engine pe ON pe.processing_id=dr.processing_id WHERE pe.list_id=?")->execute([$listId]);
            $_SESSION['clean'][$listId] = ['offset'=>0, 'total'=>total_beneficiaries($pdo,$listId), 'processing_id'=>$cur['processing_id']];
        } else {
            // Resume
            if (!isset($_SESSION['clean'][$listId]['processing_id'])) {
                $_SESSION['clean'][$listId] = ['offset'=>0, 'total'=>total_beneficiaries($pdo,$listId), 'processing_id'=>$cur['processing_id']];
            }
        }
        echo json_encode(['status'=>'running','percent'=>0]); exit;
    } catch (Throwable $e) {
        mark_status($pdo, $cur['processing_id'] ?? 0, 'error');
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]); exit;
    }
}

// Polling does one batch of work per request
if (isset($qs['progress'])) {
    try {
        $cur = latest_processing_row($pdo, $listId);
        if (!$cur || ($cur['status'] ?? '') === 'complete') {
            echo json_encode(['status'=>'complete','percent'=>100]); exit;
        }
        if (($cur['status'] ?? '') === 'error') {
            echo json_encode(['status'=>'error','percent'=>0]); exit;
        }
        $procId = (int)$cur['processing_id'];

        // Session progress
        $prog =& $_SESSION['clean'][$listId];
        if (!isset($prog['processing_id']) || $prog['processing_id'] !== $procId) {
            $prog = ['offset'=>0, 'total'=>total_beneficiaries($pdo,$listId), 'processing_id'=>$procId];
        }

        $offset = (int)$prog['offset'];
        $total  = (int)$prog['total'];
        if ($total === 0) {
            mark_status($pdo, $procId, 'complete');
            echo json_encode(['status'=>'complete','percent'=>100]); exit;
        }

        $batch = load_source_batch($pdo, $listId, $offset, SOURCE_BATCH);
        if (!$batch) {
            mark_status($pdo, $procId, 'complete');
            echo json_encode(['status'=>'complete','percent'=>100]); exit;
        }

        // Analyze and collect beneficiary_ids to flag
        $idToReason = analyze_and_flag($batch);

        // Insert flags in DB in 10k groups (within one transaction for atomicity)
        $pdo->beginTransaction();
        try {
            insert_flags($pdo, $procId, $idToReason);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            mark_status($pdo, $procId, 'error');
            throw $e;
        }

        // Advance offset and compute percent
        $prog['offset'] = $offset + count($batch);
        $percent = min(100, (int)floor(($prog['offset'] / $total) * 100));

        // If finished
        if ($prog['offset'] >= $total) {
            mark_status($pdo, $procId, 'complete');
            echo json_encode(['status'=>'complete','percent'=>100]); exit;
        }

        echo json_encode([
            'status'   => 'running',
            'percent'  => $percent,
            'processed'=> $prog['offset'],
            'total'    => $total
        ]); exit;

    } catch (Throwable $e) {
        // Mark current run as error (best effort)
        $cur = latest_processing_row($pdo, $listId);
        if ($cur) mark_status($pdo, (int)$cur['processing_id'], 'error');
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]); exit;
    }
}

// Fallback
http_response_code(400);
echo json_encode(['status'=>'error','message'=>'Invalid request.']);
