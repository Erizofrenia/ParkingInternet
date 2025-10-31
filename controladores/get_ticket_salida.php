<?php
require_once "conexion.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['barcode'])) {
	echo json_encode(['success' => false, 'message' => 'Falta el código de barras']);
	exit;
}

$barcode = $_GET['barcode'];

try {
	$sql = "
		SELECT 
			r.id,
			r.placa,
			r.modelo_color,
			r.tipo_vehiculo,
			r.asignacion,
			r.cliente_nombre,
			r.entrada,
			r.salida,
			r.total,
			r.barcode,
			t.precio_hora,
			t.precio_minimo,
			e.nombre AS estacionamiento_nombre,
			e.direccion AS estacionamiento_direccion,
			e.ticket_ancho,
			u.nombre AS usuario_nombre,
			u.perfil_img,
			ROUND(TIMESTAMPDIFF(MINUTE, r.entrada, r.salida) / 60, 2) AS horas_cobradas
		FROM registros r
		INNER JOIN estacionamientos e ON r.estacionamiento_id = e.id
		LEFT JOIN tarifas t ON t.estacionamiento_id = e.id AND t.tipo_vehiculo = r.tipo_vehiculo
		LEFT JOIN usuarios u ON e.id = u.estacionamiento_id
		WHERE r.barcode = :barcode
		LIMIT 1
	";

	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
	$stmt->execute();

	$data = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$data) {
		echo json_encode(['success' => false, 'message' => 'No se encontró el registro']);
		exit;
	}

	echo json_encode(['success' => true, 'data' => $data]);

} catch (PDOException $e) {
	echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
