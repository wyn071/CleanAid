<?php
// dB/config.php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// $servername = "localhost";
// $username   = "root";
// $password   = "";
// $database   = "cleanaid";

$servername = "mysql.railway.internal";
$username   = "root";
$password   = "gMhexrzIPxuhRNOziuCWwTTJEsGXdvQy";
$database   = "railway";
$port      = 3306;

try {
    // $conn = new mysqli($servername, $username, $password, $database);
    $conn = new mysqli($servername, $username, $password, $database, $port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die("Database connection failed.");
}
`
//MYSQL_URL (from Railway):
//mysql://root:gMhexrzIPxuhRNOziuCWwTTJEsGXdvQy@mysql.railway.internal:3306/railway