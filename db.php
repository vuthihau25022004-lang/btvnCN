<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "quanlysanpham";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Ket noi database that bai: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
