<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
	echo json_encode(['error' => true, 'message' => 'Sesión no válida']);
	exit;
}

$usuario_id = $_SESSION['usuario_id'];
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
	// Total de cortes del usuario
	$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM cortes_caja WHERE usuario_id = :usuario_id");
	$stmt->execute([':usuario_id' => $usuario_id]);
	$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
	$total_pages = ceil($total / $limit);

	// Cortes del usuario
	$stmt = $conn->prepare("
		SELECT 
			id,
			DATE_FORMAT(fecha, '%d/%m/%Y %H:%i') AS fecha,
			total_dia,
			total_registros
		FROM cortes_caja
		WHERE usuario_id = :usuario_id
		ORDER BY fecha DESC
		LIMIT :limit OFFSET :offset
	");

	$stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
	$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
	$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
	$stmt->execute();

	$cortes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode([
		'cortes' => $cortes,
		'page' => $page,
		'total_pages' => $total_pages
	]);
} catch (PDOException $e) {
	echo json_encode(['error' => true, 'message' => 'Error al obtener cortes: ' . $e->getMessage()]);
}
