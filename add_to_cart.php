<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once 'mysqli_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($data['id'])) {
        $id = (int)$data['id'];
        
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $data['name'],
                'price' => $data['price'],
                'quantity' => 1
            ];
        }
        
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['customer_id'])) {
            $customer_id = (int)$_SESSION['customer_id'];
            $stmt = mysqli_prepare($dbc, "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            mysqli_stmt_bind_param($stmt, 'ii', $customer_id, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    $total_items = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_items += $item['quantity'];
    }
    
    echo json_encode(['success' => true, 'total_items' => $total_items]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
