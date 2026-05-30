<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['quantity']) && is_numeric($item['quantity'])) {
            $cart_count += (int)$item['quantity'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sherbuys - Premium Tech E-Commerce</title>
    <meta name="description" content="Discover the latest in premium technology.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="main-header">
        <div class="container header-container">
            <a href="index.php" class="logo">Sher<span>buys</span></a>
            <nav class="main-nav">
                <ul class="nav-list">
                    <?php if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true): ?>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#products">Products</a></li>
                        <li><a href="brands.php">Brands</a></li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <li><a href="purchases.php">Purchases</a></li>
                    <?php endif; ?>
                    <?php if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true): ?>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <div class="account-indicator" style="display: flex; align-items: center; gap: 10px; color: var(--text-primary); font-weight: 600;">
                        <span style="color: #ef4444; border: 1px solid #ef4444; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; text-transform: uppercase;">Admin Mode</span>
                        <a href="admin_dashboard.php" class="btn btn-outline btn-small">Dashboard</a>
                        <a href="admin_products.php" class="btn btn-outline btn-small">Inventory</a>
                        <a href="admin_customers.php" class="btn btn-outline btn-small">Customers</a>
                        <a href="logout.php" class="btn btn-outline btn-small" style="border-color: #ef4444; color: #ef4444;">Logout</a>
                    </div>
                <?php elseif(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <div class="account-indicator" style="display: flex; align-items: center; gap: 10px; color: var(--text-primary); font-weight: 600;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'My Account'); ?></span>
                        <a href="logout.php" class="btn btn-outline btn-small" style="margin-left: 10px;">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-outline">Register</a>
                <?php endif; ?>
                <a href="cart.php" class="btn btn-primary">Cart (<span class="cart-count-display"><?php echo $cart_count; ?></span>)</a>
            </div>
        </div>
    </header>
    <main class="main-content">
