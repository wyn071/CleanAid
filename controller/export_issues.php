<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = $_POST['filename'] ?? 'flagged_data.csv';
    $issues = json_decode($_POST['issues'] ?? '[]', true);

    if (!is_array($issues) || empty($issues)) {
        die('❌ No data to export.');
    }

    // Force browser to download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="flagged_' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add CSV headers
    $headers = array_keys(reset($issues));
    if (!in_array('Reason(s)', $headers)) {
        $headers[] = 'Reason(s)';
    }
    fputcsv($output, $headers);

    // Add rows
    foreach ($issues as $row) {
        $rowValues = [];
        foreach ($headers as $key) {
            $rowValues[] = $row[$key] ?? '';
        }
        fputcsv($output, $rowValues);
    }

    fclose($output);
    exit;
} else {
    echo "Invalid access.";
}
