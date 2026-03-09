<?php
header("Content-Type: application/json");
include "../includes/db.php";

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

if ($order_id <= 0) {
    echo json_encode(["error" => "Invalid order ID"]);
    exit();
}

$order = $conn->query("SELECT * FROM orders WHERE order_id = $order_id")->fetch_assoc();

if (!$order) {
    echo json_encode(["error" => "Order not found"]);
    exit();
}

$items = [];
$result = $conn->query("
    SELECT p.name, op.price 
    FROM order_product op
    JOIN products p ON op.product_id = p.product_id
    WHERE op.order_id = $order_id
");

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    "order_id" => (int) $order['order_id'],
    "pickup_number" => (int) $order['pickup_number'],
    "total" => (float) $order['price_total'],
    "items" => $items
]);
