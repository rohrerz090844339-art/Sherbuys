<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysqli_connect.php';


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'];
    
    
    $payment_method_post = $_POST['payment_method'] ?? '';
    $payment_mode_id = null;
    if ($payment_method_post === 'credit_card') {
        $payment_mode_id = 1;
    } elseif ($payment_method_post === 'paypal') {
        $payment_mode_id = 2;
    } elseif ($payment_method_post === 'crypto') {
        $payment_mode_id = 3;
    } elseif ($payment_method_post === 'cod') {
        $payment_mode_id = 4;
    } elseif ($payment_method_post === 'gcash') {
        $payment_mode_id = 5;
    }

    $shipping_name = trim($_POST['shipping_name'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $shipping_city = trim($_POST['shipping_city'] ?? '');
    $shipping_zip = trim($_POST['shipping_zip'] ?? '');

    
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    
    
    $status_id = 1; 
    
    $query = "INSERT INTO orders (customer_id, total_amount, payment_mode_id, shipping_name, shipping_address, shipping_city, shipping_zip, status_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbc, $query);
    
    mysqli_stmt_bind_param($stmt, 'idissssi', $customer_id, $total_amount, $payment_mode_id, $shipping_name, $shipping_address, $shipping_city, $shipping_zip, $status_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($dbc);
        mysqli_stmt_close($stmt);

        
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $product_name = $item['name'];
            $price = $item['price'];
            $quantity = $item['quantity'];

            $item_query = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)";
            $item_stmt = mysqli_prepare($dbc, $item_query);
            
            mysqli_stmt_bind_param($item_stmt, 'iisdi', $order_id, $product_id, $product_name, $price, $quantity);
            mysqli_stmt_execute($item_stmt);
            mysqli_stmt_close($item_stmt);
        }

        
        unset($_SESSION['cart']);
        $clear_stmt = mysqli_prepare($dbc, "DELETE FROM cart WHERE customer_id = ?");
        mysqli_stmt_bind_param($clear_stmt, 'i', $customer_id);
        mysqli_stmt_execute($clear_stmt);
        mysqli_stmt_close($clear_stmt);

        
        include 'header.php';
        ?>
        <section class="success-section" style="padding: 200px 0 150px; min-height: 60vh; text-align: center;">
            <div class="container">
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 60px 40px; max-width: 600px; margin: 0 auto;">
                    <div style="width: 80px; height: 80px; background: rgba(34, 197, 94, 0.1); color: #22c55e; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 30px;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h2 style="font-size: 2.5rem; margin-bottom: 15px;">Payment Successful!</h2>
                    <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 30px;">Thank you for your purchase. Your order (ID: #<?php echo $order_id; ?>) has been placed and is being processed.</p>
                    <div style="display: flex; gap: 15px; justify-content: center;">
                        <a href="purchases.php" class="btn btn-primary btn-large">View Purchases</a>
                        <a href="index.php" class="btn btn-outline btn-large">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </section>
        <?php
        include 'footer.php';
    } else {
        
        include 'header.php';
        ?>
        <section style="padding: 200px 0 150px; min-height: 60vh; text-align: center;">
            <div class="container">
                <div style="background: var(--bg-card); border: 1px solid #ef4444; border-radius: 16px; padding: 60px 40px; max-width: 600px; margin: 0 auto;">
                    <div style="width: 80px; height: 80px; background: rgba(239,68,68,0.1); color: #ef4444; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 30px;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <h2 style="font-size: 2rem; margin-bottom: 15px; color: #ef4444;">Payment Failed</h2>
                    <p style="color: var(--text-secondary); font-size: 1rem; margin-bottom: 10px;">There was a problem saving your order. Please try again.</p>
                    <p style="background: rgba(239,68,68,0.07); border: 1px solid #ef444460; border-radius: 8px; padding: 12px; font-size: 0.85rem; color: #ef4444; margin-bottom: 30px; word-break: break-all;">
                        <?php echo htmlspecialchars(mysqli_error($dbc)); ?>
                    </p>
                    <a href="checkout.php" class="btn btn-primary btn-large">Try Again</a>
                </div>
            </div>
        </section>
        <?php
        include 'footer.php';
    }
} else {
    header("Location: index.php");
    exit();
}
?>
