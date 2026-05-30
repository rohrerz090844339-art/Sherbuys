<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysqli_connect.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product = null;
if ($product_id > 0) {
    $stmt = mysqli_prepare($dbc, "SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

if (!$product) {
    header('Location: index.php');
    exit;
}

include 'header.php';
?>

<section class="products-section" style="padding: 180px 0 100px; min-height: 80vh;">
    <div class="container">
        
        <div style="margin-bottom: 30px;">
            <a href="index.php#products" style="color: var(--text-secondary); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Back to Shop
            </a>
        </div>

        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 60px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 24px; padding: 50px; align-items: center;">
            
            <div style="position: relative; text-align: center; background: var(--bg-secondary); padding: 40px; border-radius: 16px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; min-height: 400px;">
                <?php if(!empty($product['badge'])): ?>
                    <span style="position: absolute; top: 20px; left: 20px; background: var(--accent-primary); color: white; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; box-shadow: 0 5px 15px var(--accent-glow);"><?php echo htmlspecialchars($product['badge']); ?></span>
                <?php endif; ?>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 100%; max-height: 450px; object-fit: contain; filter: drop-shadow(0 15px 30px rgba(0,0,0,0.5));">
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <span style="color: var(--accent-primary); font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; font-size: 0.9rem; display: block; margin-bottom: 5px;">
                        <?php echo htmlspecialchars($product['brand_name'] ?? 'Premium Line'); ?>
                    </span>
                    <h1 style="color: white; font-size: 2.8rem; font-weight: 800; margin: 0; line-height: 1.2;"><?php echo htmlspecialchars($product['name']); ?></h1>
                </div>

                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <span style="font-size: 2.2rem; font-weight: 800; color: white;">₱<?php echo number_format($product['price'], 2); ?></span>
                    <span style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-secondary); padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">
                        Category: <?php echo htmlspecialchars($product['category'] ?? 'General'); ?>
                    </span>
                </div>

                <div style="border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 20px 0; margin: 10px 0;">
                    <h3 style="color: white; font-size: 1.1rem; margin-bottom: 10px;">Product Description</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6; font-size: 1.05rem;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <div>
                        <span style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 5px;">Availability</span>
                        <?php if ($product['stock'] > 0): ?>
                            <span style="color: #10b981; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span>
                                In Stock (<?php echo $product['stock']; ?> items remaining)
                            </span>
                        <?php else: ?>
                            <span style="color: #ef4444; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                <span style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                                Temporarily Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <?php if ($product['stock'] > 0): ?>
                        <button class="btn btn-primary btn-large add-to-cart-btn" data-id="<?php echo htmlspecialchars($product['id']); ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo htmlspecialchars($product['price']); ?>" style="width: 100%; border-radius: 12px; padding: 16px; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="btn btn-outline btn-large" style="width: 100%; border-radius: 12px; padding: 16px; font-size: 1.1rem; cursor: not-allowed; opacity: 0.5;" disabled>
                            Sold Out
                        </button>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    </div>
</section>

<style>
@media (max-width: 992px) {
    div[style*="grid-template-columns: 1.2fr 1fr"] {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
        padding: 30px !important;
    }
}
</style>

<?php include 'footer.php'; ?>
