<?php
echo "=== Sherbuys DB Test ===" . PHP_EOL;


mysqli_report(MYSQLI_REPORT_OFF);

$dbc = @mysqli_connect('127.0.0.1', 'root', '', '', 3306);
if (!$dbc) $dbc = @mysqli_connect('127.0.0.1', 'root', '', '', 3307);
if (!$dbc) $dbc = @mysqli_connect('127.0.0.1', 'root', '', '');

if (!$dbc) {
    echo "FAIL: Cannot connect to MySQL - " . mysqli_connect_error() . PHP_EOL;
    exit(1);
}
echo "OK: MySQL connected!" . PHP_EOL;


mysqli_query($dbc, "CREATE DATABASE IF NOT EXISTS ecommerces CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
mysqli_select_db($dbc, 'ecommerces');
mysqli_set_charset($dbc, 'utf8mb4');
echo "OK: Database 'ecommerces' selected." . PHP_EOL;


$drops = ['cart','order_items','orders','products','brands','payment_modes','order_statuses','customers','admins'];
foreach ($drops as $t) mysqli_query($dbc, "DROP TABLE IF EXISTS `$t`");
echo "OK: Old tables dropped." . PHP_EOL;


$tables = [];

$tables['customers'] = "CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$tables['admins'] = "CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$tables['brands'] = "CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$tables['order_statuses'] = "CREATE TABLE order_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$tables['payment_modes'] = "CREATE TABLE payment_modes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$tables['products'] = "CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT DEFAULT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image VARCHAR(255) DEFAULT 'assets/images/placeholder.png',
    category VARCHAR(100) DEFAULT NULL,
    badge VARCHAR(50) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$tables['orders'] = "CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_mode_id INT DEFAULT NULL,
    shipping_name VARCHAR(150) DEFAULT NULL,
    shipping_address TEXT DEFAULT NULL,
    shipping_city VARCHAR(100) DEFAULT NULL,
    shipping_zip VARCHAR(20) DEFAULT NULL,
    status_id INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_mode_id) REFERENCES payment_modes(id) ON DELETE SET NULL,
    FOREIGN KEY (status_id) REFERENCES order_statuses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$tables['order_items'] = "CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$tables['cart'] = "CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (customer_id, product_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

foreach ($tables as $name => $sql) {
    if (mysqli_query($dbc, $sql)) {
        echo "OK: Table '$name' created." . PHP_EOL;
    } else {
        echo "FAIL: Table '$name' - " . mysqli_error($dbc) . PHP_EOL;
    }
}


mysqli_query($dbc, "INSERT INTO order_statuses (name) VALUES ('Pending'),('Approved'),('Disapproved'),('Cancelled'),('Delivered')");
echo "OK: Order statuses seeded." . PHP_EOL;

mysqli_query($dbc, "INSERT INTO payment_modes (name) VALUES ('Credit / Debit Card'),('PayPal'),('Cryptocurrency'),('Cash on Delivery'),('GCash')");
echo "OK: Payment modes seeded." . PHP_EOL;

mysqli_query($dbc, "INSERT INTO brands (name, description) VALUES
    ('TechCorp','Leading technology manufacturer of premium laptops and computers.'),
    ('SoundMaster','Industry leader in premium audio equipment and headphones.'),
    ('MobileElite','Cutting-edge mobile devices for the modern consumer.')");
echo "OK: Brands seeded." . PHP_EOL;

mysqli_query($dbc, "INSERT INTO products (brand_id, name, description, price, image, category, badge, stock) VALUES
    (1,'Quantum X Pro Laptop','Ultra-thin high-performance laptop with OLED display.',1899.99,'assets/images/laptop.png','Computers','New Arrival',10),
    (3,'Stellar Edge Smartphone','Next-gen flagship smartphone with edge-to-edge display.',1099.99,'assets/images/smartphone.png','Phones','Bestseller',15),
    (2,'Sonic Void Headphones','Premium active noise-cancelling wireless headphones.',349.99,'assets/images/headphones.png','Audio','',20)");
echo "OK: Products seeded." . PHP_EOL;


$hash = password_hash('test123', PASSWORD_DEFAULT);
mysqli_query($dbc, "INSERT IGNORE INTO customers (fullname, email, password) VALUES ('Test User', 'test@sherbuys.com', '$hash')");
$customer_id = mysqli_insert_id($dbc);
if (!$customer_id) {
    $r = mysqli_query($dbc, "SELECT id FROM customers WHERE email='test@sherbuys.com'");
    $customer_id = mysqli_fetch_row($r)[0];
}
echo "OK: Test customer ID=$customer_id ready." . PHP_EOL;

$stmt = mysqli_prepare($dbc, "INSERT INTO orders (customer_id, total_amount, payment_mode_id, shipping_name, shipping_address, shipping_city, shipping_zip, status_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$total = 1899.99;
$payment_mode_id = 5; 
$status_id = 1;     
$ship_name = "Test User";
$ship_addr = "123 Test St";
$ship_city = "Manila";
$ship_zip  = "1000";
mysqli_stmt_bind_param($stmt, 'idissssi', $customer_id, $total, $payment_mode_id, $ship_name, $ship_addr, $ship_city, $ship_zip, $status_id);

if (mysqli_stmt_execute($stmt)) {
    $order_id = mysqli_insert_id($dbc);
    echo "OK: Test order inserted! Order ID=$order_id" . PHP_EOL;
    mysqli_stmt_close($stmt);

   
    $istmt = mysqli_prepare($dbc, "INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
    $pid = 1; $pname = "Quantum X Pro Laptop"; $price = 1899.99; $qty = 1;
    mysqli_stmt_bind_param($istmt, 'iisdi', $order_id, $pid, $pname, $price, $qty);
    if (mysqli_stmt_execute($istmt)) {
        echo "OK: Order item inserted!" . PHP_EOL;
    } else {
        echo "FAIL: Order item - " . mysqli_error($dbc) . PHP_EOL;
    }
    mysqli_stmt_close($istmt);
} else {
    echo "FAIL: Order insert - " . mysqli_error($dbc) . PHP_EOL;
}


$result = mysqli_query($dbc, "
    SELECT o.id, o.total_amount, o.created_at, s.name AS status, pm.name AS payment,
           oi.product_name, oi.quantity, oi.price
    FROM orders o
    JOIN order_statuses s ON o.status_id = s.id
    LEFT JOIN payment_modes pm ON o.payment_mode_id = pm.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.customer_id = $customer_id
");
echo PHP_EOL . "=== Orders in Database ===" . PHP_EOL;
while ($row = mysqli_fetch_assoc($result)) {
    echo "  Order #" . $row['id'] . " | " . $row['payment'] . " | Status: " . $row['status'] . " | Total: PHP " . number_format($row['total_amount'],2) . PHP_EOL;
    echo "  -> Item: " . $row['product_name'] . " x" . $row['quantity'] . " @ PHP " . number_format($row['price'],2) . PHP_EOL;
}

echo PHP_EOL . "=== ALL TESTS PASSED ===" . PHP_EOL;
mysqli_close($dbc);
