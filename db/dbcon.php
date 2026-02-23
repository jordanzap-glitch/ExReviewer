<?php
$servername = "localhost";
$username = "srceduph_ojt_db";
$password = "Mdq1QeMg8~7q";
$database = "srceduph_ojt_db";
$port = 3306;
// Create connection
$conn = mysqli_connect($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

