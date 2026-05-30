<?php

require_once 'mysqli_connect.php';

$products = [];
$result = mysqli_query($dbc, "SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id ORDER BY p.id ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_free_result($result);
}
?>
