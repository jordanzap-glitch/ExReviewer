<?php
$servername = "localhost";
$username = "eereview_admin_db";
$password = "QlBzr[dLZmzk(xt9";
$database = "eereview_db";
$port = 3306;
// Create connection
$conn = mysqli_connect($servername, $username, $password, $database, $port);
    
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>