<?php

class AuthController {
    public function showLogin() {
        $pageTitle = "Login - BuligDiretso";
        require_once VIEW_PATH . 'login.php';
    }

    public function showSignup() {
        $pageTitle = "Sign Up - BuligDiretso";
        require_once VIEW_PATH . 'signup.php';
    }
    public function showDashboard() {
        $pageTitle = "Dashboard - BuligDiretso";
        require_once VIEW_PATH . 'dashboard.php';
    }
}