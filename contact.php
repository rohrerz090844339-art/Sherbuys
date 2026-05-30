<?php include 'header.php'; ?>
<section class="hero-section" style="padding: 150px 0 100px;">
    <div class="container" style="max-width: 600px;">
        <h1 class="hero-title" style="text-align:center;">Contact <span>Us</span></h1>
        <form class="newsletter-form" style="display:flex; flex-direction:column; gap:20px; background:var(--bg-card); padding:40px; border-radius:16px; border:1px solid var(--border-color);">
            <input type="text" placeholder="Your Name" style="border-radius:8px; width:100%;" required>
            <input type="email" placeholder="Your Email" style="border-radius:8px; width:100%;" required>
            <textarea placeholder="Your Message" rows="5" style="padding:12px 15px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; color:var(--text-primary); font-family:var(--font-main); outline:none; width:100%; resize:vertical;" required></textarea>
            <button type="submit" class="btn btn-primary" style="width:100%; border-radius:8px; padding:12px;">Send Message</button>
        </form>
    </div>
</section>
<?php include 'footer.php'; ?>
