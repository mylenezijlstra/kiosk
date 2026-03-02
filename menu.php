<?php
session_start();
include "includes/lang.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= t('menu_title') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="menu-screen">

    <?php include "includes/language_switch.php"; ?>

    <div class="sidebar">

        <div class="logo">
            <img src="assets/img/logo (2).png" alt="Logo">
        </div>

        <h3><?= t('categories') ?></h3>
        <div id="category-list"></div>

    </div>

    <div class="products" id="product-list"></div>

    <div class="bottom-bar">
        <div id="cart-info">
            <strong>0 <?= t('items') ?></strong> €0.00
        </div>

        <a href="<?= lang_url('review.php') ?>" class="review-order">
            <?= t('review_order') ?>
        </a>
    </div>

    <!-- Vertalingen voor JavaScript -->
    <script>
        const translations = {
            customize_ingredients: "<?= t('customize_ingredients') ?>",
            add_to_cart: "<?= t('add_to_cart') ?>",
            close: "<?= t('close') ?>",
            items: "<?= t('items') ?>",
            cross_sell_title: "<?= t('cross_sell_title') ?>"
        };
    </script>

    <script src="assets/js/app.js"></script>

    <div id="popup-overlay" class="popup-overlay" style="display:none;">
        <div id="popup" class="popup"></div>
    </div>

</body>

</html>