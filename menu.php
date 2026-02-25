<?php
session_start();
include "includes/db.php";

if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$categories = $conn->query("SELECT * FROM categories");
$selected = isset($_GET['cat']) ? $_GET['cat'] : 1;

$products = $conn->query("
SELECT p.*, i.filename 
FROM products p
JOIN images i ON p.image_id = i.image_id
WHERE p.category_id = $selected
AND p.available = 1
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="menu-screen">

<div class="sidebar">
    <h3>Categories</h3>
    <?php while($cat = $categories->fetch_assoc()): ?>
        <a href="?cat=<?=$cat['category_id']?>"
           class="cat-btn <?=$selected==$cat['category_id']?'active':''?>">
           <?=$cat['name']?>
        </a>
    <?php endwhile; ?>
</div>

<div class="products">
    <?php while($p = $products->fetch_assoc()): ?>
        <div class="card">
            <img src="<?=$p['filename']?>">
            <div class="card-body">
                <h3><?=$p['name']?></h3>
                <p><?=$p['kcal']?> kcal</p>
                <div class="card-footer">
                    <span>€<?=number_format($p['price'],2)?></span>
                    <button onclick="addToCart(<?=$p['product_id']?>)">+</button>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div class="bottom-bar">
    <div>
        <?php
        $total = 0;
        foreach($_SESSION['cart'] as $id){
            $r = $conn->query("SELECT price FROM products WHERE product_id=$id");
            $row = $r->fetch_assoc();
            $total += $row['price'];
        }
        ?>
        <strong><?=count($_SESSION['cart'])?> items</strong>
        €<?=number_format($total,2)?>
    </div>

    <a href="review.php" class="review-btn">Review Order</a>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>