<?php
/**
 * SIM-MBG — API Landing / Profile Endpoint (MVC Gateway)
 * Mengarahkan pengecekan sesi dan profil pengguna aktif ke arsitektur MVC (AuthController).
 */

require_once __DIR__ . "/../config/connection.php";
require_once __DIR__ . "/../controllers/AuthController.php";

$authController = new AuthController($conn);
$authController->me();
