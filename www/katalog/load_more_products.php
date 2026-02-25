<?php
require_once 'db.php';

$sqlFile = __DIR__ . '/extra_products.sql';
$sql = file_get_contents($sqlFile);

if ($sql === false) {
    die("Error reading extra_products.sql\n");
}

try {
    $beforeProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $beforeImages = (int) $pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn();

    $pdo->exec($sql);

    $afterProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $afterImages = (int) $pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn();

    $addedProducts = $afterProducts - $beforeProducts;
    $addedImages = $afterImages - $beforeImages;

    echo "Extra products loaded successfully.\n";
    echo "Added products: {$addedProducts}\n";
    echo "Added images: {$addedImages}\n";
} catch (PDOException $e) {
    die('Error loading extra products: ' . $e->getMessage() . "\n");
}
