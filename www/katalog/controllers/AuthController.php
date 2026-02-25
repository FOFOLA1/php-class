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
                $error = "Neplatné přihlašovací údaje.";
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

            if (empty($username) || empty($password)) {
                $error = "Uživatelské jméno a heslo jsou povinné.";
            } else {
                if ($this->userModel->register($username, $password)) {
                    header("Location: index.php?action=login");
                    exit;
                } else {
                    $error = "Registrace se nezdařila (uživatelské jméno může být již zabrané).";
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
