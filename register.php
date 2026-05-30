<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysqli_connect.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (!empty($fullname) && !empty($email) && !empty($password)) {

        
        $stmt = mysqli_prepare($dbc, "SELECT id FROM customers WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'That email is already registered. Please login.';
        } else {
            mysqli_stmt_close($stmt);

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($dbc, "INSERT INTO customers (fullname, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $fullname, $email, $hash);

            if (mysqli_stmt_execute($stmt)) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        mysqli_stmt_close($stmt);

    } else {
        $error = 'Please fill in all fields.';
    }
}

include 'header.php'; 
?>
<section class="hero-section" style="padding: 150px 0 100px;">
    <div class="container" style="max-width: 500px;">
        <h1 class="hero-title" style="text-align:center; font-size: 3rem;">Create <span>Account</span></h1>
        
        <?php if($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($success); ?>
                <br><a href="login.php" style="color:#10b981; font-weight:700;">Click here to login &rarr;</a>
            </div>
        <?php endif; ?>

        <?php if(!$success): ?>
        <form method="POST" action="register.php" class="newsletter-form" style="display:flex; flex-direction:column; gap:20px; background:var(--bg-card); padding:40px; border-radius:16px; border:1px solid var(--border-color);">
            <input type="text"     name="fullname" placeholder="Full Name"      style="border-radius:8px; width:100%;" required>
            <input type="email"    name="email"    placeholder="Email Address"   style="border-radius:8px; width:100%;" required>
            <input type="password" name="password" placeholder="Password"        style="padding:12px 15px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; color:var(--text-primary); font-family:var(--font-main); outline:none; width:100%;" required>
            <button type="submit" class="btn btn-primary" style="width:100%; border-radius:8px; padding:12px;">Register</button>
            <p style="text-align:center; color:var(--text-secondary); margin-top:10px;">Already have an account? <a href="login.php" class="accent">Login here</a></p>
        </form>
        <?php endif; ?>
    </div>
</section>
<?php include 'footer.php'; ?>
