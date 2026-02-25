<?php
require_once 'models/User.php';

class AuthController
{
    private $userModel;

    public function __construct($pdo)
    {
        $this->userModel = new User($pdo);
    }

    public function login()
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $user = $this->userModel->login($username, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid credentials";
            }
        }
        require 'views/login.php';
    }

    public function register()
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Basic validation
            if (empty($username) || empty($password)) {
                $error = "Username and password are required";
            } else {
                if ($this->userModel->register($username, $password)) {
                    // Start session and login automatically or just redirect
                    header("Location: index.php?action=login");
                    exit;
                } else {
                    $error = "Registration failed (username might be taken)";
                }
            }
        }
        require 'views/register.php';
    }

    public function logout()
    {
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
