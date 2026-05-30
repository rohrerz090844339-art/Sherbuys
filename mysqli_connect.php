<?php

if (ob_get_level() === 0) {
    ob_start();
}

define('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

mysqli_report(MYSQLI_REPORT_OFF);

$dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!$dbc && !getenv('MYSQLHOST')) {
    // Local fallback
    $dbc = @mysqli_connect('127.0.0.1', 'root', '', 'ecommerces');
}

if (!$dbc) {
    ob_end_clean();
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>DB Error</title></head><body>';
    echo '<div style="font-family:sans-serif;background:#1e1e2e;color:#f38ba8;padding:40px;text-align:center;min-height:100vh;">';
    echo '<h2>⚠️ Database Connection Failed</h2>';
    echo '<p>Please ensure your database is running and configured correctly.</p>';
    echo '<p style="font-size:0.85rem;color:#a6adc8;">' . htmlspecialchars(mysqli_connect_error()) . '</p>';
    echo '<p style="color:#a6adc8;">On Railway, ensure MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, and MYSQLPORT are set.</p>';
    echo '</div></body></html>';
    exit;
}


if (!mysqli_select_db($dbc, DB_NAME)) {
    $create_db = mysqli_query($dbc, "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    if ($create_db) {
        mysqli_select_db($dbc, DB_NAME);
    } else {
        ob_end_clean();
        die('Could not select or create database: ' . mysqli_error($dbc));
    }
}

mysqli_set_charset($dbc, 'utf8mb4');


$table_check = mysqli_query($dbc, "SHOW TABLES LIKE 'products'");
if (!$table_check || mysqli_num_rows($table_check) == 0) {
    $setup_queries = [
        "CREATE TABLE IF NOT EXISTS `customers` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `fullname`   VARCHAR(100)  NOT NULL,
            `email`      VARCHAR(150)  NOT NULL UNIQUE,
            `password`   VARCHAR(255)  NOT NULL,
            `phone`      VARCHAR(20)   DEFAULT NULL,
            `address`    TEXT          DEFAULT NULL,
            `created_at` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `admins` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `username`   VARCHAR(100) NOT NULL,
            `email`      VARCHAR(150) NOT NULL UNIQUE,
            `password`   VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `brands` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `name`        VARCHAR(100) NOT NULL,
            `description` TEXT         DEFAULT NULL,
            `logo`        VARCHAR(255) DEFAULT NULL,
            `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `products` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `brand_id`    INT             DEFAULT NULL,
            `name`        VARCHAR(200)    NOT NULL,
            `description` TEXT            DEFAULT NULL,
            `price`       DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `image`       VARCHAR(255)    DEFAULT 'assets/images/placeholder.png',
            `category`    VARCHAR(100)    DEFAULT NULL,
            `badge`       VARCHAR(50)     DEFAULT NULL,
            `stock`       INT             NOT NULL DEFAULT 0,
            `created_at`  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `order_statuses` (
            `id`   INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `payment_modes` (
            `id`   INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `orders` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `customer_id`      INT           NOT NULL,
            `total_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `payment_mode_id`  INT           DEFAULT NULL,
            `shipping_name`    VARCHAR(150)  DEFAULT NULL,
            `shipping_address` TEXT          DEFAULT NULL,
            `shipping_city`    VARCHAR(100)  DEFAULT NULL,
            `shipping_zip`     VARCHAR(20)   DEFAULT NULL,
            `status_id`        INT           NOT NULL DEFAULT 1,
            `created_at`       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`customer_id`)     REFERENCES `customers`(`id`)     ON DELETE CASCADE,
            FOREIGN KEY (`payment_mode_id`) REFERENCES `payment_modes`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`status_id`)       REFERENCES `order_statuses`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `order_items` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `order_id`     INT           NOT NULL,
            `product_id`   INT           DEFAULT NULL,
            `product_name` VARCHAR(200)  NOT NULL,
            `price`        DECIMAL(10,2) NOT NULL,
            `quantity`     INT           NOT NULL DEFAULT 1,
            FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `cart` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `customer_id` INT NOT NULL,
            `product_id`  INT NOT NULL,
            `quantity`    INT NOT NULL DEFAULT 1,
            `added_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_cart_item` (`customer_id`, `product_id`),
            FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`)  REFERENCES `products`(`id`)  ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    foreach ($setup_queries as $q) {
        mysqli_query($dbc, $q);
    }

    
    $chk = mysqli_query($dbc, "SELECT COUNT(*) AS c FROM order_statuses");
    if ($chk && mysqli_fetch_assoc($chk)['c'] == 0) {
        mysqli_query($dbc, "INSERT INTO order_statuses (name) VALUES ('Pending'),('Approved'),('Disapproved'),('Cancelled'),('Delivered')");
    }

    
    $chk = mysqli_query($dbc, "SELECT COUNT(*) AS c FROM payment_modes");
    if ($chk && mysqli_fetch_assoc($chk)['c'] == 0) {
        mysqli_query($dbc, "INSERT INTO payment_modes (name) VALUES ('Credit / Debit Card'),('PayPal'),('Cryptocurrency'),('Cash on Delivery'),('GCash')");
    }

    
    $chk = mysqli_query($dbc, "SELECT COUNT(*) AS c FROM brands");
    if ($chk && mysqli_fetch_assoc($chk)['c'] == 0) {
        mysqli_query($dbc, "INSERT INTO brands (name, description) VALUES
            ('TechCorp',    'Leading technology manufacturer of premium laptops and computers.'),
            ('SoundMaster', 'Industry leader in premium audio equipment and headphones.'),
            ('MobileElite', 'Cutting-edge mobile devices for the modern consumer.')");
    }

    
    $chk = mysqli_query($dbc, "SELECT COUNT(*) AS c FROM products");
    if ($chk && mysqli_fetch_assoc($chk)['c'] == 0) {
        mysqli_query($dbc, "INSERT INTO products (brand_id, name, description, price, image, category, badge, stock) VALUES
            (1, 'Quantum X Pro Laptop',    'Ultra-thin, high-performance laptop with a stunning OLED display.',   1899.99, 'assets/images/laptop.png',     'Computers', 'New Arrival', 10),
            (3, 'Stellar Edge Smartphone', 'Next-gen flagship smartphone with edge-to-edge display.',             1099.99, 'assets/images/smartphone.png', 'Phones',    'Bestseller',  15),
            (2, 'Sonic Void Headphones',   'Premium active noise-cancelling wireless headphones.',                 349.99, 'assets/images/headphones.png', 'Audio',     '',            20)");
    }
}


$admin_chk = mysqli_query($dbc, "SELECT COUNT(*) AS c FROM admins");
if ($admin_chk && mysqli_fetch_assoc($admin_chk)['c'] == 0) {
    
    $hashed = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($dbc, "INSERT IGNORE INTO admins (username, email, password) VALUES (?, ?, ?)");
    $uname = 'admin';
    $uemail = 'admin@sherbuys.com';
    mysqli_stmt_bind_param($stmt, 'sss', $uname, $uemail, $hashed);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
