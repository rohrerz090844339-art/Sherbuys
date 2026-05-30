<?php
// Database Setup Script
// On Railway, you can run this once to initialize your tables.

DEFINE('DB_HOST',     getenv('MYSQLHOST') ?: '127.0.0.1');
DEFINE('DB_USER',     getenv('MYSQLUSER') ?: 'root');
DEFINE('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: '');
DEFINE('DB_NAME',     getenv('MYSQLDATABASE') ?: 'railway');
DEFINE('DB_PORT',     getenv('MYSQLPORT') ?: '3306');

$logs   = [];
$errors = [];

mysqli_report(MYSQLI_REPORT_OFF);

$dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, '', DB_PORT);
if (!$dbc && !getenv('MYSQLHOST')) {
    $dbc = @mysqli_connect('127.0.0.1', 'root', '', '', 3307);
}

if (!$dbc) {
    die('<pre style="color:red">Could not connect to MySQL: ' . mysqli_connect_error() . '</pre>');
}
$logs[] = '✅ Connected to MySQL at ' . DB_HOST;

if (mysqli_query($dbc, "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
    $logs[] = '✅ Database `' . DB_NAME . '` ensured.';
} else {
    $errors[] = '❌ Could not create database: ' . mysqli_error($dbc);
}
mysqli_select_db($dbc, DB_NAME);
mysqli_set_charset($dbc, 'utf8mb4');

$drops = ['cart','order_items','orders','products','brands','payment_modes','order_statuses','customers','admins'];
foreach ($drops as $tbl) {
    mysqli_query($dbc, "DROP TABLE IF EXISTS `$tbl`");
}
$logs[] = '✅ Old tables dropped.';

$tables = [
"CREATE TABLE customers (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    fullname   VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    phone      VARCHAR(20)   DEFAULT NULL,
    address    TEXT          DEFAULT NULL,
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"CREATE TABLE admins (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"CREATE TABLE brands (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT         DEFAULT NULL,
    logo        VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    brand_id    INT             DEFAULT NULL,
    name        VARCHAR(200)    NOT NULL,
    description TEXT            DEFAULT NULL,
    price       DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    image       VARCHAR(255)    DEFAULT 'assets/images/placeholder.png',
    category    VARCHAR(100)    DEFAULT NULL,
    badge       VARCHAR(50)     DEFAULT NULL,
    stock       INT             NOT NULL DEFAULT 0,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"CREATE TABLE order_statuses (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"CREATE TABLE payment_modes (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"CREATE TABLE orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    customer_id      INT           NOT NULL,
    total_amount     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_mode_id  INT           DEFAULT NULL,
    shipping_name    VARCHAR(150)  DEFAULT NULL,
    shipping_address TEXT          DEFAULT NULL,
    shipping_city    VARCHAR(100)  DEFAULT NULL,
    shipping_zip     VARCHAR(20)   DEFAULT NULL,
    status_id        INT           NOT NULL DEFAULT 1,
    created_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id)     REFERENCES customers(id)     ON DELETE CASCADE,
    FOREIGN KEY (payment_mode_id) REFERENCES payment_modes(id) ON DELETE SET NULL,
    FOREIGN KEY (status_id)       REFERENCES order_statuses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"CREATE TABLE order_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id     INT           NOT NULL,
    product_id   INT           DEFAULT NULL,
    product_name VARCHAR(200)  NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    quantity     INT           NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

"CREATE TABLE cart (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (customer_id, product_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($tables as $sql) {
    if (mysqli_query($dbc, $sql)) {
        preg_match('/CREATE TABLE (\w+)/', $sql, $m);
        $logs[] = '✅ Table `' . ($m[1] ?? '?') . '` created.';
    } else {
        $errors[] = '❌ ' . mysqli_error($dbc);
    }
}

$admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);

$seeds = [
    "INSERT INTO order_statuses (name) VALUES ('Pending'),('Approved'),('Disapproved'),('Cancelled'),('Delivered')",
    "INSERT INTO payment_modes (name) VALUES ('Credit / Debit Card'),('PayPal'),('Cryptocurrency'),('Cash on Delivery'),('GCash')",
    "INSERT INTO brands (name, description) VALUES
        ('TechCorp',    'Leading technology manufacturer of premium laptops and computers.'),
        ('SoundMaster', 'Industry leader in premium audio equipment and headphones.'),
        ('MobileElite', 'Cutting-edge mobile devices for the modern consumer.')",
    "INSERT INTO products (brand_id, name, description, price, image, category, badge, stock) VALUES
        (1, 'Quantum X Pro Laptop',    'Ultra-thin, high-performance laptop with a stunning OLED display.',   1899.99, 'assets/images/laptop.png',     'Computers', 'New Arrival', 10),
        (3, 'Stellar Edge Smartphone', 'Next-gen flagship smartphone with edge-to-edge display.',             1099.99, 'assets/images/smartphone.png', 'Phones',    'Bestseller',  15),
        (2, 'Sonic Void Headphones',   'Premium active noise-cancelling wireless headphones.',                 349.99, 'assets/images/headphones.png', 'Audio',     '',            20)",
    "INSERT INTO admins (username, email, password) VALUES ('admin', 'admin@sherbuys.com', '" . mysqli_real_escape_string($dbc, $admin_password_hash) . "')"
];

foreach ($seeds as $sql) {
    if (mysqli_query($dbc, $sql)) {
        $logs[] = '✅ Seed data inserted.';
    } else {
        $errors[] = '❌ Seed error: ' . mysqli_error($dbc);
    }
}

$r = mysqli_query($dbc, "SHOW TABLES");
$existing_tables = [];
while ($row = mysqli_fetch_row($r)) $existing_tables[] = $row[0];
$logs[] = '📋 Tables in DB: ' . implode(', ', $existing_tables);

mysqli_close($dbc);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DB Setup — Sherbuys</title>
<style>
  body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 40px; }
  h1   { color: #6366f1; font-size: 1.8rem; margin-bottom: 10px; }
  .log   { color: #4ade80; margin: 4px 0; }
  .error { color: #f87171; margin: 4px 0; }
  .box   { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 30px; max-width: 800px; margin: 20px auto; }
  .btn   { display: inline-block; margin-top: 20px; padding: 12px 28px; background: #6366f1; color: #fff; border-radius: 8px; text-decoration: none; font-size: 1rem; }
  .btn:hover { background: #4f46e5; }
</style>
</head>
<body>
<div class="box">
  <h1>🗄️ Database Setup Report</h1>
  <?php foreach ($logs   as $l) echo "<div class='log'>$l</div>"; ?>
  <?php foreach ($errors as $e) echo "<div class='error'>$e</div>"; ?>
  <?php if (empty($errors)): ?>
    <p style="margin-top:20px; color:#4ade80; font-size:1.1rem;">✅ <strong>All done! Your database is ready.</strong></p>
    <a href="index.php" class="btn">→ Go to Shop</a>
    &nbsp;
    <a href="register.php" class="btn" style="background:#10b981;">→ Register Account</a>
  <?php else: ?>
    <p style="margin-top:20px; color:#f87171; font-size:1.1rem;">⚠️ Some errors occurred. Check XAMPP / MySQL is running.</p>
  <?php endif; ?>
</div>
</body>
</html>
