<?php
session_start();

// Load config FIRST using real path
require_once __DIR__ . '/config/config.php';

$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'home':
        require_once CONTROLLER_PATH . 'HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
    
    case 'login':
        require_once CONTROLLER_PATH . 'AuthController.php';
        $controller = new AuthController();
        $controller->showLogin();
        break;

    case 'signup':
        require_once CONTROLLER_PATH . 'AuthController.php';
        $controller = new AuthController();
        $controller->showSignup();
        break;
        case 'signup':

    require_once CONTROLLER_PATH . 'AuthController.php';
        $controller = new AuthController();
        $controller->showDashboard();
        break;


    default:
        require_once CONTROLLER_PATH . 'HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
}
?>