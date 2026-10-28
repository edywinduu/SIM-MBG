<?php
/**
 * SIM-MBG — API Logout Endpoint (MVC Gateway)
 * Mengarahkan proses logout ke arsitektur MVC (AuthController).
 */

require_once __DIR__ . "/../config/connection.php";
require_once __DIR__ . "/../controllers/AuthController.php";

$authController = new AuthController($conn);
$authController->logout();
