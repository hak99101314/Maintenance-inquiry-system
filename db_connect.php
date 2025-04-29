<?php
$servername = "localhost";
$username = "root";
$password = 'karry,roy,jackson';
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}
?>
