<?php
require_once "conexion.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

$barcode = $_GET['barcode'] ?? '';
$estacionamiento_id = $_SESSION['estacionamiento_id'] ?? 0;

if (!$barcode || !$estacionamiento_id) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

try {
    // Buscar registro con su tarifa
    $stmt = $conn->prepare("
        SELECT 
            r.id, r.placa, r.modelo_color, r.entrada, r.barcode, r.tipo_vehiculo,
            TIMESTAMPDIFF(MINUTE, r.entrada, NOW()) AS minutos,
            t.precio_hora, t.precio_minimo
        FROM registros r
        LEFT JOIN tarifas t 
            ON r.tipo_vehiculo = t.tipo_vehiculo 
            AND r.estacionamiento_id = t.estacionamiento_id
        WHERE r.barcode = :barcode AND r.estacionamiento_id = :est
          AND r.cobrado = 0
        LIMIT 1
    ");

    $stmt->execute([
        ':barcode' => $barcode,
        ':est' => $estacionamiento_id
    ]);
    $reg = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reg) {
        echo json_encode(["success" => false, "message" => "No se encontró el ticket o ya fue cobrado"]);
        exit;
    }

    // Calcular total
    $minutos = intval($reg['minutos']);
    $horas = ceil($minutos / 60);
    $total = $reg['precio_minimo'];
	$detalle = "Tarifa mínima $" . number_format($reg['precio_minimo'], 2);
	

    if ($minutos > 60) {
        $total = $reg['precio_minimo'] + (($horas - 1) * $reg['precio_hora']);
		$detalle .= " + (" . ($horas - 1) . "h x $" . number_format($reg['precio_hora'], 2) . ")";
    }

    $reg['tiempo'] = floor($minutos / 60) . "h " . ($minutos % 60) . "min";
	$reg['total'] = number_format($total, 2);
	$reg['detalle_tarifa'] = $detalle;


    echo json_encode(["success" => true, "data" => $reg]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
