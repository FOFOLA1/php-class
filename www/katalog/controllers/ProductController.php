<?php
require_once 'models/Product.php';

class ProductController
{
    private $productModel;

    public function __construct($pdo)
    {
        $this->productModel = new Product($pdo);
    }

    public function index()
    {
        $categoryId = isset($_GET['category']) ? $_GET['category'] : null;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

        $products = $this->productModel->getAll($categoryId, $sort);

        $currentCategory = null;
        if ($categoryId) {
            $currentCategory = $this->productModel->getCategory($categoryId);
        }

        // Get subcategories for navigation
        $categories = $this->productModel->getCategories($categoryId);

        // Also needed: if inside a subcategory, we might want to know the parent to go back
        // But $currentCategory has parent_id, so we can use that in the view.

        require 'views/product_list.php';
    }

    public function detail()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            die("Product ID required");
        }
        $product = $this->productModel->getById($id);
        if (!$product) {
            die("Product not found");
        }
        // Assuming fetchAll returns array of rows, fetch returns row
        // getById returns single row
        $images = $this->productModel->getImages($id);

        require 'views/product_detail.php';
    }

    public function delete()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Access denied");
        }

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $this->productModel->delete($id);
        }

        header("Location: index.php");
        exit;
    }

    public function addCategory()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Access denied");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $parentId = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

            if ($this->productModel->addCategory($name, $parentId)) {
                header("Location: index.php"); // Or back to category list
                exit;
            } else {
                echo "Failed to add category";
            }
        }

        // Reuse product list view or make a new one? 
        // Actually, maybe we accept the POST from the list view directly or have a small form.
        // Let's redirect back to index.
        header("Location: index.php");
        exit;
    }

    public function deleteCategory()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Access denied");
        }

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $this->productModel->deleteCategory($id);
        }

        header("Location: index.php");
        exit;
    }

    public function add()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Access denied");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $categoryId = $_POST['category_id'];

            $data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category_id' => $categoryId
            ];

            // Check file count before creating product
            if (isset($_FILES['images']) && count($_FILES['images']['name']) > 5) {
                $error = "Maximum allowed images is 5.";
            } else {
                $productId = $this->productModel->save($data);

                if ($productId) {
                    // Handle images
                    if (isset($_FILES['images'])) {
                        $files = $_FILES['images'];
                        $count = count($files['name']);
                        for ($i = 0; $i < $count; $i++) {
                            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                                $tmpName = $files['tmp_name'][$i];
                                $name = basename($files['name'][$i]);
                                $uploadDir = 'uploads/';
                                if (!file_exists($uploadDir)) {
                                    mkdir($uploadDir, 0777, true);
                                }
                                // Basic sanitization
                                $name = preg_replace("/[^a-zA-Z0-9\._-]/", "", $name);
                                $targetFile = $uploadDir . time() . "_" . $name;

                                if (move_uploaded_file($tmpName, $targetFile)) {
                                    $this->productModel->addImage($productId, $targetFile);
                                }
                            }
                        }
                    }
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Failed to add product";
                }
            }
        }

        $categories = $this->productModel->getAllCategoriesFlat();
        require 'views/product_form.php';
    }
}
