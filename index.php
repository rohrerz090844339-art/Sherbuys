<?php 
include 'header.php'; 
include 'products.php';
?>

<section class="hero-section">
    <div class="container hero-container">
        <div class="hero-content">
            <span class="hero-subtitle">The Future is Here</span>
            <h1 class="hero-title">Elevate Your <span>Digital</span> Experience</h1>
            <p class="hero-description">Discover our curated collection of premium technology products designed to integrate seamlessly into your lifestyle.</p>
            <div class="hero-buttons">
                <a href="#products" class="btn btn-primary btn-large">Shop Now</a>
                <a href="about.php" class="btn btn-outline btn-large">Learn More</a>
            </div>
        </div>
        <div class="hero-image-wrapper">
            <div class="glow-orb"></div>
            <img src="assets/images/laptop.png" alt="Premium Laptop" class="hero-image">
        </div>
    </div>
</section>

<section id="products" class="products-section">
    <div class="container">
        <div class="section-header">
            <h2>Featured Technology</h2>
            <p>Hand-picked premium devices for uncompromising performance.</p>
        </div>
        <div class="products-layout">
            <aside class="products-sidebar">
                <h3>Categories</h3>
                <ul class="sidebar-list category-filters">
                    <li>
                        <a href="#" data-filter="all" class="active">
                            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                            All Products
                        </a>
                    </li>
                    <li>
                        <a href="#" data-filter="Computers">
                            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                            Computers
                        </a>
                    </li>
                    <li>
                        <a href="#" data-filter="Phones">
                            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                            Phones
                        </a>
                    </li>
                    <li>
                        <a href="#" data-filter="Audio">
                            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                            Audio
                        </a>
                    </li>
                </ul>
                
                <h3>Top Products</h3>
                <ul class="sidebar-list">
                    <?php foreach ($products as $product): ?>
                        <li>
                            <a href="#product-<?php echo $product['id']; ?>">
                                <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" id="product-<?php echo htmlspecialchars($product['id']); ?>" data-category="<?php echo htmlspecialchars($product['category']); ?>">
                        <div class="product-image-container">
                            <?php if(!empty($product['badge'])): ?>
                                <span class="product-badge"><?php echo htmlspecialchars($product['badge']); ?></span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-desc"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-bottom">
                                <span class="product-price">₱<?php echo number_format($product['price'], 2); ?></span>
                                <button class="btn btn-primary btn-small add-to-cart-btn" data-id="<?php echo htmlspecialchars($product['id']); ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo htmlspecialchars($product['price']); ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterLinks = document.querySelectorAll('.category-filters a');
    const productCards = document.querySelectorAll('.product-card');

    filterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            filterLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            
            productCards.forEach(card => {
                if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    const topProductLinks = document.querySelectorAll('.sidebar-list:not(.category-filters) a');
    topProductLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            productCards.forEach(card => {
                card.style.display = 'block';
            });
            
            filterLinks.forEach(l => l.classList.remove('active'));
            const allFilter = document.querySelector('.category-filters a[data-filter="all"]');
            if (allFilter) allFilter.classList.add('active');
        });
    });
});
</script>

<?php include 'footer.php'; ?>
