<?php
session_start();
include("../../dB/config.php");
include("./includes/header.php");

// 🔐 Ensure user is logged in BEFORE including any layout
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Unauthorized access.";
    $_SESSION['code'] = "error";
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle the upload BEFORE output is sent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExtension === 'csv') {
        if (($handle = fopen($fileTmpPath, 'r')) !== false) {
            fgetcsv($handle); // Skip header
            $existingListIds = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) < 12) continue;

                $beneficiary_id = trim($data[0]);
                $list_id = trim($data[1]);

                if (!in_array($list_id, $existingListIds)) {
                    $check = $conn->prepare("SELECT 1 FROM beneficiarylist WHERE list_id = ?");
                    $check->bind_param("s", $list_id);
                    $check->execute();
                    $checkResult = $check->get_result();

                    if ($checkResult->num_rows === 0) {
                        $status = 'uploaded'; // Use a consistent initial status
                        $insertList = $conn->prepare("INSERT INTO beneficiarylist (list_id, user_id, fileName, date_submitted, status) VALUES (?, ?, ?, NOW(), ?)");
                        $insertList->bind_param("siss", $list_id, $user_id, $fileName, $status);
                        $insertList->execute();
                    }

                    $existingListIds[] = $list_id;
                }

                // Optional: Check for existing beneficiary before insert to avoid duplicates

                $stmt = $conn->prepare("INSERT INTO beneficiary (
                    list_id, first_name, last_name, middle_name, ext_name,
                    birth_date, region, province, city, barangay, marital_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "sssssssssss",
                    $list_id,
                    $data[2], $data[3], $data[4], $data[5], $data[6],
                    $data[7], $data[8], $data[9], $data[10], $data[11]
                );

                $stmt->execute();
            }

            fclose($handle);
            $_SESSION['uploaded_filename'] = $fileName;
            $_SESSION['message'] = "File uploaded successfully!";
            $_SESSION['code'] = "success";
            header("Location: clean.php");
            exit();
        } else {
            $_SESSION['message'] = "Failed to open the uploaded file.";
            $_SESSION['code'] = "error";
        }
    } else {
        $_SESSION['message'] = "Only CSV files are allowed.";
        $_SESSION['code'] = "warning";
    }
}

// Now safe to include layout files
include("./includes/topbar.php");
include("./includes/sidebar.php");
?>

<main id="main" class="main">
  <section class="container py-5">
    <h2 class="fw-bold mb-4">Data Upload</h2>
    <p class="text-muted mb-4">Upload your CSV file for beneficiary data processing</p>

    <form method="POST" enctype="multipart/form-data" action="upload.php">
      <div class="border border-dashed rounded-3 p-5 text-center bg-light">
        <input type="file" name="file[]" accept=".csv" class="form-control mb-3" required multiple>
        <button type="submit" class="btn btn-primary">Upload File</button>
      </div>
    </form>
  </section>
</main>

<?php include("./includes/footer.php"); ?>
