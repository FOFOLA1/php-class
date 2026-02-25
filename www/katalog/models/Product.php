<?php
class Product
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll($categoryId = null, $sort = 'name')
    {
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id";

        $params = [];
        if ($categoryId) {
            // Get all descendant categories
            $categoryIds = $this->getCategoryDescendantIds($categoryId);

            // Create placeholders for IN clause
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

            $sql .= " WHERE p.category_id IN ($placeholders)";
            $params = $categoryIds;
        }

        $allowedSorts = ['name', 'price'];
        if (in_array($sort, $allowedSorts)) {
            $sql .= " ORDER BY " . $sort;
        } else {
            $sql .= " ORDER BY name";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategory($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategoryDescendantIds($categoryId)
    {
        $ids = [$categoryId];
        $children = $this->getCategories($categoryId);
        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getCategoryDescendantIds($child['id']));
        }
        return $ids;
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name 
                                     FROM products p 
                                     LEFT JOIN categories c ON p.category_id = c.id 
                                     WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getImages($productId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getCategories($parentId = null)
    {
        if ($parentId === null) {
            $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
            $stmt->execute();
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name");
            $stmt->execute([$parentId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get all categories as a flat list with indented names showing hierarchy.
     */
    public function getAllCategoriesFlat($parentId = null, $prefix = '')
    {
        $result = [];
        $children = $this->getCategories($parentId);
        foreach ($children as $cat) {
            $cat['display_name'] = $prefix . $cat['name'];
            $result[] = $cat;
            $result = array_merge($result, $this->getAllCategoriesFlat($cat['id'], $prefix . $cat['name'] . ' > '));
        }
        return $result;
    }

    /**
     * Save product (create or update)
     */
    public function addCategory($name, $parentId = null)
    {
        $sql = "INSERT INTO categories (name, parent_id) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $parentId]);
    }

    public function deleteCategory($id)
    {
        // 1. Get all descendant category IDs (including the category itself)
        $categoryIds = $this->getCategoryDescendantIds($id);

        // 2. For each category, get all products and delete them (to remove images from disk)
        foreach ($categoryIds as $catId) {
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE category_id = ?");
            if ($stmt->execute([$catId])) {
                $products = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($products as $productId) {
                    $this->delete($productId);
                }
            }
        }

        // 3. Delete categories
        if (empty($categoryIds)) return false;

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id IN ($placeholders)");
        return $stmt->execute($categoryIds);
    }

    public function save($data)
    {
        if (isset($data['id'])) {
            // Update
            $sql = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$data['name'], $data['description'], $data['price'], $data['category_id'], $data['id']]);
        } else {
            // Create
            $sql = "INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute([$data['name'], $data['description'], $data['price'], $data['category_id']])) {
                return $this->pdo->lastInsertId();
            }
            return false;
        }
    }

    public function addImage($productId, $imageUrl)
    {
        $stmt = $this->pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
        return $stmt->execute([$productId, $imageUrl]);
    }

    public function delete($id)
    {
        // First get images to delete files
        $images = $this->getImages($id);
        foreach ($images as $img) {
            if (file_exists($img['image_url'])) {
                unlink($img['image_url']);
            }
        }

        // Delete from DB (cascade handles image records)
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Deprecated: kept for compatibility if needed, but better to use save()
    public function add($name, $description, $price, $categoryId)
    {
        return $this->save([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category_id' => $categoryId
        ]);
    }
}
