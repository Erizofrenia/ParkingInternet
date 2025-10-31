<?php
require 'conexion.php'; // ajusta según tu ruta

if (!isset($_GET['barcode'])) {
    echo json_encode(['success' => false, 'message' => 'Falta código de barras']);
    exit;
}

$barcode = $_GET['barcode'];

// Traer datos del registro
$sql = "SELECT r.*, t.precio_hora, t.precio_minimo 
        FROM registros r
        JOIN tarifas t ON r.estacionamiento_id = t.estacionamiento_id 
                      AND r.tipo_vehiculo = t.tipo_vehiculo
        WHERE r.barcode = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$barcode]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
    exit;
}

// Calcular tiempo y total
$entrada = new DateTime($registro['entrada']);
$ahora = new DateTime();
$intervalo = $entrada->diff($ahora);
$horas = $intervalo->days * 24 + $intervalo->h + ($intervalo->i > 0 ? 1 : 0);

$total = max($registro['precio_minimo'], $horas * $registro['precio_hora']);
$tiempoTexto = $intervalo->h . "h " . $intervalo->i . "min";

echo json_encode([
    'success' => true,
    'data' => [
        'placa' => $registro['placa'],
        'modelo' => $registro['modelo_color'],
        'entrada' => $registro['entrada'],
        'tiempo' => $tiempoTexto,
        'total' => number_format($total, 2),
        'barcode' => $registro['barcode']
    ]
]);
