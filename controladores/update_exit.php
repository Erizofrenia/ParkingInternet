<?php
require_once "conexion.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

$barcode = $_POST['barcode'] ?? '';
$total_raw = $_POST['total'] ?? 0;
$received_raw = $_POST['received'] ?? 0;
$estacionamiento_id = $_SESSION['estacionamiento_id'] ?? 0;

if (!$barcode || !$estacionamiento_id) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

// --- Normalizar totales: eliminar comas, símbolos, espacios ---
$cleanNumber = function ($val) {
    if (is_numeric($val)) return (float)$val;
    $val = preg_replace('/[^\d\.\-]/', '', str_replace(',', '', $val));
    return (float)$val;
};

$total = $cleanNumber($total_raw);
$received = $cleanNumber($received_raw);

if ($received < $total) {
    echo json_encode(["success" => false, "message" => "El monto recibido es menor al total"]);
    exit;
}

try {
    // Buscar registro
    $stmt = $conn->prepare("
        SELECT id, cobrado 
        FROM registros 
        WHERE barcode = :barcode AND estacionamiento_id = :est 
        LIMIT 1
    ");
    $stmt->execute([
        ':barcode' => $barcode,
        ':est' => $estacionamiento_id
    ]);
    $reg = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reg) {
        echo json_encode(["success" => false, "message" => "No se encontró el registro"]);
        exit;
    }

    if ($reg['cobrado']) {
        echo json_encode(["success" => false, "message" => "El registro ya fue cobrado"]);
        exit;
    }

    // Calcular cambio
    $cambio = $received - $total;

    // Actualizar registro
    $update = $conn->prepare("
        UPDATE registros 
        SET salida = NOW(), total = :total, cobrado = 1 
        WHERE id = :id
    ");
    $update->execute([
        ':total' => $total,
        ':id' => $reg['id']
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Salida registrada correctamente",
        "data" => [
            "barcode" => $barcode,
            "total" => number_format($total, 2, '.', ''),     // salida limpia con punto decimal
            "received" => number_format($received, 2, '.', ''),
            "cambio" => number_format($cambio, 2, '.', '')
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
