<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once 'mysqli_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['id']) && isset($_SESSION['cart'][$data['id']])) {
        $id = (int)$data['id'];
        unset($_SESSION['cart'][$id]);
        
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['customer_id'])) {
            $customer_id = (int)$_SESSION['customer_id'];
            $stmt = mysqli_prepare($dbc, "DELETE FROM cart WHERE customer_id = ? AND product_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $customer_id, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        $total_items = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_items += $item['quantity'];
        }
        
        echo json_encode(['success' => true, 'total_items' => $total_items]);
        exit;
    }
}

echo json_encode(['success' => false]);
?>
