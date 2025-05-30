<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['flagged_records'])) {
    echo "Unauthorized or no data available.";
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="flagged_issues.csv"');

$output = fopen("php://output", "w");

// Header row
fputcsv($output, [
    'Beneficiary ID', 'Full Name', 'Birth Date', 'Barangay',
    'Region', 'Province', 'City', 'Marital Status', 'Flagged Reason(s)'
]);

foreach ($_SESSION['flagged_records'] as $record) {
    fputcsv($output, [
        $record['beneficiary_id'],
        $record['first_name'] . ' ' . $record['last_name'],
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
