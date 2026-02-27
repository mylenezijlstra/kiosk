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

<div class="review-container">

    <h1>Review Your Order</h1>

    <div class="review-list">

    <?php foreach($_SESSION['cart'] as $id):

        $id = (int)$id;

        $r = $conn->query("
            SELECT p.*, i.filename 
            FROM products p
            LEFT JOIN images i ON p.image_id = i.image_id
            WHERE p.product_id = $id
        ");

        $p = $r->fetch_assoc();
        if(!$p) continue;

        $total += $p['price'];
    ?>

        <div class="review-item">
            <img src="<?= htmlspecialchars($p['filename']) ?>" 
                 alt="<?= htmlspecialchars($p['name']) ?>">

            <div class="review-info">
                <h3><?= htmlspecialchars($p['name']) ?></h3>
                <p>€<?= number_format($p['price'],2) ?></p>
            </div>
        </div>

    <?php endforeach; ?>

    </div>

    <div class="review-total">
        <h2>Total</h2>
        <h2>€<?= number_format($total,2) ?></h2>
    </div>

    <div class="review-actions">
        <form action="confirm.php" method="POST">
            <button class="confirm-btn">Confirm Order</button>
        </form>

        <a href="menu.php" class="back-btn">Add More Items</a>
    </div>

</div>

</body>
</html>