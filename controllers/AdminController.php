<?php

class AdminController {

    public function adminDashboard() {
        $pageTitle = "Admin Dashboard - BuligDiretso";

        require_once VIEW_PATH . 'includes/header.php';
        require_once VIEW_PATH . 'admin-dashboard.php';
        require_once VIEW_PATH . 'includes/footer.php';
    }
}
