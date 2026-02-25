CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user'
);

INSERT IGNORE INTO categories (name, parent_id) VALUES 
('Electronics', NULL), 
('Clothing', NULL), 
('Books', NULL),
('Laptops', 1),
('Components', 1),
('Men Clothing', 2);

INSERT IGNORE INTO products (name, description, price, category_id) VALUES 
('Laptop', 'High performance laptop', 999.99, 4), -- Laptops
('T-Shirt', 'Cotton t-shirt', 19.99, 6), -- Men Clothing
('PHP Guide', 'Learn PHP in 24 hours', 29.99, 3), -- Books
('CPU', 'Fast Processor', 299.99, 5); -- Components
