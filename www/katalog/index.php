<?php
session_start();
require_once 'db.php';
require_once 'controllers/ProductController.php';
require_once 'controllers/AuthController.php';

// Simple router
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

$productController = new ProductController($pdo);
$authController = new AuthController($pdo);

switch ($action) {
    case 'index':
        $productController->index();
        break;
    case 'add':
        $productController->add();
        break;
    case 'delete':
        $productController->delete();
        break;
    case 'add_category':
        $productController->addCategory();
        break;
    case 'delete_category':
        $productController->deleteCategory();
        break;
    case 'detail':
        $productController->detail();
        break;
    case 'login':
        $authController->login();
        break;
    case 'register':
        $authController->register();
        break;
    case 'logout':
        $authController->logout();
        break;
    default:
        echo "Page not found";
        break;
}
