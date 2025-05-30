<?php
session_start();

// Check user session and flagged records
if (!isset($_SESSION['user_id']) || empty($_SESSION['flagged_records'])) {
    echo "Unauthorized access or no flagged data to export.";
    exit();
}

// Set CSV headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="flagged_issues.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Optional: Add UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

// Column headers
fputcsv($output, [
    'Beneficiary ID',
    'Full Name',
    'Birth Date',
    'Barangay',
    'Region',
    'Province',
    'City',
    'Marital Status',
    'Flagged Reason(s)'
]);

// Loop through flagged records and write to CSV
foreach ($_SESSION['flagged_records'] as $record) {
    fputcsv($output, [
        $record['beneficiary_id'],
        trim($record['first_name'] . ' ' . $record['last_name']),
        $record['birth_date'],
        $record['barangay'],
        $record['region'],
        $record['province'],
        $record['city'],
        $record['marital_status'],
        implode("; ", array_unique($record['reasons']))
    ]);
}

fclose($output);
exit();
