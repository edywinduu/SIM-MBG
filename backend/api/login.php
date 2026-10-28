<?php
/**
 * SIM-MBG — API Login Endpoint (MVC Gateway)
 * Mengarahkan autentikasi login ke arsitektur MVC (AuthController & User Model).
 * Acuan implementasi prosedural tetap dipertahankan pada login.reference.php.
 */

require_once __DIR__ . "/../config/connection.php";
require_once __DIR__ . "/../controllers/AuthController.php";

$authController = new AuthController($conn);
$authController->login();
