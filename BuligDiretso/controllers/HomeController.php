<?php

class HomeController {
    public function index() {
        $pageTitle = "Home";
        // Load the home view

        require_once VIEW_PATH . 'includes/header.php';
        require_once VIEW_PATH . 'home.php';
        require_once VIEW_PATH . 'includes/footer.php';
        
    }
}