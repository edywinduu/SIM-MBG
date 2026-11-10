<?php
/**
 * SIM-MBG — SPPG API Gateway (MVC Gateway)
 * Meneruskan request modul SPPG ke SppgController & SppgModel.
 */

require_once __DIR__ . "/../config/connection.php";
require_once __DIR__ . "/../controllers/SppgController.php";

$sppgController = new SppgController($conn);
$sppgController->handleRequest();

