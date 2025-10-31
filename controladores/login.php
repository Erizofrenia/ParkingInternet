<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['rememberMe']) && $_POST['rememberMe'] === 'true';

    if ($username === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Por favor completa todos los campos.']);
        exit;
    }

    try {
        // Ahora también seleccionamos estacionamiento_id
        $stmt = $conn->prepare("SELECT id, usuario, password, estacionamiento_id FROM usuarios WHERE usuario = :usuario LIMIT 1");
        $stmt->bindParam(':usuario', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $password) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['estacionamiento_id'] = $user['estacionamiento_id']; // <-- guardamos en sesión

            // Recordarme: cookie que dura 30 días
            if ($remember) {
                setcookie("usuario_id", $user['id'], time() + (86400 * 30), "/");
                setcookie("usuario", $user['usuario'], time() + (86400 * 30), "/");
                setcookie("estacionamiento_id", $user['estacionamiento_id'], time() + (86400 * 30), "/");
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en el servidor.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
