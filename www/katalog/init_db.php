<?php
require_once 'db.php';
require_once 'models/User.php';

$sql = file_get_contents('schema.sql');
if ($sql === false) {
    die("Error reading schema.sql");
}

try {
    $pdo->exec($sql);
    echo "Database initialized successfully.\n";

    // Add admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pass = User::hashPassword('admin');

        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES ('admin', :pass, 'admin')");
        $stmt->execute([':pass' => $pass]);
        echo "Admin user created (admin/admin).\n";
    }
} catch (PDOException $e) {
    die("Error initializing database: " . $e->getMessage());
}
