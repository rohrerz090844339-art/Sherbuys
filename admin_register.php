<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysqli_connect.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        $stmt = mysqli_prepare($dbc, "SELECT id FROM admins WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'That email is already registered as an admin.';
        } else {
            mysqli_stmt_close($stmt);

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($dbc, "INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hash);

            if (mysqli_stmt_execute($stmt)) {
                $success = 'Admin registration successful! You can now log in.';
            } else {
                $error = 'Admin registration failed. Please try again.';
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Please fill in all fields.';
    }
}

include 'header.php'; 
?>
<section class="hero-section" style="padding: 150px 0 100px; min-height: 70vh;">
    <div class="container" style="max-width: 500px;">
        <h1 class="hero-title" style="text-align:center; font-size: 3rem; margin-bottom: 10px;">Admin <span>Register</span></h1>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 30px;">Create a new administrative account</p>
        
        <?php if($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($success); ?>
                <br><a href="admin_login.php" style="color:#10b981; font-weight:700; text-decoration: underline;">Go to Admin Login &rarr;</a>
            </div>
        <?php endif; ?>

        <?php if(!$success): ?>
        <form method="POST" action="admin_register.php" class="newsletter-form" style="display:flex; flex-direction:column; gap:20px; background:var(--bg-card); padding:40px; border-radius:16px; border:1px solid var(--border-color);">
            <div>
                <label style="display:block; margin-bottom:8px; color:var(--text-secondary); font-size:0.9rem;">Username</label>
                <input type="text" name="username" placeholder="e.g. administrator" style="border-radius:8px; width:100%;" required>
            </div>
            <div>
                <label style="display:block; margin-bottom:8px; color:var(--text-secondary); font-size:0.9rem;">Admin Email</label>
                <input type="email" name="email" placeholder="admin@sherbuys.com" style="border-radius:8px; width:100%;" required>
            </div>
            <div>
                <label style="display:block; margin-bottom:8px; color:var(--text-secondary); font-size:0.9rem;">Password</label>
                <input type="password" name="password" placeholder="••••••••" style="padding:12px 15px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; color:var(--text-primary); font-family:var(--font-main); outline:none; width:100%;" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; border-radius:8px; padding:12px; font-weight:600;">Register Admin</button>
            <p style="text-align:center; color:var(--text-secondary); margin-top:10px; font-size:0.9rem;">Already have an admin account? <a href="admin_login.php" class="accent">Login here</a></p>
        </form>
        <?php endif; ?>
    </div>
</section>
<?php include 'footer.php'; ?>
