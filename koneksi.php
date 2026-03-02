<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_baju_portfolio";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Start session untuk login
session_start();
?>