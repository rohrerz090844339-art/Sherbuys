<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysqli_connect.php';

$selected_brand_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$selected_brand = null;
if ($selected_brand_id > 0) {
    $brand_query = mysqli_prepare($dbc, "SELECT * FROM brands WHERE id = ?");
    mysqli_stmt_bind_param($brand_query, 'i', $selected_brand_id);
    mysqli_stmt_execute($brand_query);
    $res = mysqli_stmt_get_result($brand_query);
    $selected_brand = mysqli_fetch_assoc($res);
    mysqli_stmt_close($brand_query);
}

$products = [];
if ($selected_brand) {
    $prod_query = mysqli_prepare($dbc, "SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.brand_id = ? ORDER BY p.id DESC");
    mysqli_stmt_bind_param($prod_query, 'i', $selected_brand_id);
    mysqli_stmt_execute($prod_query);
    $res = mysqli_stmt_get_result($prod_query);
    while ($row = mysqli_fetch_assoc($res)) {
        $products[] = $row;
    }
    mysqli_stmt_close($prod_query);
} else {
    $res = mysqli_query($dbc, "SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id ORDER BY p.id DESC");
    while ($row = mysqli_fetch_assoc($res)) {
        $products[] = $row;
    }
}

$brands = [];
$brands_res = mysqli_query($dbc, "SELECT b.*, COUNT(p.id) AS product_count FROM brands b LEFT JOIN products p ON b.id = p.brand_id GROUP BY b.id ORDER BY b.name ASC");
if ($brands_res) {
    while ($row = mysqli_fetch_assoc($brands_res)) {
        $brands[] = $row;
    }
}

include 'header.php';
?>

<section class="products-section" style="padding: 150px 0 100px; min-height: 80vh;">
    <div class="container">
        
        <div class="section-header" style="margin-bottom: 50px;">
            <?php if ($selected_brand): ?>
                <h2>Products by <span><?php echo htmlspecialchars($selected_brand['name']); ?></span></h2>
                <p><?php echo htmlspecialchars($selected_brand['description'] ?? 'Premium technology from our trusted partner.'); ?></p>
            <?php else: ?>
                <h2>Shop by <span>Brand</span></h2>
                <p>Browse cutting-edge electronics from the world's leading tech makers.</p>
            <?php endif; ?>
        </div>

        <div class="products-layout" style="display: grid; grid-template-columns: 280px 1fr; gap: 40px; align-items: start;">
            
            <aside class="products-sidebar" style="background: var(--bg-card); padding: 30px; border-radius: 16px; border: 1px solid var(--border-color);">
                <h3 style="color: white; margin-bottom: 20px; font-size: 1.2rem;">All Brands</h3>
                <ul class="sidebar-list" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
                    <li>
                        <a href="brands.php" style="display: flex; justify-content: space-between; align-items: center; text-decoration: none; padding: 8px 12px; border-radius: 8px; color: <?php echo !$selected_brand ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; background: <?php echo !$selected_brand ? 'rgba(99, 102, 241, 0.08)' : 'transparent'; ?>; font-weight: <?php echo !$selected_brand ? '600' : 'normal'; ?>;">
                            <span>All Brands</span>
                        </a>
                    </li>
                    <?php foreach ($brands as $b): ?>
                        <li>
                            <a href="brands.php?id=<?php echo $b['id']; ?>" style="display: flex; justify-content: space-between; align-items: center; text-decoration: none; padding: 8px 12px; border-radius: 8px; color: <?php echo ($selected_brand && $selected_brand['id'] == $b['id']) ? 'var(--accent-primary)' : 'var(--text-secondary)'; ?>; background: <?php echo ($selected_brand && $selected_brand['id'] == $b['id']) ? 'rgba(99, 102, 241, 0.08)' : 'transparent'; ?>; font-weight: <?php echo ($selected_brand && $selected_brand['id'] == $b['id']) ? '600' : 'normal'; ?>;">
                                <span><?php echo htmlspecialchars($b['name']); ?></span>
                                <span style="font-size: 0.8rem; background: var(--bg-secondary); padding: 2px 8px; border-radius: 12px; color: var(--text-primary);"><?php echo $b['product_count']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div>
                <?php if (empty($products)): ?>
                    <div style="background:var(--bg-card); padding:60px 40px; border-radius:16px; border:1px solid var(--border-color); text-align:center;">
                        <h3 style="margin-bottom: 20px;">No products available</h3>
                        <p style="color:var(--text-secondary); margin-bottom: 30px;">There are no products listed under this brand yet.</p>
                        <a href="brands.php" class="btn btn-primary btn-large">View All Brands</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" id="product-<?php echo htmlspecialchars($product['id']); ?>">
                                <div class="product-image-container" onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'" style="cursor: pointer;">
                                    <?php if(!empty($product['badge'])): ?>
                                        <span class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></span>
                                    <?php endif; ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="product-info">
                                    <span class="product-category" onclick="window.location.href='brands.php?id=<?php echo $product['brand_id']; ?>'" style="cursor: pointer; color: var(--accent-primary);"><?php echo htmlspecialchars($product['brand_name'] ?? 'Brand'); ?></span>
                                    <h3 class="product-name" onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'" style="cursor: pointer;"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-desc"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <div class="product-bottom">
                                        <span class="product-price">₱<?php echo number_format($product['price'], 2); ?></span>
                                        <button class="btn btn-primary btn-small add-to-cart-btn" data-id="<?php echo htmlspecialchars($product['id']); ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo htmlspecialchars($product['price']); ?>">Add to Cart</button>
                                    </div>
                                </div>
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
    .products-layout {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'footer.php'; ?>
