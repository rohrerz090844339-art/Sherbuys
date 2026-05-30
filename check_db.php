<?php
mysqli_report(MYSQLI_REPORT_OFF);
$dbc = @mysqli_connect('127.0.0.1', 'root', '', 'ecommerces', 3307);
if (!$dbc) { echo 'Cannot connect'; exit(1); }


$tables_res = mysqli_query($dbc, "SHOW TABLES");
echo "=== ALL TABLES ===\n";
$all_tables = [];
while ($t = mysqli_fetch_row($tables_res)) {
    $all_tables[] = $t[0];
    echo "  " . $t[0] . "\n";
}


echo "\n=== SEARCHING FOR '?' OR 'Eye' IN ALL TABLES ===\n";
foreach ($all_tables as $table) {
    $cols_res = mysqli_query($dbc, "SHOW COLUMNS FROM `$table`");
    $text_cols = [];
    while ($col = mysqli_fetch_assoc($cols_res)) {
        if (stripos($col['Type'], 'varchar') !== false || stripos($col['Type'], 'text') !== false) {
            $text_cols[] = $col['Field'];
        }
    }
    if (empty($text_cols)) continue;

    $where_parts = [];
    foreach ($text_cols as $col) {
        $where_parts[] = "`$col` LIKE '%Eye%' OR `$col` LIKE '%?????%'";
    }
    $sql = "SELECT * FROM `$table` WHERE " . implode(' OR ', $where_parts) . " LIMIT 10";
    $res = mysqli_query($dbc, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        echo "\nFOUND IN TABLE: $table\n";
        while ($row = mysqli_fetch_assoc($res)) {
            foreach ($row as $k => $v) {
                echo "  [$k]: $v\n";
            }
            echo "  ---\n";
        }
    }
}


echo "\n=== PRINTS TABLE ===\n";
$r = mysqli_query($dbc, "SELECT * FROM prints LIMIT 20");
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) {
        foreach ($row as $k => $v) echo "  [$k]: $v\n";
        echo "  ---\n";
    }
} else {
    echo "  Error or empty\n";
}

mysqli_close($dbc);
