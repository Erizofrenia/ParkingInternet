<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$estacionamiento_id = $_SESSION['estacionamiento_id'] ?? 0;
if (!$estacionamiento_id) {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit;
}

// Parámetros
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');

// Query base
$where = "WHERE estacionamiento_id = :estacionamiento_id";
$params = [":estacionamiento_id" => $estacionamiento_id];

// Filtro de búsqueda
if ($search !== '') {
    $where .= " AND (placa LIKE :search OR modelo_color LIKE :search OR asignacion LIKE :search)";
    $params[":search"] = "%$search%";
}

// Total de registros
$stmt = $conn->prepare("SELECT COUNT(*) FROM registros $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Registros paginados
$sql = "SELECT 
            id, 
            placa, 
            modelo_color, 
            asignacion, 
            tipo_vehiculo AS tipo, 
            entrada AS hora_entrada, 
            barcode,
            CASE 
                WHEN salida IS NULL THEN 'Activo' 
                ELSE 'Finalizado' 
            END AS estado
        FROM registros 
        $where 
        ORDER BY entrada DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);

// Bind de parámetros
foreach ($params as $key => $val) {
    if ($key === ':estacionamiento_id') {
        $stmt->bindValue($key, $val, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Respuesta JSON
echo json_encode([
    'success' => true,
    'data' => $registros,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
