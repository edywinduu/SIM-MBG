<?php
/**
 * SIM-MBG — Admin API Gateway (MVC Gateway)
 * Meneruskan request modul Admin ke AdminController & AdminModel.
 */

require_once __DIR__ . "/../config/connection.php";
require_once __DIR__ . "/../controllers/AdminController.php";

$adminController = new AdminController($conn);
$adminController->handleRequest();

