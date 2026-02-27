<?php
session_start();
include "includes/db.php";

$total = 0;

foreach($_SESSION['cart'] as $id){
    $id = (int)$id;
    $r = $conn->query("SELECT price FROM products WHERE product_id=$id");
    $row = $r->fetch_assoc();
    $total += $row['price'];
}

$date = date("Y-m-d");
$count = $conn->query("SELECT COUNT(*) as c FROM orders WHERE DATE(datetime)='$date'");
$row = $count->fetch_assoc();
$pickup = ($row['c'] % 99) + 1;

$conn->query("INSERT INTO orders (order_status_id,pickup_number,price_total)
VALUES (2,$pickup,$total)");

$order_id = $conn->insert_id;

foreach($_SESSION['cart'] as $id){
    $id = (int)$id;
    $r = $conn->query("SELECT price FROM products WHERE product_id=$id");
    $row = $r->fetch_assoc();
    $conn->query("INSERT INTO order_product (order_id,product_id,price)
    VALUES ($order_id,$id,{$row['price']})");
}

$_SESSION['cart'] = [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bestelling Bevestigd</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Auto redirect na 5 seconden -->
    <meta http-equiv="refresh" content="5;url=index.php">
</head>
<body class="confirm-screen">

<div class="confirm-container">

    <h1>Bestelling Bevestigd!</h1>
    <h2>Jouw Bestelnummer</h2>

    <div class="pickup-number">
        #<?=str_pad($pickup,3,"0",STR_PAD_LEFT)?>
    </div>

    <p class="redirect-text">
        Je wordt automatisch teruggestuurd...
    </p>

    <a href="index.php" class="start-button">Nieuwe Bestelling</a>

</div>

</body>
</html>