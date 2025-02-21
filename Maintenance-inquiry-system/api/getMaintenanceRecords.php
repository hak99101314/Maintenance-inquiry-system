<?php
header('Content-Type: application/json');
$pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社', 'root', '');

$stmt = $pdo->query("SELECT part_name, quantity FROM purchase_records");
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($inventory);
?>
