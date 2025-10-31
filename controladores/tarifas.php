<?php
require 'conexion.php';
session_start();
$userId = $_SESSION['user_id']; // Suponiendo sesión iniciada

$action = $_POST['action'] ?? '';

if($action == 'list'){
    $stmt = $conn->prepare("SELECT t.id, t.tipo_vehiculo, t.precio_hora, t.precio_minimo 
                            FROM tarifas t
                            JOIN estacionamientos e ON e.id = t.estacionamiento_id
                            WHERE e.user_id = ?");
    $stmt->execute([$userId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if($action == 'add'){
    $tipo = $_POST['tipo_vehiculo'];
    $hora = $_POST['precio_hora'];
    $min = $_POST['precio_minimo'];

    // Obtener estacionamiento del usuario
    $stmt = $conn->prepare("SELECT id FROM estacionamientos WHERE user_id = ?");
    $stmt->execute([$userId]);
    $estacionamiento = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$estacionamiento) exit(json_encode(['error'=>'No tiene estacionamiento']));

    $stmt = $conn->prepare("INSERT INTO tarifas (estacionamiento_id, tipo_vehiculo, precio_hora, precio_minimo) VALUES (?,?,?,?)");
    $stmt->execute([$estacionamiento['id'], $tipo, $hora, $min]);
    echo json_encode(['success'=>true]);
}

if($action == 'update'){
    $id = $_POST['id'];
    $tipo = $_POST['tipo_vehiculo'];
    $hora = $_POST['precio_hora'];
    $min = $_POST['precio_minimo'];

    $stmt = $conn->prepare("UPDATE tarifas t
                            JOIN estacionamientos e ON e.id = t.estacionamiento_id
                            SET t.tipo_vehiculo=?, t.precio_hora=?, t.precio_minimo=?
                            WHERE t.id=? AND e.user_id=?");
    $stmt->execute([$tipo, $hora, $min, $id, $userId]);
    echo json_encode(['success'=>true]);
}

if($action == 'delete'){
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE t FROM tarifas t
                            JOIN estacionamientos e ON e.id = t.estacionamiento_id
                            WHERE t.id=? AND e.user_id=?");
    $stmt->execute([$id, $userId]);
    echo json_encode(['success'=>true]);
}
?>
