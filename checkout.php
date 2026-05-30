<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'header.php'; 

if(empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<section class="checkout-section" style="padding: 150px 0 100px; min-height: 60vh;">
    <div class="container">
        <div class="section-header">
            <h2>Secure <span>Checkout</span></h2>
            <p>Select your payment method and complete your purchase.</p>
        </div>

        <div class="checkout-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start;">
            <div class="payment-methods" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 30px;">
                <h3 style="margin-bottom: 20px; font-size: 1.4rem;">Payment Method</h3>
                <form action="process_payment.php" method="POST" id="checkoutForm">
                    
                    <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
                        <label style="display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid var(--border-color); border-radius: 12px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--accent-primary)';" onmouseout="this.style.borderColor='var(--border-color)';">
                            <input type="radio" name="payment_method" value="credit_card" required style="accent-color: var(--accent-primary); width: 20px; height: 20px;">
                            <div style="flex: 1;">
                                <span style="font-weight: 600; font-size: 1.1rem; display: block;">Credit / Debit Card</span>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Visa, Mastercard, Amex</span>
                            </div>
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-secondary);"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        </label>

                        <label style="display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid var(--border-color); border-radius: 12px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--accent-primary)';" onmouseout="this.style.borderColor='var(--border-color)';">
                            <input type="radio" name="payment_method" value="paypal" style="accent-color: var(--accent-primary); width: 20px; height: 20px;">
                            <div style="flex: 1;">
                                <span style="font-weight: 600; font-size: 1.1rem; display: block;">PayPal</span>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Pay securely with your PayPal account</span>
                            </div>
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #00457C;"><path d="M7 21h4.5l.5-3.5 1-.5h3c3.5 0 5-2 5-5s-1.5-4-4.5-4h-5L7 21z"></path><path d="M10 13h4.5c2.5 0 4-1 4-3s-1.5-3-3.5-3H9L7 16h4.5l-1.5 5z"></path></svg>
                        </label>

                        <label style="display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid var(--border-color); border-radius: 12px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--accent-primary)';" onmouseout="this.style.borderColor='var(--border-color)';">
                            <input type="radio" name="payment_method" value="crypto" style="accent-color: var(--accent-primary); width: 20px; height: 20px;">
                            <div style="flex: 1;">
                                <span style="font-weight: 600; font-size: 1.1rem; display: block;">Cryptocurrency</span>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Bitcoin, Ethereum, USDT</span>
                            </div>
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #F7931A;"><circle cx="12" cy="12" r="10"></circle><path d="M8 8h6a2 2 0 1 1 0 4h-4"></path><path d="M10 12h5a2 2 0 1 1 0 4h-5"></path><path d="M10 7v10"></path><path d="M14 7v10"></path></svg>
                        </label>

                        <label style="display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid var(--border-color); border-radius: 12px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--accent-primary)';" onmouseout="this.style.borderColor='var(--border-color)';">
                            <input type="radio" name="payment_method" value="cod" style="accent-color: var(--accent-primary); width: 20px; height: 20px;">
                            <div style="flex: 1;">
                                <span style="font-weight: 600; font-size: 1.1rem; display: block;">Cash on Delivery</span>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Pay when your order arrives</span>
                            </div>
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #22c55e;"><rect x="1" y="6" width="22" height="13" rx="2"></rect><path d="M1 10h22"></path><circle cx="12" cy="15" r="2"></circle></svg>
                        </label>

                        <label style="display: flex; align-items: center; gap: 15px; padding: 20px; border: 1px solid var(--border-color); border-radius: 12px; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--accent-primary)';" onmouseout="this.style.borderColor='var(--border-color)';">
                            <input type="radio" name="payment_method" value="gcash" style="accent-color: var(--accent-primary); width: 20px; height: 20px;">
                            <div style="flex: 1;">
                                <span style="font-weight: 600; font-size: 1.1rem; display: block;">GCash</span>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Pay via GCash e-wallet</span>
                            </div>
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #007dff;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path><path d="M12 8v4l3 3"></path></svg>
                        </label>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <h4 style="margin-top: 10px; margin-bottom: 5px; font-size: 1.1rem;">Billing Details</h4>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-secondary);">Full Name</label>
                            <input type="text" name="shipping_name" placeholder="John Doe" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: white; outline: none; font-family: inherit;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; color: var(--text-secondary);">Address</label>
                            <input type="text" name="shipping_address" placeholder="123 Tech Lane" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: white; outline: none; font-family: inherit;">
                        </div>
                        <div style="display: flex; gap: 15px;">
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 5px; color: var(--text-secondary);">City</label>
                                <input type="text" name="shipping_city" placeholder="San Francisco" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: white; outline: none; font-family: inherit;">
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 5px; color: var(--text-secondary);">Zip Code</label>
                                <input type="text" name="shipping_zip" placeholder="94105" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: white; outline: none; font-family: inherit;">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="order-summary" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 30px; position: sticky; top: 120px;">
                <h3 style="margin-bottom: 20px; font-size: 1.4rem;">Order Summary</h3>
                <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 25px; max-height: 300px; overflow-y: auto;">
                    <?php 
                    $total = 0;
                    foreach($_SESSION['cart'] as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                            <div>
                                <span style="display: block; font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></span>
                                <span style="font-size: 0.85rem; color: var(--text-secondary);">Qty: <?php echo $item['quantity']; ?></span>
                            </div>
                            <span style="font-weight: 600;">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <span style="color: var(--text-secondary);">Subtotal</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <span style="color: var(--text-secondary);">Shipping</span>
                    <span>Free</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <span style="font-size: 1.2rem; font-weight: 800;">Total</span>
                    <span style="font-size: 1.8rem; font-weight: 800; color: var(--accent-primary);">₱<?php echo number_format($total, 2); ?></span>
                </div>

                <button type="submit" form="checkoutForm" class="btn btn-primary btn-large" style="width: 100%; margin-top: 30px; font-size: 1.2rem;">Complete Purchase</button>
            </div>
        </div>
    </div>
</section>

<style>
@media (max-width: 768px) {
    .checkout-container {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'footer.php'; ?>
