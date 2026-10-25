<?php
$host = 'localhost';
$dbname = 'sim_mbg';
$port = 3306;

// Mencegah uncaught exception mysqli di PHP 8.1+
mysqli_report(MYSQLI_REPORT_OFF);

// Daftar kredensial yang didukung (default root atau user lokal)
$authAttempts = [
    ['user' => 'root', 'pass' => ''],
    ['user' => 'edy', 'pass' => '123']
];

$conn = null;
$lastError = '';

foreach ($authAttempts as $cred) {
    $c = @new mysqli($host, $cred['user'], $cred['pass'], $dbname, (int)$port);
    if (!$c->connect_error) {
        $conn = $c;
        break;
    }
    $lastError = $c->connect_error;
}

if (!$conn || $conn->connect_error) {
    http_response_code(500);
    if ((isset($_SERVER["HTTP_ACCEPT"]) && str_contains($_SERVER["HTTP_ACCEPT"], "application/json")) ||
        (isset($_GET["format"]) && $_GET["format"] === "json") ||
        (isset($_GET["action"]))) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode([
            "status" => "error",
            "message" => "Koneksi database MySQL gagal: " . $lastError
        ]);
    } else {
        echo "Koneksi database gagal: " . htmlspecialchars($lastError);
    }
    exit;
}

$conn->set_charset("utf8mb4");
