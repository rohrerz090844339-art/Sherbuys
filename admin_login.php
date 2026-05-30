<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'mysqli_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password']  ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = mysqli_prepare($dbc, "SELECT id, username, password FROM admins WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $row['id'];
                $_SESSION['admin_username']  = htmlspecialchars($row['username']);
                
                mysqli_stmt_close($stmt);
                header('Location: admin_dashboard.php');
                exit;
            }
        }
        mysqli_stmt_close($stmt);
        $error = 'Invalid admin email or password.';
    } else {
        $error = 'Please enter both email and password.';
    }
}

include 'header.php'; 
?>
<section class="hero-section" style="padding: 150px 0 100px; min-height: 70vh;">
    <div class="container" style="max-width: 500px;">
        <h1 class="hero-title" style="text-align:center; font-size: 3rem; margin-bottom: 10px;">Admin <span>Portal</span></h1>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 30px;">Access the Sherbuys Management Console</p>
        
        <?php if($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php" class="newsletter-form" style="display:flex; flex-direction:column; gap:20px; background:var(--bg-card); padding:40px; border-radius:16px; border:1px solid var(--border-color);">
            <div>
                <label style="display:block; margin-bottom:8px; color:var(--text-secondary); font-size:0.9rem;">Admin Email</label>
                <input type="email" name="email" placeholder="admin@sherbuys.com" style="border-radius:8px; width:100%;" required>
            </div>
            <div>
                <label style="display:block; margin-bottom:8px; color:var(--text-secondary); font-size:0.9rem;">Password</label>
                <input type="password" name="password" placeholder="••••••••" style="padding:12px 15px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px; color:var(--text-primary); font-family:var(--font-main); outline:none; width:100%;" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; border-radius:8px; padding:12px; font-weight:600;">Log In to Console</button>
            <p style="text-align:center; color:var(--text-secondary); margin-top:10px; font-size:0.9rem;">Default admin login: <strong style="color:white;">admin@sherbuys.com</strong> / <strong style="color:white;">admin123</strong></p>
        </form>
    </div>
</section>
<?php include 'footer.php'; ?>
