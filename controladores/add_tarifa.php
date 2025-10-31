<?php
session_start(); // ¡Muy importante!
require 'conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$tipo = trim($data['tipo_vehiculo'] ?? '');
$precio_hora = floatval($data['precio_hora'] ?? 0);
$precio_minimo = floatval($data['precio_minimo'] ?? 0);
$estacionamiento_id = $_SESSION['estacionamiento_id'] ?? 0;

// Validación
if (!$tipo || $precio_hora <= 0 || $precio_minimo <= 0 || !$estacionamiento_id) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

try {
    // Verificar si ya existe el tipo de vehículo en este estacionamiento
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tarifas WHERE estacionamiento_id = :estacionamiento_id AND tipo_vehiculo = :tipo");
    $stmt->execute([
        ':estacionamiento_id' => $estacionamiento_id,
        ':tipo' => $tipo
    ]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($exists > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una tarifa para este tipo de vehículo']);
        exit;
    }

    // Insertar nueva tarifa
    $stmt = $conn->prepare("
        INSERT INTO tarifas (estacionamiento_id, tipo_vehiculo, precio_hora, precio_minimo) 
        VALUES (:estacionamiento_id, :tipo, :precio_hora, :precio_minimo)
    ");
    $stmt->execute([
        ':estacionamiento_id' => $estacionamiento_id,
        ':tipo' => $tipo,
        ':precio_hora' => $precio_hora,
        ':precio_minimo' => $precio_minimo
    ]);

    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
