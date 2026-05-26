<?php
DEFINE('DB_USER', 'root');
DEFINE('DB_PASSWORD', '');
DEFINE('DB_HOST', '127.0.0.1');
DEFINE('DB_NAME', 'ecommerces');

mysqli_report(MYSQLI_REPORT_OFF);

$dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, '', 3306);

if (!$dbc) {
    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, '', 3307);
}

if (!$dbc) {
    $dbc = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, '');
}

if (!$dbc) {
    die('Could not connect to MySQL: ' . mysqli_connect_error());
}

$db_selected = mysqli_select_db($dbc, DB_NAME);
if (!$db_selected) {
    $create_db = mysqli_query($dbc, "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    if ($create_db) {
        mysqli_select_db($dbc, DB_NAME);
    } else {
        die('Could not select or create database: ' . mysqli_error($dbc));
    }
}

mysqli_set_charset($dbc, 'utf8');

$table_check = mysqli_query($dbc, "SHOW TABLES LIKE 'products'");
if (mysqli_num_rows($table_check) == 0) {
    $sql_file = __DIR__ . '/ecommerces.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        $sql = preg_replace('/--.*$/m', '', $sql);
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                mysqli_query($dbc, $query);
            }
        }
    }
}

// Ensure users from users.json are imported if they don't exist
$users_file = __DIR__ . '/users.json';
if (file_exists($users_file)) {
    $users_data = json_decode(file_get_contents($users_file), true);
    if (is_array($users_data)) {
        foreach ($users_data as $user) {
            $fullname = $user['fullname'] ?? '';
            $email = $user['email'] ?? '';
            $password = $user['password'] ?? '';
            if ($fullname && $email && $password) {
                $stmt = mysqli_prepare($dbc, "INSERT IGNORE INTO customers (fullname, email, password) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'sss', $fullname, $email, $password);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
