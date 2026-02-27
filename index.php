<?php
session_start();

/* Maak array met 1 t/m 25 */
$images = range(1, 25);

/* Shuffle voor random volgorde */
shuffle($images);

/* Pak eerste 6 voor slideshow */
$images = array_slice($images, 0, 6);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Happy Herbivore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="start-screen">

<!-- SLIDESHOW -->
<div class="slideshow">
    <?php foreach ($images as $index => $num): ?>
        <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
            <img src="assets/img/eten<?php echo $num; ?>.png" alt="Eten">
        </div>
    <?php endforeach; ?>
</div>

<!-- CONTENT -->
<div class="start-container">
    <img src="./assets/img/logo (2).png" class="logo" alt="Happy Herbivore Logo">

    <h1>Welkom bij Happy Herbivore</h1>
    <h2>Healthy in a Hurry</h2>

    <a href="keuze.php" class="start-button">
        Raak aan om te bestellen
    </a>

    <p class="tagline">100% Plantaardig • Vers • Heerlijk</p>
</div>

<!-- SLIDESHOW SCRIPT -->
<script>
let slides = document.querySelectorAll(".slide");
let current = 0;

if (slides.length > 1) {
    setInterval(() => {
        slides[current].classList.remove("active");
        current = (current + 1) % slides.length;
        slides[current].classList.add("active");
    }, 4000);
}
</script>

</body>
</html>