<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'header.php'; 
?>
<section class="products-section" style="padding: 150px 0 100px; min-height: 60vh;">
    <div class="container">
        <div class="section-header">
            <h2>Your <span>Cart</span></h2>
            <p>Review your selected items before checkout.</p>
        </div>
        
        <?php if(empty($_SESSION['cart'])): ?>
            <div style="background:var(--bg-card); padding:60px 40px; border-radius:16px; border:1px solid var(--border-color); text-align:center;">
                <h3 style="margin-bottom: 20px;">Your cart is currently empty</h3>
                <p style="color:var(--text-secondary); margin-bottom: 30px;">Looks like you haven't added any premium tech to your cart yet.</p>
                <a href="index.php#products" class="btn btn-primary btn-large">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-container" style="display: flex; flex-direction: column; gap: 20px;">
                <?php 
                $total = 0;
                foreach($_SESSION['cart'] as $id => $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <div class="cart-item" style="display: flex; align-items: center; justify-content: space-between; background:var(--bg-card); padding: 20px 30px; border-radius: 16px; border:1px solid var(--border-color);">
                        <div style="flex: 2;">
                            <h3 style="font-size: 1.2rem; margin-bottom: 5px;"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p style="color: var(--text-secondary);">₱<?php echo number_format($item['price'], 2); ?> each</p>
                        </div>
                        <div style="flex: 1; text-align: center;">
                            <span style="font-weight: 600; font-size: 1.1rem;">Qty: <?php echo $item['quantity']; ?></span>
                        </div>
                        <div style="flex: 1; text-align: right; display: flex; align-items: center; justify-content: flex-end; gap: 15px;">
                            <span style="font-weight: 800; font-size: 1.2rem; color: var(--accent-primary);">₱<?php echo number_format($subtotal, 2); ?></span>
                            <button class="btn btn-outline btn-small remove-cart-btn" data-id="<?php echo htmlspecialchars($id); ?>" style="border-color: #ef4444; color: #ef4444; padding: 6px 12px; font-size: 0.8rem;">Cancel</button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-summary" style="margin-top: 30px; display: flex; justify-content: flex-end; align-items: center; gap: 30px; padding: 30px; background: rgba(99, 102, 241, 0.05); border-radius: 16px; border: 1px solid var(--accent-glow);">
                    <div style="text-align: right;">
                        <span style="color: var(--text-secondary); font-size: 1.1rem; margin-right: 15px;">Total:</span>
                        <span style="font-size: 2rem; font-weight: 800;">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary btn-large">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const removeBtns = document.querySelectorAll('.remove-cart-btn');
    removeBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const id = btn.getAttribute('data-id');
            const cartItem = btn.closest('.cart-item');
            
            btn.innerHTML = 'Canceling...';
            btn.style.opacity = '0.5';
            
            try {
                const response = await fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                
                const result = await response.json();
                if (result.success) {
                    cartItem.style.transition = 'all 0.3s ease';
                    cartItem.style.opacity = '0';
                    cartItem.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                }
            } catch (error) {
                console.error('Error removing item:', error);
                btn.innerHTML = 'Cancel';
                btn.style.opacity = '1';
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
