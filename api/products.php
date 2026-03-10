<?php
header("Content-Type: application/json");
include "../includes/db.php";



$cat = isset($_GET['cat']) ? intval($_GET['cat']) : 1;
$sql = "
SELECT p.*, i.filename 
FROM products p
JOIN images i ON p.image_id = i.image_id
WHERE p.category_id = $cat
AND p.available = 1
";

$result = $conn->query($sql);
$rows = [];

while($r = $result->fetch_assoc()){
    $rows[] = $r;
    }
    
    echo json_encode($rows);
    
    