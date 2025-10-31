<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$corte_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($corte_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de corte inválido']);
    exit;
}

try {
    // === Datos del corte ===
    $stmt = $conn->prepare("
        SELECT c.id, c.fecha, u.perfil_img, e.ticket_ancho, e.nombre AS estacionamiento_nombre, e.id AS estacionamiento_id
        FROM cortes_caja c
        INNER JOIN usuarios u ON u.id = c.usuario_id
        INNER JOIN estacionamientos e ON e.id = u.estacionamiento_id
        WHERE c.id = :corte_id AND c.usuario_id = :usuario_id
    ");
    $stmt->execute([':corte_id' => $corte_id, ':usuario_id' => $usuario_id]);
    $corte = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$corte) {
        echo json_encode(['success' => false, 'message' => 'Corte no encontrado o no autorizado']);
        exit;
    }

    $fecha_corte = date('Y-m-d', strtotime($corte['fecha']));
    $estacionamiento_id = $corte['estacionamiento_id'];

    // === Tarifas ===
    $tarifas = [];
    $stmt = $conn->query("SELECT tipo_vehiculo, precio_minimo, precio_hora FROM tarifas");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
        $tarifas[$t['tipo_vehiculo']] = [
            'minimo' => (float)$t['precio_minimo'],
            'hora'   => (float)$t['precio_hora']
        ];
    }

    // === Registros del día filtrando por usuario/estacionamiento ===
    $stmt = $conn->prepare("
        SELECT tipo_vehiculo, entrada, salida, barcode
        FROM registros
        WHERE DATE(salida) = :fecha 
          AND cobrado = 1
          AND estacionamiento_id = :estacionamiento_id
    ");
    $stmt->execute([':fecha' => $fecha_corte, ':estacionamiento_id' => $estacionamiento_id]);
    $registros_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $registros = [];
    $horas_completas_total = 0;
    $horas_minimas_total = 0;
    $total_dia_calc = 0;

    foreach ($registros_raw as $r) {
        if (empty($r['entrada']) || empty($r['salida'])) continue;

        $entrada = new DateTime($r['entrada']);
        $salida  = new DateTime($r['salida']);
        $diff = $entrada->diff($salida);
        $horas = $diff->days * 24 + $diff->h + ($diff->i / 60);

        $tipo = $r['tipo_vehiculo'];
        $tarifa = $tarifas[$tipo] ?? ['minimo' => 0, 'hora' => 0];

        // === Calcular horas y total ===
        if ($horas <= 1) {
            $total = $tarifa['minimo'];
            $horas_texto = "1 hr (mínimo)";
            $horas_minimas_total++;
        } else {
            $horas_completas = ceil($horas) - 1;
            $total = $tarifa['minimo'] + ($horas_completas * $tarifa['hora']);
            $horas_texto = "{$horas_completas} hr + mínimo";
            $horas_completas_total += $horas_completas;
            $horas_minimas_total++; // el mínimo cuenta en este cobro
        }

        $total_dia_calc += $total;

        $registros[] = [
            'tipo_vehiculo' => $tipo,
            'barcode' => $r['barcode'],
            'horas_cobradas' => $horas_texto,
            'total' => round($total, 2)
        ];
    }

    echo json_encode([
        'success' => true,
        'corte' => [
            'id' => $corte['id'],
            'fecha' => date('d/m/Y H:i', strtotime($corte['fecha'])),
            'estacionamiento' => $corte['estacionamiento_nombre'],
            'perfil_img' => $corte['perfil_img'],
            'ticket_ancho' => (int)($corte['ticket_ancho'] ?: 300),
            'total_dia' => round($total_dia_calc, 2),
            'total_registros' => count($registros),
            'horas_completas' => $horas_completas_total,
            'horas_minimas' => $horas_minimas_total,
            'registros' => $registros
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
