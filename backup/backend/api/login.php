<?php

// Mengaktifkan session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Header CORS agar dapat diakses dari browser/frontend dan Postman
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Tangani preflight request OPTIONS
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// 1. Memastikan request menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Metode request tidak diizinkan.");
}

// Sambungkan ke database
require_once __DIR__ . "/../config/connection.php";

// 2. Mengambil data dari Postman (form-data / x-www-form-urlencoded / raw JSON) atau form HTML
$input = $_POST;
$rawBody = file_get_contents("php://input");
if (!empty($rawBody)) {
    $jsonData = json_decode($rawBody, true);
    if (is_array($jsonData)) {
        $input = array_merge($input, $jsonData);
    }
}

// Mendukung identifier melalui username, email, nis, atau nisn
$username = trim($input["username"] ?? $input["email"] ?? $input["nis"] ?? $input["nisn"] ?? $input["identifier"] ?? "");
$password = (string)($input["password"] ?? "");

// 3. Validasi input
if ($username === "" || $password === "") {
    http_response_code(400);
    exit("Username dan password wajib diisi.");
}

// 4. Mencari pengguna berdasarkan username, email, atau NISN (via tabel students)
$stmt = $conn->prepare(
    "SELECT u.id, u.username, u.email, u.password_hash, u.full_name, u.role, u.status,
            s.id AS student_id, s.nisn, s.class_name, s.recipient_status, s.profile_picture,
            sc.id AS school_id, sc.name AS school_name
     FROM users u
     LEFT JOIN students s ON s.user_id = u.id
     LEFT JOIN schools sc ON sc.id = s.school_id
     WHERE u.username = ? OR u.email = ? OR s.nisn = ?
     LIMIT 1"
);

if (!$stmt) {
    http_response_code(500);
    exit("Terjadi kesalahan pada sistem: " . $conn->error);
}

$stmt->bind_param("sss", $username, $username, $username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 5. Memeriksa password (mendukung bcrypt hash maupun plain-text untuk testing dan seed data)
$passwordMatches = false;
if ($user) {
    if (password_verify($password, $user["password_hash"])) {
        $passwordMatches = true;
    } else if ($password === $user["password_hash"]) {
        $passwordMatches = true;
    }
}

if (!$user || !$passwordMatches) {
    http_response_code(401);
    $stmt->close();
    $conn->close();
    exit("Username atau password salah.");
}

// Periksa status akun
if (isset($user["status"]) && $user["status"] === "inactive") {
    http_response_code(403);
    $stmt->close();
    $conn->close();
    exit("Akun tidak aktif.");
}

// 6. Memeriksa dan menormalisasi role pengguna
$rawRole = strtolower(trim($user["role"] ?? ""));
// Normalisasi role: jika di database bernilai 'student', dinormalisasi ke 'siswa'
$role = ($rawRole === "student") ? "siswa" : $rawRole;

$allowedRoles = ["siswa", "sppg", "admin"];
if (!in_array($role, $allowedRoles, true)) {
    http_response_code(403);
    $stmt->close();
    $conn->close();
    exit("Role akun tidak valid.");
}

// 7. Membuat session login
session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["full_name"] = $user["full_name"] ?? $user["username"];
$_SESSION["role"] = $role;
if (!empty($user["student_id"])) {
    $_SESSION["student_id"] = (int)$user["student_id"];
}

// 8. Menutup koneksi
$stmt->close();
$conn->close();

// 9. Memberikan respons (format JSON jika diminta, atau teks standar)
$isJsonRequest = (
    (isset($_SERVER["HTTP_ACCEPT"]) && str_contains($_SERVER["HTTP_ACCEPT"], "application/json")) ||
    (isset($_GET["format"]) && $_GET["format"] === "json")
);

if ($isJsonRequest) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "status" => "success",
        "message" => "Login berhasil.",
        "role" => $role,
        "data" => [
            "id" => $user["id"],
            "student_id" => !empty($user["student_id"]) ? (int)$user["student_id"] : null,
            "username" => $user["username"],
            "full_name" => $user["full_name"] ?? $user["username"],
            "email" => $user["email"],
            "role" => $role,
            "nis" => $user["nisn"] ?? "",
            "nisn" => $user["nisn"] ?? "",
            "school" => $user["school_name"] ?? "",
            "school_id" => $user["school_id"] ?? null,
            "class" => $user["class_name"] ?? "",
            "profile_picture" => $user["profile_picture"] ?? ""
        ]
    ]);
} else {
    echo "Login berhasil. Role: " . htmlspecialchars($role, ENT_QUOTES, "UTF-8");
}

exit;
