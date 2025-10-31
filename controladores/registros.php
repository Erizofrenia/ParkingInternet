<?php
require 'conexion.php';
session_start();
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

if($action == 'registerEntry'){
    $placa = $_POST['placa'];
    $modelo = $_POST['modelo_color'];
    $tipo = $_POST['tipo_vehiculo'];
    $cliente = $_POST['cliente_nombre'] ?? '';

    $stmt = $conn->prepare("SELECT id FROM estacionamientos WHERE user_id = ?");
    $stmt->execute([$userId]);
    $estacionamiento = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$estacionamiento) exit(json_encode(['error'=>'No tiene estacionamiento']));

    $barcode = uniqid('TICKET_');

    $stmt = $conn->prepare("INSERT INTO registros 
        (estacionamiento_id, placa, modelo_color, tipo_vehiculo, cliente_nombre, entrada, barcode) 
        VALUES (?,?,?,?,?,NOW(),?)");
    $stmt->execute([$estacionamiento['id'], $placa, $modelo, $tipo, $cliente, $barcode]);
    echo json_encode(['success'=>true, 'barcode'=>$barcode]);
}

if($action == 'processExit'){
    $barcode = $_POST['barcode'];
    $stmt = $conn->prepare("SELECT r.*, t.precio_hora, t.precio_minimo 
                            FROM registros r 
                            JOIN estacionamientos e ON e.id = r.estacionamiento_id
                            LEFT JOIN tarifas t ON t.estacionamiento_id = e.id AND t.tipo_vehiculo = r.tipo_vehiculo
                            WHERE r.barcode=? AND e.user_id=?");
    $stmt->execute([$barcode, $userId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$record) exit(json_encode(['error'=>'Vehículo no encontrado']));
    
    // Calcular tiempo y total
    $entrada = new DateTime($record['entrada']);
    $ahora = new DateTime();
    $diff = $ahora->diff($entrada);
    $hours = $diff->h + ($diff->days*24) + ($diff->i/60);
    $total = max($hours*$record['precio_hora'], $record['precio_minimo']);

    echo json_encode([
        'success'=>true,
        'placa'=>$record['placa'],
        'modelo'=>$record['modelo_color'],
        'entrada'=>$record['entrada'],
        'total'=>$total
    ]);
}

if($action == 'completeExit'){
    $barcode = $_POST['barcode'];
    $received = $_POST['received'];
    $stmt = $conn->prepare("UPDATE registros r 
                            JOIN estacionamientos e ON e.id = r.estacionamiento_id
                            SET r.salida=NOW(), r.total=?, r.cobrado=1 
                            WHERE r.barcode=? AND e.user_id=?");
    $stmt->execute([$received, $barcode, $userId]);
    echo json_encode(['success'=>true]);
}
?>
