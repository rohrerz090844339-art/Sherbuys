<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysqli_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (!empty($email) && !empty($password)) {

        $stmt = mysqli_prepare($dbc, "SELECT id, fullname, password FROM customers WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['logged_in']   = true;
                $_SESSION['customer_id'] = $row['id'];
                $_SESSION['username']    = htmlspecialchars($row['fullname']);
                
                $customer_id = $row['id'];
                mysqli_stmt_close($stmt);
                
                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $pid => $item) {
                        $qty = (int)$item['quantity'];
                        $sync_stmt = mysqli_prepare($dbc, "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
                        mysqli_stmt_bind_param($sync_stmt, 'iiii', $customer_id, $pid, $qty, $qty);
                        mysqli_stmt_execute($sync_stmt);
                        mysqli_stmt_close($sync_stmt);
                    }
                }
                
                $_SESSION['cart'] = [];
                $cart_result = mysqli_query($dbc, "SELECT c.product_id, c.quantity, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.customer_id = $customer_id");
                if ($cart_result) {
                    while ($cart_row = mysqli_fetch_assoc($cart_result)) {
                        $_SESSION['cart'][$cart_row['product_id']] = [
                            'name' => $cart_row['name'],
                            'price' => $cart_row['price'],
                            'quantity' => (int)$cart_row['quantity']
                        ];
                    }
                    mysqli_free_result($cart_result);
                }
                
                header('Location: index.php');
                exit;
            }
        }
        mysqli_stmt_close($stmt);
        $error = 'Invalid email or password. Please try again.';

    } else {
        $error = 'Please enter both email and password.';
    }
}

include 'header.php'; 
?>
<section class="hero-section" style="padding: 150px 0 100px;">
    <div class="container" style="max-width: 500px;">
        <h1 class="hero-title" style="text-align:center; font-size: 3rem;">Welcome <span>Back</span></h1>
        
        <?php if($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="newsletter-form" style="display:flex; flex-direction:column; gap:20px; background:var(--bg-card); padding:40px; border-radius:16px; border:1px solid var(--border-color);">
            <input type="email"    name="email"    placeholder="Email Address" style="border-radius:8px; width:100%;" required>
            <input type="password" name="password" placeholder="Password"      style="padding:12px 15px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; color:var(--text-primary); font-family:var(--font-main); outline:none; width:100%;" required>
            <button type="submit" class="btn btn-primary" style="width:100%; border-radius:8px; padding:12px;">Login</button>
            <p style="text-align:center; color:var(--text-secondary); margin-top:10px;">Don't have an account? <a href="register.php" class="accent">Register here</a></p>
        </form>
    </div>
</section>
<?php include 'footer.php'; ?>
