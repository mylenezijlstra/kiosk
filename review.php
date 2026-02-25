<?php
session_start();
include "includes/db.php";

if(empty($_SESSION['cart'])){
    header("Location: menu.php");
    exit();
}

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Order</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="review-screen">

<h1>Review Your Order</h1>

<?php foreach($_SESSION['cart'] as $id):
$r = $conn->query("SELECT * FROM products WHERE product_id=$id");
$p = $r->fetch_assoc();
$total += $p['price'];
?>

<div class="review-item">
    <span><?=$p['name']?></span>
    <span>€<?=number_format($p['price'],2)?></span>
</div>

<?php endforeach; ?>

<h2>Total €<?=number_format($total,2)?></h2>

<form action="confirm.php" method="POST">
    <button class="confirm-btn">Confirm Order</button>
</form>

<a href="menu.php" class="back-btn">Add More Items</a>

</body>
</html>