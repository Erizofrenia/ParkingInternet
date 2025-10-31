<?php
$host = 'localhost';
$user = 'hackersi_parking';
$pass = 'YHNcAyDG5uAEB6wMsx6G';
$db = 'hackersi_parking';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>