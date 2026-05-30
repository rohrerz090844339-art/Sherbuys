<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once 'mysqli_connect.php';

$update_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $status_id = (int)$_POST['status_id'];
    
    $update_stmt = mysqli_prepare($dbc, "UPDATE orders SET status_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, 'ii', $status_id, $order_id);
    if (mysqli_stmt_execute($update_stmt)) {
        $update_message = "Order #$order_id status successfully updated!";
    }
    mysqli_stmt_close($update_stmt);
}

$revenue_q = mysqli_query($dbc, "SELECT SUM(total_amount) AS total FROM orders WHERE status_id != 4");
$revenue_row = mysqli_fetch_assoc($revenue_q);
$total_revenue = $revenue_row['total'] ?? 0.00;

$orders_q = mysqli_query($dbc, "SELECT COUNT(*) AS count FROM orders");
$orders_row = mysqli_fetch_assoc($orders_q);
$total_orders = $orders_row['count'] ?? 0;

$users_q = mysqli_query($dbc, "SELECT COUNT(*) AS count FROM customers");
$users_row = mysqli_fetch_assoc($users_q);
$total_users = $users_row['count'] ?? 0;

$products_q = mysqli_query($dbc, "SELECT COUNT(*) AS count FROM products");
$products_row = mysqli_fetch_assoc($products_q);
$total_products = $products_row['count'] ?? 0;

$orders = [];
$orders_query = "
    SELECT o.id, DATE(o.created_at) AS date, o.total_amount, o.shipping_name, o.shipping_city,
           s.name AS status, s.id AS status_id, pm.name AS payment, c.fullname AS customer_name,
           GROUP_CONCAT(CONCAT(oi.product_name, ' (x', oi.quantity, ')') SEPARATOR ', ') AS items
    FROM orders o
    JOIN order_statuses s ON o.status_id = s.id
    LEFT JOIN payment_modes pm ON o.payment_mode_id = pm.id
    JOIN customers c ON o.customer_id = c.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
";
$orders_result = mysqli_query($dbc, $orders_query);
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $row;
    }
    mysqli_free_result($orders_result);
}

$statuses = [];
$status_result = mysqli_query($dbc, "SELECT * FROM order_statuses ORDER BY id ASC");
if ($status_result) {
    while ($row = mysqli_fetch_assoc($status_result)) {
        $statuses[] = $row;
    }
    mysqli_free_result($status_result);
}

$low_stock_products = [];
$stock_result = mysqli_query($dbc, "SELECT id, name, stock FROM products WHERE stock < 5 ORDER BY stock ASC");
if ($stock_result) {
    while ($row = mysqli_fetch_assoc($stock_result)) {
        $low_stock_products[] = $row;
    }
    mysqli_free_result($stock_result);
}

$customers = [];
$customers_result = mysqli_query($dbc, "SELECT id, fullname, email, phone, address, DATE(created_at) AS reg_date FROM customers ORDER BY created_at DESC");
if ($customers_result) {
    while ($row = mysqli_fetch_assoc($customers_result)) {
        $customers[] = $row;
    }
    mysqli_free_result($customers_result);
}

include 'header.php';
?>

