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
('Elektronika', NULL), 
('Oblečení', NULL), 
('Knihy', NULL),
('Notebooky', 1),
('Součásti', 1),
('Mužské oblečení', 2);

INSERT IGNORE INTO products (name, description, price, category_id) VALUES 
('Notebook', 'Výkonný moderní herní notebook', 24999, 4), -- Notebooks
('Tričko', 'Bavlněné tričko', 499, 6), -- Men Clothing
('PHP Kniha', 'Nauč se PHP z pohodlí domova!', 751, 3), -- Books
('CPU', 'Moderní procesor', 7500, 5); -- Components


INSERT IGNORE INTO product_images (product_id, image_url) VALUES
(1, 'uploads/laptop.webp'),
(3, 'uploads/php_guide.webp'),
(4, 'uploads/cpu.webp');