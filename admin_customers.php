<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once 'mysqli_connect.php';

$customers = [];
$query = "
    SELECT c.id, c.fullname, c.email, c.phone, c.address, c.created_at,
           COUNT(o.id) AS total_orders,
           SUM(IFNULL(o.total_amount, 0)) AS total_spend
    FROM customers c
    LEFT JOIN orders o ON c.id = o.customer_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
";
$result = mysqli_query($dbc, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row;
    }
    mysqli_free_result($result);
}

include 'header.php';
?>

<section class="products-section" style="padding: 150px 0 100px; min-height: 80vh;">
    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="font-size: 2.8rem; margin: 0; color: white;">Manage <span>Customers</span></h1>
                <p style="color: var(--text-secondary); margin-top: 5px;">View profiles, registration details, and customer order history.</p>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="admin_dashboard.php" class="btn btn-outline">Back to Dashboard</a>
                <a href="admin_products.php" class="btn btn-outline">Manage Inventory</a>
            </div>
        </div>

        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 30px;">
            <h2 style="color: white; font-size: 1.5rem; margin-bottom: 25px;">Registered Buyers List</h2>
            
            <?php if (empty($customers)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 40px 0;">No registered buyers found.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary);">
                                <th style="padding: 12px 10px;">ID</th>
                                <th style="padding: 12px 10px;">Name</th>
                                <th style="padding: 12px 10px;">Contact Details</th>
                                <th style="padding: 12px 10px;">Shipping Address</th>
                                <th style="padding: 12px 10px; text-align: center;">Orders Placed</th>
                                <th style="padding: 12px 10px; text-align: right;">Total Purchases</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $c): ?>
                                <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.02)';" onmouseout="this.style.background='transparent';">
                                    <td style="padding: 15px 10px; font-weight: bold; color: white;">
                                        CUST-<?php echo str_pad($c['id'], 5, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <strong style="color: white;"><?php echo htmlspecialchars($c['fullname']); ?></strong>
                                        <span style="display: block; font-size: 0.8rem; color: var(--text-secondary);">Member since: <?php echo date("M d, Y", strtotime($c['created_at'])); ?></span>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <span style="display: block; color: var(--text-primary);"><?php echo htmlspecialchars($c['email']); ?></span>
                                        <?php if ($c['phone']): ?>
                                            <span style="display: block; font-size: 0.85rem; color: var(--text-secondary);">📞 <?php echo htmlspecialchars($c['phone']); ?></span>
                                        <?php else: ?>
                                            <span style="font-style: italic; font-size: 0.85rem; color: rgba(255,255,255,0.2);">No phone</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 15px 10px; color: var(--text-secondary); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($c['address'] ?? 'N/A'); ?>">
                                        <?php echo htmlspecialchars($c['address'] ?? 'No address listed'); ?>
                                    </td>
                                    <td style="padding: 15px 10px; text-align: center; font-weight: 600; color: white;">
                                        <?php echo $c['total_orders']; ?>
                                    </td>
                                    <td style="padding: 15px 10px; text-align: right; font-weight: 800; color: var(--accent-primary);">
                                        ₱<?php echo number_format($c['total_spend'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include 'footer.php'; ?>
