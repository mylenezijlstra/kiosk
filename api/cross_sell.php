<?php
header("Content-Type: application/json");
include "../includes/db.php";

$id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($id === 0) {
    echo json_encode([]);
    exit;
}

// Get the cross_sell_ids for this product
$result = $conn->query("SELECT cross_sell_ids FROM products WHERE product_id = $id");
$row = $result->fetch_assoc();

if (!$row || empty($row['cross_sell_ids'])) {
    echo json_encode([]);
    exit;
}

// Parse comma-separated IDs
$ids = array_map('intval', explode(',', $row['cross_sell_ids']));
$idList = implode(',', $ids);

// Fetch all cross-sell products
$sql = "
SELECT p.product_id, p.name, p.price, p.kcal, i.filename
FROM products p
LEFT JOIN images i ON p.image_id = i.image_id
WHERE p.product_id IN ($idList)
AND p.available = 1
";

$result = $conn->query($sql);
$products = [];

while ($p = $result->fetch_assoc()) {
    $products[] = $p;
}

echo json_encode($products);
