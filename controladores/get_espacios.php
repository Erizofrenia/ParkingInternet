<?php
require_once "conexion.php"; // aquí ya tienes $conn como instancia PDO

header('Content-Type: application/json; charset=utf-8');

try {
    // Total de espacios del estacionamiento
    $stmt = $conn->query("SELECT espacios FROM estacionamientos LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $row ? (int)$row['espacios'] : 0;

    // Espacios ocupados (vehículos actualmente sin salida)
    $ocupados = [];
    $stmt = $conn->query("SELECT asignacion FROM registros WHERE salida IS NULL");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ocupados[] = $r['asignacion'];
    }

    echo json_encode([
        "success" => true,
        "espacios" => $total,
        "ocupados" => $ocupados
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
