<?php
/**
 * SIM-MBG — API Register Endpoint (MVC Gateway)
 * Mengarahkan autentikasi registrasi ke arsitektur MVC (AuthController & User Model).
 * Acuan implementasi prosedural tetap dipertahankan pada register.reference.php.
 */

require_once __DIR__ . "/../config/connection.php";
require_once __DIR__ . "/../controllers/AuthController.php";

$authController = new AuthController($conn);
$authController->register();
