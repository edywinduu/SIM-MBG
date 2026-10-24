<?php

// Mengaktifkan session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Header CORS agar dapat diakses dari browser/frontend dan Postman
header("Access-Control-Allow-Origin: *");
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

// Deteksi apakah klien meminta respons JSON
$isJsonRequest = (
    (isset($_SERVER["HTTP_ACCEPT"]) && str_contains($_SERVER["HTTP_ACCEPT"], "application/json")) ||
    (isset($_GET["format"]) && $_GET["format"] === "json")
);

/**
 * Helper untuk mengirimkan error dan menutup koneksi
 */
function sendError($statusCode, $message, $conn = null, $stmt = null) {
    global $isJsonRequest;
    if ($stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if ($conn instanceof mysqli) {
        $conn->close();
    }
    http_response_code($statusCode);
    if ($isJsonRequest) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode([
            "status" => "error",
            "message" => $message
        ]);
    } else {
        echo $message;
    }
    exit;
}

// 3. Mengambil dan memvalidasi input dari formulir registrasi
// Data Langkah 1: Sekolah, NIS/NISN, Kelas
$schoolInput = trim($input["school"] ?? $input["school_name"] ?? $input["school_id"] ?? "");
$nis = trim($input["nis"] ?? $input["nisn"] ?? "");
$className = trim($input["class"] ?? $input["class_name"] ?? "");

// Data Langkah 2: Username, Email, Password, Konfirmasi Kata Sandi, Nama Lengkap
$username = trim($input["username"] ?? "");
$email = trim($input["email"] ?? "");
$password = (string)($input["password"] ?? "");
$confirmPassword = (string)($input["confirmPassword"] ?? $input["confirm_password"] ?? "");
$fullName = trim($input["full_name"] ?? $input["name"] ?? "");
if ($fullName === "") {
    $fullName = $username;
}

// Validasi field wajib diisi
if ($schoolInput === "") {
    sendError(400, "Asal sekolah wajib dipilih.", $conn);
}
if ($nis === "") {
    sendError(400, "Nomor Induk Siswa (NIS/NISN) wajib diisi.", $conn);
}
if ($className === "") {
    sendError(400, "Kelas wajib dipilih.", $conn);
}
if ($username === "") {
    sendError(400, "Nama pengguna (username) wajib diisi.", $conn);
}
if ($email === "") {
    sendError(400, "Alamat email wajib diisi.", $conn);
}
if ($password === "") {
    sendError(400, "Kata sandi wajib diisi.", $conn);
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError(400, "Format alamat email tidak valid.", $conn);
}

// Validasi panjang kata sandi
if (strlen($password) < 8) {
    sendError(400, "Kata sandi minimal 8 karakter.", $conn);
}

// Validasi kecocokan konfirmasi kata sandi jika disertakan
if ($confirmPassword !== "" && $password !== $confirmPassword) {
    sendError(400, "Konfirmasi kata sandi tidak cocok. Harap periksa kembali.", $conn);
}

// 4. Memeriksa duplikasi pada database
// 4.1 Cek apakah username sudah digunakan di tabel users
$stmtCheckUser = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
if (!$stmtCheckUser) {
    sendError(500, "Terjadi kesalahan sistem: " . $conn->error, $conn);
}
$stmtCheckUser->bind_param("s", $username);
$stmtCheckUser->execute();
$stmtCheckUser->store_result();
if ($stmtCheckUser->num_rows > 0) {
    sendError(409, "Nama pengguna (username) sudah digunakan. Silakan pilih username lain.", $conn, $stmtCheckUser);
}
$stmtCheckUser->close();

// 4.2 Cek apakah email sudah terdaftar di tabel users
$stmtCheckEmail = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
if (!$stmtCheckEmail) {
    sendError(500, "Terjadi kesalahan sistem: " . $conn->error, $conn);
}
$stmtCheckEmail->bind_param("s", $email);
$stmtCheckEmail->execute();
$stmtCheckEmail->store_result();
if ($stmtCheckEmail->num_rows > 0) {
    sendError(409, "Alamat email sudah terdaftar. Silakan gunakan email lain atau langsung masuk.", $conn, $stmtCheckEmail);
}
$stmtCheckEmail->close();

// 4.3 Cek apakah NIS/NISN sudah terdaftar di tabel students
$stmtCheckNis = $conn->prepare("SELECT id FROM students WHERE nisn = ? LIMIT 1");
if (!$stmtCheckNis) {
    sendError(500, "Terjadi kesalahan sistem: " . $conn->error, $conn);
}
$stmtCheckNis->bind_param("s", $nis);
$stmtCheckNis->execute();
$stmtCheckNis->store_result();
if ($stmtCheckNis->num_rows > 0) {
    sendError(409, "Nomor Induk Siswa (NIS/NISN) sudah terdaftar dalam sistem.", $conn, $stmtCheckNis);
}
$stmtCheckNis->close();

