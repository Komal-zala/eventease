<?php
$servername = "localhost";
$username   = "root";  // apna MySQL username
$password   = "";      // apna MySQL password
$dbname     = "eventease"; // apna database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
