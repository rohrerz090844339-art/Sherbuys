    </main>
    <footer class="main-footer">
        <div class="container footer-container">
            <div class="footer-col">
                <h3>Sher<span>buys</span></h3>
                <p>Empowering your future with cutting-edge technology. Experience the premium standard.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#products">Products</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="admin_login.php" style="color: var(--accent-primary);">Admin Portal</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#privacy">Privacy Policy</a></li>
                    <li><a href="#terms">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Newsletter</h4>
                <p>Subscribe for the latest updates and tech news.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">→</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> Sherbuys. All rights reserved.</p>
        </div>
    </footer>

    <a href="cart.php" class="floating-cart" title="View Cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <span class="cart-badge cart-count-display">
            <?php 
            $footer_cart_count = 0;
            if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                    if (is_array($item) && isset($item['quantity']) && is_numeric($item['quantity'])) {
                        $footer_cart_count += (int)$item['quantity'];
                    }
                }
            }
            echo $footer_cart_count;
            ?>
        </span>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
            const cartDisplays = document.querySelectorAll('.cart-count-display');
            
            addToCartBtns.forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    const originalText = btn.innerHTML;
                    btn.innerHTML = 'Adding...';
                    btn.style.opacity = '0.8';
                    
                    const productData = {
                        id: btn.getAttribute('data-id'),
                        name: btn.getAttribute('data-name'),
                        price: btn.getAttribute('data-price')
                    };
                    
                    try {
                        const response = await fetch('add_to_cart.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(productData)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            cartDisplays.forEach(display => {
                                display.textContent = result.total_items;
                                display.style.display = 'inline-block';
                                display.style.transition = 'transform 0.3s ease';
                                display.style.transform = 'scale(1.5)';
                                setTimeout(() => {
                                    display.style.transform = 'scale(1)';
                                }, 300);
                            });
                            
                            btn.innerHTML = 'Added!';
                            btn.style.background = '#10b981';
                            setTimeout(() => {
                                btn.innerHTML = originalText;
                                btn.style.background = '';
                                btn.style.opacity = '1';
                            }, 2000);
                        }
                    } catch (error) {
                        console.error('Error adding to cart:', error);
                        btn.innerHTML = 'Error';
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.style.opacity = '1';
                        }, 2000);
                    }
                });
            });
        });
    </script>
</body>
</html>
