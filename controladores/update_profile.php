<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['estacionamiento_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

$userId = $_SESSION['usuario_id'];
$parkingId = $_SESSION['estacionamiento_id'];

// Datos del usuario
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$job = $_POST['job'] ?? '';
$newPhotoPath = null;

// Datos del estacionamiento
$parkingName = $_POST['parkingName'] ?? '';
$parkingMessage = $_POST['parkingMessage'] ?? '';
$parkingAddress = $_POST['parkingAddress'] ?? '';
$parkingSpaces = $_POST['parkingSpaces'] ?? 10;
$ticketSize = $_POST['ticketSize'] ?? 80;

try {
    // Procesar imagen de perfil si se subió
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/../media/perfil/";
        $relativeDir = "media/perfil/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = "profile_" . $userId . "_" . time() . "." . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;
        $dbPath = $relativeDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $newPhotoPath = $dbPath;
        }
    }

    // Actualizar tabla usuarios
    $queryUser = "UPDATE usuarios 
                  SET nombre = :nombre, email = :email, telefono = :telefono, puesto = :puesto";
    if ($newPhotoPath) {
        $queryUser .= ", perfil_img = :perfil_img";
    }
    $queryUser .= " WHERE id = :id";

    $stmtUser = $conn->prepare($queryUser);
    $stmtUser->bindParam(':nombre', $name);
    $stmtUser->bindParam(':email', $email);
    $stmtUser->bindParam(':telefono', $phone);
    $stmtUser->bindParam(':puesto', $job);
    if ($newPhotoPath) {
        $stmtUser->bindParam(':perfil_img', $newPhotoPath);
    }
    $stmtUser->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmtUser->execute();

    // Actualizar tabla estacionamientos
    $queryParking = "UPDATE estacionamientos 
                     SET nombre = :pname, direccion = :paddress, mensaje = :pmensaje, espacios = :pspaces, ticket_ancho = :pticket
                     WHERE id = :pid";

    $stmtParking = $conn->prepare($queryParking);
    $stmtParking->bindParam(':pname', $parkingName);
    $stmtParking->bindParam(':pmensaje', $parkingMessage);
    $stmtParking->bindParam(':paddress', $parkingAddress);
    $stmtParking->bindParam(':pspaces', $parkingSpaces, PDO::PARAM_INT);
    $stmtParking->bindParam(':pticket', $ticketSize, PDO::PARAM_INT);
    $stmtParking->bindParam(':pid', $parkingId, PDO::PARAM_INT);
    $stmtParking->execute();

    echo json_encode([
        'success' => true,
        'newPhoto' => $newPhotoPath
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
