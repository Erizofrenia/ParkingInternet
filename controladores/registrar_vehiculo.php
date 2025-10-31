<?php
require_once "conexion.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

// Datos recibidos
$tipo_id    = $_POST['vehicleTypeId'] ?? '';
$placa      = $_POST['licensePlate'] ?? '';
$modelo     = $_POST['modelColor'] ?? '';
$cliente    = $_POST['customerName'] ?? '';
$espacio    = $_POST['selectedSpace'] ?? '';
$estacionamiento_id = $_SESSION['estacionamiento_id'] ?? 0;

// Validaciones
if (!$tipo_id || empty($espacio) || (empty($placa) && empty($modelo)) || !$estacionamiento_id) {
    echo json_encode(["success" => false, "message" => "Faltan campos obligatorios"]);
    exit;
}

try {
    // Obtener tipo_vehiculo desde tarifas
    $stmtTipo = $conn->prepare("SELECT tipo_vehiculo FROM tarifas WHERE id = :id AND estacionamiento_id = :est");
    $stmtTipo->execute([
        ':id' => $tipo_id,
        ':est' => $estacionamiento_id
    ]);
    $tipoVehiculo = $stmtTipo->fetchColumn();

    if (!$tipoVehiculo) {
        echo json_encode(["success" => false, "message" => "Tipo de vehículo no válido"]);
        exit;
    }

    // Generar código de barras numérico único (ejemplo: timestamp + 4 dígitos aleatorios)
    $barcode = time() . rand(10000, 99999);

    // Insertar registro con asignación y barcode
    $stmt = $conn->prepare("
        INSERT INTO registros 
            (estacionamiento_id, tipo_vehiculo, placa, modelo_color, cliente_nombre, asignacion, entrada, barcode) 
        VALUES 
            (:estacionamiento_id, :tipo_vehiculo, :placa, :modelo_color, :cliente_nombre, :asignacion, NOW(), :barcode)
    ");

    $stmt->execute([
        ':estacionamiento_id' => $estacionamiento_id,
        ':tipo_vehiculo' => $tipoVehiculo,
        ':placa' => $placa,
        ':modelo_color' => $modelo,
        ':cliente_nombre' => $cliente,
        ':asignacion' => $espacio,
        ':barcode' => $barcode
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Vehículo registrado correctamente",
        "barcode" => $barcode // opcional: devolver el código de barras al frontend
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