<section class="products-section" style="padding: 150px 0 100px; min-height: 80vh;">
    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="font-size: 2.8rem; margin: 0; color: white;">Admin <span>Dashboard</span></h1>
                <p style="color: var(--text-secondary); margin-top: 5px;">Manage products, orders, and system settings.</p>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="index.php" class="btn btn-outline">View Storefront</a>
                <a href="logout.php" class="btn btn-outline" style="border-color: #ef4444; color: #ef4444;">Console Logout</a>
            </div>
        </div>

        <?php if($update_message): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 35px;">
                🎉 <?php echo htmlspecialchars($update_message); ?>
            </div>
        <?php endif; ?>

        <!-- Key Metrics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
            
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 25px; border-radius: 16px;">
                <span style="color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">💰 Total Revenue</span>
                <h2 style="font-size: 2.2rem; margin: 10px 0 0; color: var(--accent-primary);">₱<?php echo number_format($total_revenue, 2); ?></h2>
            </div>
            
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 25px; border-radius: 16px;">
                <span style="color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">📦 Total Orders</span>
                <h2 style="font-size: 2.2rem; margin: 10px 0 0; color: white;"><?php echo $total_orders; ?></h2>
            </div>
            
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 25px; border-radius: 16px;">
                <span style="color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">👥 Registered Customers</span>
                <h2 style="font-size: 2.2rem; margin: 10px 0 0; color: white;"><?php echo $total_users; ?></h2>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 25px; border-radius: 16px;">
                <span style="color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">⚡ Active Products</span>
                <h2 style="font-size: 2.2rem; margin: 10px 0 0; color: white;"><?php echo $total_products; ?></h2>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2.5fr 1fr; gap: 30px; align-items: start; margin-bottom: 40px;">
            
            <!-- Left: Orders List -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 30px;">
                <h2 style="font-size: 1.5rem; margin-bottom: 20px; color: white;">Manage Order Transactions</h2>
                
                <?php if(empty($orders)): ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: 40px 0;">No order transactions exist yet.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary);">
                                    <th style="padding: 12px 10px;">ID</th>
                                    <th style="padding: 12px 10px;">Buyer</th>
                                    <th style="padding: 12px 10px;">Total</th>
                                    <th style="padding: 12px 10px;">Payment</th>
                                    <th style="padding: 12px 10px;">Status</th>
                                    <th style="padding: 12px 10px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                    <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.02)';" onmouseout="this.style.background='transparent';">
                                        <td style="padding: 15px 10px; font-weight: bold; color: white;">
                                            ORD-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
                                            <span style="display: block; font-size: 0.75rem; font-weight: normal; color: var(--text-secondary);"><?php echo $order['date']; ?></span>
                                        </td>
                                        <td style="padding: 15px 10px;">
                                            <strong style="color: white;"><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                            <span style="display: block; font-size: 0.8rem; color: var(--text-secondary); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($order['items']); ?>">
                                                <?php echo htmlspecialchars($order['items']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 15px 10px; font-weight: 600; color: var(--accent-primary);">
                                            ₱<?php echo number_format($order['total_amount'], 2); ?>
                                        </td>
                                        <td style="padding: 15px 10px; color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($order['payment'] ?? 'N/A'); ?>
                                        </td>
                                        <td style="padding: 15px 10px;">
                                            <?php 
                                                $badgeColor = '#10b981';
                                                if ($order['status'] === 'Cancelled' || $order['status'] === 'Disapproved') {
                                                    $badgeColor = '#ef4444';
                                                } elseif ($order['status'] === 'Pending' || $order['status'] === 'Processing') {
                                                    $badgeColor = '#f59e0b';
                                                }
                                            ?>
                                            <span style="background: <?php echo $badgeColor; ?>20; color: <?php echo $badgeColor; ?>; border: 1px solid <?php echo $badgeColor; ?>; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; display: inline-block;">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 15px 10px; text-align: center;">
                                            <form method="POST" action="admin_dashboard.php" style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status_id" style="background: var(--bg-secondary); color: white; border: 1px solid var(--border-color); border-radius: 6px; padding: 6px; font-size: 0.85rem; outline: none; cursor: pointer;">
                                                    <?php foreach ($statuses as $st): ?>
                                                        <option value="<?php echo $st['id']; ?>" <?php if ($st['id'] == $order['status_id']) echo 'selected'; ?>>
                                                            <?php echo htmlspecialchars($st['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn btn-primary btn-small" style="padding: 6px 12px; font-size: 0.85rem;">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Low Stock Warnings -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 30px;">
                <h2 style="font-size: 1.3rem; margin-bottom: 20px; color: white;">⚠️ Stock Warning</h2>
                <?php if (empty($low_stock_products)): ?>
                    <p style="color: #10b981; font-size: 0.95rem; font-weight: 600;">✅ All products are in stock!</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($low_stock_products as $p): ?>
                            <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span style="font-weight: 600; display: block; font-size: 0.92rem; color: white;"><?php echo htmlspecialchars($p['name']); ?></span>
                                    <span style="font-size: 0.8rem; color: var(--text-secondary);">Product ID: #<?php echo $p['id']; ?></span>
                                </div>
                                <span style="background: #ef4444; color: white; border-radius: 6px; padding: 4px 10px; font-weight: bold; font-size: 0.85rem;">
                                    <?php echo $p['stock']; ?> left
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</section>

<style>
@media (max-width: 992px) {
    div[style*="grid-template-columns: 2.5fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'footer.php'; ?>
