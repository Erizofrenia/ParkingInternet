<?php
include 'conexion.php'; // debe devolver un objeto $conn de tipo PDO

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Total de registros
    $stmt = $conn->query("SELECT COUNT(*) AS total FROM cortes_caja");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total / $limit);

    // Consulta principal
    $stmt = $conn->prepare("
        SELECT 
            id, 
            DATE_FORMAT(fecha, '%d/%m/%Y %H:%i') AS fecha, 
            total_dia, 
            total_registros 
        FROM cortes_caja 
        ORDER BY fecha DESC 
        LIMIT :limit OFFSET :offset
    ");
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
    echo json_encode([
        'error' => true,
        'message' => 'Error al obtener los cortes: ' . $e->getMessage()
    ]);
}
