<?php
// config.php

$host = 'localhost'; // Database host
$db = 'srceduph_ojt_db'; // Database name
$user = 'srceduph_ojt_db'; // Database username
$pass = 'Mdq1QeMg8~7q'; // Database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>