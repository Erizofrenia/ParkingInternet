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
    // Obtener registro con tarifa, estacionamiento y usuario
    $stmt = $conn->prepare("
		SELECT 
			r.placa, 
			r.modelo_color, 
			r.tipo_vehiculo, 
			r.cliente_nombre, 
			r.asignacion, 
			r.entrada, 
			r.barcode,
			t.precio_hora, 
			t.precio_minimo,
			e.nombre AS estacionamiento_nombre,
			e.ticket_ancho,
			e.mensaje AS estacionamiento_mensaje,
			u.perfil_img
		FROM registros r
		LEFT JOIN tarifas t 
			ON r.tipo_vehiculo = t.tipo_vehiculo 
			AND r.estacionamiento_id = t.estacionamiento_id
		LEFT JOIN estacionamientos e 
			ON r.estacionamiento_id = e.id
		LEFT JOIN usuarios u 
			ON u.estacionamiento_id = r.estacionamiento_id
		WHERE r.barcode = :barcode 
		  AND r.estacionamiento_id = :est
		LIMIT 1
	");

    $stmt->execute([
        ':barcode' => $barcode,
        ':est' => $estacionamiento_id
    ]);

    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        echo json_encode(["success" => false, "message" => "Registro no encontrado"]);
        exit;
    }

    // Imagen por defecto si no tiene perfil
    if (!$registro['perfil_img']) {
        $registro['perfil_img'] = 'media/parkinginternet.png';
    }

    echo json_encode([
        "success" => true,
        "data" => $registro
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
