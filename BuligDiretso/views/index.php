<?php
session_start ();
require_once CONFIG_PATH . 'config.php';

$action = $_GET['action'] ?? 'home';

switch($action){
    case 'home':
        require_once CONTROLLER_PATH . 'HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
        
    default:
        require_once CONTROLLER_PATH . 'HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
    

}

?>
