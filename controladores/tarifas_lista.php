<?php
require_once "conexion.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

$estacionamiento_id = $_SESSION['estacionamiento_id'] ?? 0;
if (!$estacionamiento_id) {
    echo json_encode(["success" => false, "message" => "Sesión inválida"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, tipo_vehiculo, precio_hora 
                            FROM tarifas 
                            WHERE estacionamiento_id = :id 
                            ORDER BY tipo_vehiculo ASC");
    $stmt->execute([":id" => $estacionamiento_id]);
    $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $tarifas]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
