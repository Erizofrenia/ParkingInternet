<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['estacionamiento_id'])) {
    echo json_encode(['success' => false, 'message' => 'No se encontró sesión de estacionamiento']);
    exit;
}

$estacionamiento_id = $_SESSION['estacionamiento_id'];

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Contar total de tarifas del estacionamiento
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tarifas WHERE estacionamiento_id = :eid");
    $stmt->bindParam(':eid', $estacionamiento_id, PDO::PARAM_INT);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Obtener tarifas del estacionamiento
    $stmt = $conn->prepare("
        SELECT * 
        FROM tarifas 
        WHERE estacionamiento_id = :eid
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':eid', $estacionamiento_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'tarifas' => $tarifas,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