// 5. Mencocokkan data sekolah pada tabel schools
$schoolId = null;
$schoolName = $schoolInput;

if (is_numeric($schoolInput)) {
    $stmtSchool = $conn->prepare("SELECT id, name FROM schools WHERE id = ? LIMIT 1");
    if ($stmtSchool) {
        $stmtSchool->bind_param("i", $schoolInput);
        $stmtSchool->execute();
        $schoolRes = $stmtSchool->get_result();
        if ($schoolRow = $schoolRes->fetch_assoc()) {
            $schoolId = (int)$schoolRow["id"];
            $schoolName = $schoolRow["name"];
        }
        $stmtSchool->close();
    }
} else {
    $stmtSchool = $conn->prepare("SELECT id, name FROM schools WHERE name = ? OR name LIKE ? LIMIT 1");
    if ($stmtSchool) {
        $likeSchool = "%" . $schoolInput . "%";
        $stmtSchool->bind_param("ss", $schoolInput, $likeSchool);
        $stmtSchool->execute();
        $schoolRes = $stmtSchool->get_result();
        if ($schoolRow = $schoolRes->fetch_assoc()) {
            $schoolId = (int)$schoolRow["id"];
            $schoolName = $schoolRow["name"];
        }
        $stmtSchool->close();
    }
}

// Fallback jika ID sekolah belum ditemukan di database
if (!$schoolId) {
    $resDefault = $conn->query("SELECT id, name FROM schools LIMIT 1");
    if ($resDefault && ($defRow = $resDefault->fetch_assoc())) {
        $schoolId = (int)$defRow["id"];
        if (empty($schoolName)) {
            $schoolName = $defRow["name"];
        }
    } else {
        // Buat sekolah default jika tabel schools masih kosong
        $conn->query("INSERT INTO schools (npsn, name, education_level, district, city, sppg_id, status) VALUES ('50100000', 'SMA Negeri 1 Denpasar', 'SMA', 'Denpasar Barat', 'Kota Denpasar', 1, 'active')");
        $schoolId = (int)$conn->insert_id;
    }
}

// 6. Menyimpan Akun Pengguna dan Profil Siswa ke Database (Transaksi)
$conn->begin_transaction();

try {
    // 6.1 Insert akun pengguna ke tabel users (role 'student')
    $role = "student";
    $status = "active";

    $stmtUser = $conn->prepare(
        "INSERT INTO users (username, email, password_hash, full_name, role, institution, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtUser) {
        throw new Exception("Gagal mempersiapkan query user: " . $conn->error);
    }

    $stmtUser->bind_param("sssssss", $username, $email, $password, $fullName, $role, $schoolName, $status);
    if (!$stmtUser->execute()) {
        throw new Exception("Gagal menyimpan data akun pengguna: " . $stmtUser->error);
    }
    $newUserId = (int)$stmtUser->insert_id;
    $stmtUser->close();

    // 6.2 Insert data profil siswa ke tabel students
    $recipientStatus = "active";
    $stmtStudent = $conn->prepare(
        "INSERT INTO students (user_id, nisn, name, school_id, class_name, recipient_status)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    if (!$stmtStudent) {
        throw new Exception("Gagal mempersiapkan query siswa: " . $conn->error);
    }

    $stmtStudent->bind_param("ississ", $newUserId, $nis, $fullName, $schoolId, $className, $recipientStatus);
    if (!$stmtStudent->execute()) {
        throw new Exception("Gagal menyimpan profil siswa: " . $stmtStudent->error);
    }
    $newStudentId = (int)$stmtStudent->insert_id;
    $stmtStudent->close();

    // Commit transaksi
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    sendError(500, "Terjadi kesalahan saat pendaftaran: " . $e->getMessage(), $conn);
}

// 7. Membuat session login langsung setelah pendaftaran berhasil
session_regenerate_id(true);

$_SESSION["user_id"] = $newUserId;
$_SESSION["username"] = $username;
$_SESSION["full_name"] = $fullName;
$_SESSION["role"] = "siswa";

// 8. Menutup koneksi database
$conn->close();

// 9. Memberikan respons sukses
if ($isJsonRequest) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "status" => "success",
        "message" => "Pendaftaran berhasil.",
        "role" => "siswa",
        "data" => [
            "id" => $newUserId,
            "student_id" => $newStudentId,
            "username" => $username,
            "full_name" => $fullName,
            "email" => $email,
            "nis" => $nis,
            "school" => $schoolName,
            "class" => $className,
            "role" => "siswa"
        ]
    ]);
} else {
    echo "Pendaftaran berhasil. Role: siswa";
}

exit;
