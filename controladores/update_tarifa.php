<?php
require 'conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id = intval($data['id'] ?? 0);
$tipo = trim($data['tipo_vehiculo'] ?? '');
$precio_hora = floatval($data['precio_hora'] ?? 0);
$precio_minimo = floatval($data['precio_minimo'] ?? 0);

if (!$id || !$tipo || $precio_hora <= 0 || $precio_minimo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

try {
    // Obtener estacionamiento_id de la tarifa a actualizar
    $stmt = $conn->prepare("SELECT estacionamiento_id FROM tarifas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $estacionamiento = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$estacionamiento) {
        echo json_encode(['success' => false, 'message' => 'Tarifa no encontrada']);
        exit;
    }
    $estacionamiento_id = $estacionamiento['estacionamiento_id'];

    // Verificar si ya existe otra tarifa con el mismo tipo
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tarifas WHERE estacionamiento_id = :estacionamiento_id AND tipo_vehiculo = :tipo AND id != :id");
    $stmt->execute([
        ':estacionamiento_id' => $estacionamiento_id,
        ':tipo' => $tipo,
        ':id' => $id
    ]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    if ($exists > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otra tarifa para este tipo de vehículo']);
        exit;
    }

    // Actualizar tarifa
    $stmt = $conn->prepare("UPDATE tarifas SET tipo_vehiculo = :tipo, precio_hora = :precio_hora, precio_minimo = :precio_minimo WHERE id = :id");
    $stmt->execute([
        ':tipo' => $tipo,
        ':precio_hora' => $precio_hora,
        ':precio_minimo' => $precio_minimo,
        ':id' => $id
    ]);

    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
