<?php 
    class userController {
        /**
     * Check if user is logged in
     */
    private function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Please login to access this page.";
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }
    }
    
    /**
     * User Dashboard
     */
    public function dashboard() {
        $this->requireLogin();
        $pageTitle = "Dashboard - Silent Signal";

        require_once VIEW_PATH . 'includes/header.php';
        require_once VIEW_PATH . 'dashboard.php';
        require_once VIEW_PATH . 'includes/footer.php';
    }
}