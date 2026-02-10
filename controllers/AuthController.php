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

    /**
     * Process login form submission
     */
  public function processLogin() {
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Validate inputs
        if(empty($email) || empty($password)) {
            $_SESSION['error'] = "All fields are required!";
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }

        // Demo credentials array for easy testing
        $demoUsers = [
            [
                'email' => 'admin@gmail.com',
                'password' => 'admin123',
                'id' => 1,
                'name' => 'Admin User',
                'role' => 'admin'
            ],
            [
                'email' => 'user@gmail.com',
                'password' => 'user123',
                'id' => 2,
                'name' => 'Juan Dela Cruz',
                'role' => 'pwd'
            ],
        ];

        // Check credentials
        $loggedIn = false;
        foreach ($demoUsers as $user) {
            if ($email == $user['email'] && $password == $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['success'] = "Login successful!";
                $loggedIn = true;
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: " . BASE_URL . "index.php?action=admin-dashboard");
                } else {
                    header("Location: " . BASE_URL . "index.php?action=dashboard");
                }
                exit();
            }
        }

        if (!$loggedIn) {
            $_SESSION['error'] = "Invalid credentials!";
            header("Location: " . BASE_URL . "index.php?action=login");
            exit();
        }
    }
}

    /**
     * Process signup form submission
     */
    public function processSignup() {
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get posted data
            $fname = $_POST['first_name'] ?? '';
            $lname = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone_number = $_POST['phone'] ?? '';
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        header("Location: " . BASE_URL . "index.php?action=home");
        exit();
    }
}