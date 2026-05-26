<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'header.php'; 
require_once 'mysqli_connect.php';

$orders = [];

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $customer_id = $_SESSION['customer_id'];
    
    $query = "SELECT o.id, DATE(o.created_at) AS date, o.total_amount AS total, s.name AS status,
                     pm.name AS payment_method,
                     o.shipping_name, o.shipping_address, o.shipping_city, o.shipping_zip,
                     GROUP_CONCAT(CONCAT(oi.product_name, ' (x', oi.quantity, ')') SEPARATOR ', ') AS items
              FROM orders o
              JOIN order_statuses s ON o.status_id = s.id
              LEFT JOIN payment_modes pm ON o.payment_mode_id = pm.id
              LEFT JOIN order_items oi ON o.id = oi.order_id
              WHERE o.customer_id = ?
              GROUP BY o.id
              ORDER BY o.created_at DESC";
              
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, 'i', $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = [
            'id'             => 'ORD-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT),
            'raw_id'         => $row['id'],
            'date'           => $row['date'],
            'total'          => $row['total'],
            'status'         => $row['status'],
            'payment_method' => $row['payment_method'] ?? 'N/A',
            'shipping_name'  => $row['shipping_name'],
            'shipping_city'  => $row['shipping_city'],
            'items'          => $row['items'] ? explode(', ', $row['items']) : []
        ];
    }
    mysqli_stmt_close($stmt);
}
?>
<section class="products-section" style="padding: 150px 0 100px; min-height: 60vh;">
    <div class="container">
        <div class="section-header">
            <h2>Your <span>Purchases</span></h2>
            <p>View your order history and status.</p>
        </div>
        
        <?php if(!isset($_SESSION['logged_in'])): ?>
            <div style="background:var(--bg-card); padding:60px 40px; border-radius:16px; border:1px solid var(--border-color); text-align:center;">
                <h3 style="margin-bottom: 20px;">Please login to view purchases</h3>
                <a href="login.php" class="btn btn-primary btn-large">Login Now</a>
            </div>
        <?php else: ?>
            <?php if(empty($orders)): ?>
                <div style="background:var(--bg-card); padding:60px 40px; border-radius:16px; border:1px solid var(--border-color); text-align:center;">
                    <h3 style="margin-bottom: 20px;">You haven't made any purchases yet</h3>
                    <p style="color:var(--text-secondary); margin-bottom: 30px;">Once you complete a checkout, your order will show up here.</p>
                    <a href="index.php#products" class="btn btn-primary btn-large">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="purchases-container" style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach($orders as $order): ?>
                        <div class="order-card" style="background:var(--bg-card); padding: 25px 30px; border-radius: 16px; border:1px solid var(--border-color); transition: all 0.3s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
                                <!-- Left: Order Info -->
                                <div style="flex: 2; min-width: 220px;">
                                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px; flex-wrap: wrap;">
                                        <h3 style="font-size: 1.3rem; margin: 0;"><?php echo htmlspecialchars($order['id']); ?></h3>
                                        <?php 
                                            $badgeColor = '#10b981';
                                            if ($order['status'] === 'Cancelled' || $order['status'] === 'Disapproved') {
                                                $badgeColor = '#ef4444';
                                            } elseif ($order['status'] === 'Pending' || $order['status'] === 'Processing') {
                                                $badgeColor = '#f59e0b';
                                            }
                                        ?>
                                        <span style="background: <?php echo $badgeColor; ?>20; color: <?php echo $badgeColor; ?>; border: 1px solid <?php echo $badgeColor; ?>; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </div>
                                    <p style="color: var(--text-secondary); margin-bottom: 4px; font-size: 0.9rem;">📅 Date: <?php echo htmlspecialchars($order['date']); ?></p>
                                    <p style="color: var(--text-secondary); margin-bottom: 4px; font-size: 0.9rem;">💳 Payment: <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($order['payment_method']); ?></strong></p>
                                    <?php if($order['shipping_name']): ?>
                                    <p style="color: var(--text-secondary); margin-bottom: 4px; font-size: 0.9rem;">📦 Ship to: <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($order['shipping_name'] . ', ' . $order['shipping_city']); ?></strong></p>
                                    <?php endif; ?>
                                    <p style="color: var(--text-primary); font-size: 0.92rem; margin-top: 8px;">🛒 Items: <?php echo htmlspecialchars(implode(', ', $order['items'])); ?></p>
                                </div>
                                <!-- Right: Total -->
                                <div style="text-align: right; min-width: 130px;">
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 4px;">Order Total</p>
                                    <span style="font-weight: 800; font-size: 1.6rem; <?php if($order['status'] === 'Cancelled') echo 'text-decoration: line-through; opacity: 0.5;'; else echo 'color: var(--accent-primary);'; ?>">
                                        ₱<?php echo number_format($order['total'], 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    border-color: rgba(255,255,255,0.15);
}
</style>

<?php include 'footer.php'; ?>
