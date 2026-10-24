<?php
$host = 'localhost';
$user = 'edy';
$pass = '123';
$dbname = 'sim_mbg';
$port ='3306';
$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>