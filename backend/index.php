<?php
/**
 * SIM-MBG — Backend Front Controller / Router
 * Titik masuk utama (Front Controller) untuk arsitektur MVC.
 * Mengarahkan request ke Controller dan Action yang sesuai.
 *
 * Contoh penggunaan:
 * - POST backend/index.php?controller=auth&action=login
 * - POST backend/index.php?controller=auth&action=register
 * - POST backend/index.php?controller=auth&action=logout
 * - GET  backend/index.php?controller=auth&action=me
 */

require_once __DIR__ . '/config/connection.php';

$controller = strtolower(trim($_GET['controller'] ?? $_POST['controller'] ?? 'auth'));
$action = strtolower(trim($_GET['action'] ?? $_POST['action'] ?? 'login'));

switch ($controller) {
    case 'auth':
        require_once __DIR__ . '/controllers/AuthController.php';
        $authController = new AuthController($conn);

        if ($action === 'register') {
            $authController->register();
        } elseif ($action === 'logout') {
            $authController->logout();
        } elseif ($action === 'me' || $action === 'profile') {
            $authController->me();
        } else {
            $authController->login();
        }
        break;

    default:
        http_response_code(404);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode([
            "status" => "error",
            "message" => "Controller '{$controller}' tidak ditemukan."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        break;
}
