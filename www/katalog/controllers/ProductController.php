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
        $categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
        if ($categoryId !== null && $categoryId <= 0) {
            $categoryId = null;
        }
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

        $products = $this->productModel->getAll($categoryId, $sort);

        $currentCategory = null;
        $parentCategory = null;
        if ($categoryId) {
            $currentCategory = $this->productModel->getCategory($categoryId);
            if ($currentCategory && !empty($currentCategory['parent_id'])) {
                $parentCategory = $this->productModel->getCategory($currentCategory['parent_id']);
            }
        }

        $categories = $this->productModel->getCategories($categoryId);

        require 'views/product_list.php';
    }

    public function detail()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if (!$id || $id <= 0) {
            die("Product ID required");
        }
        $product = $this->productModel->getById($id);
        if (!$product) {
            die("Produkt nenalezen");
        }
        $images = $this->productModel->getImages($id);

        require 'views/product_detail.php';
    }

    public function delete()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Přístup odepřen");
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if ($id && $id > 0) {
            $this->productModel->delete($id);
        }

        header("Location: index.php");
        exit;
    }

    public function addCategory()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Přístup odepřen");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $parentId = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

            if ($name === '') {
                header("Location: index.php");
                exit;
            }

            if ($this->productModel->addCategory($name, $parentId)) {
                header("Location: index.php");
                exit;
            } else {
                echo "Nepodařilo se vytvořit kategorii.";
            }
        }
        header("Location: index.php");
        exit;
    }

    public function deleteCategory()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Přístup odepřen");
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if ($id && $id > 0) {
            $this->productModel->deleteCategory($id);
        }

        header("Location: index.php");
        exit;
    }

    public function add()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            die("Přístup odepřen");
        }

        $phpUploadLimit = (int) ini_get('max_file_uploads');
        $maxImageCount = $phpUploadLimit > 0 ? min(5, $phpUploadLimit) : 5;
        $maxTotalImageSize = 20 * 1024 * 1024;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = $_POST['price'];
            $categoryId = (int) $_POST['category_id'];

            $data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category_id' => $categoryId
            ];
            if (isset($_FILES['images']) && count($_FILES['images']['name']) > $maxImageCount) {
                $error = "Maximální povolený počet obrázků je {$maxImageCount}.";
            } elseif (isset($_FILES['images']) && array_sum($_FILES['images']['size']) > $maxTotalImageSize) {
                $error = "Maximální celková velikost obrázků je 20 MB.";
            } else {
                $productId = $this->productModel->save($data);

                if ($productId) {
                    if (isset($_FILES['images'])) {
                        $files = $_FILES['images'];
                        $count = count($files['name']);
                        $uploadDir = __DIR__ . '/../uploads/';
                        $webPath = 'uploads/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        for ($i = 0; $i < $count; $i++) {
                            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                                $tmpName = $files['tmp_name'][$i];
                                $name = basename($files['name'][$i]);
                                $name = preg_replace("/[^a-zA-Z0-9\._-]/", "", $name);
                                $fileName = time() . "_" . $name;

                                if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                                    $this->productModel->addImage($productId, $webPath . $fileName);
                                }
                            }
                        }
                    }
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Nepodařilo se přidat produkt.";
                }
            }
        }

        $categories = $this->productModel->getAllCategoriesFlat();
        require 'views/product_form.php';
    }
}
