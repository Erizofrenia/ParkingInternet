<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status'=>'error','message'=>'Sesión no válida']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT password, estacionamiento_id, perfil_img FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || $password !== $usuario['password']) {
    echo json_encode(['status'=>'error','message'=>'Contraseña incorrecta']);
    exit;
}

$est_id = $usuario['estacionamiento_id'];
$stmt = $conn->prepare("SELECT nombre, ticket_ancho FROM estacionamientos WHERE id = ?");
$stmt->execute([$est_id]);
$estacionamiento = $stmt->fetch(PDO::FETCH_ASSOC);

$fecha_hoy = date('Y-m-d');
$stmt = $conn->prepare("SELECT id FROM cortes_caja WHERE DATE(fecha) = ? AND usuario_id = ?");
$stmt->execute([$fecha_hoy, $usuario_id]);
if ($stmt->fetch()) {
    echo json_encode(['status'=>'error','message'=>'Ya se realizó el corte de caja hoy.']);
    exit;
}

// === Traer tarifas para cálculo ===
$tarifas = [];
$stmt = $conn->query("SELECT tipo_vehiculo, precio_minimo, precio_hora FROM tarifas");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $tarifas[$t['tipo_vehiculo']] = [
        'minimo' => (float)$t['precio_minimo'],
        'hora'   => (float)$t['precio_hora']
    ];
}

// === Traer registros del día ===
$stmt = $conn->prepare("
    SELECT id, barcode, tipo_vehiculo, entrada, salida
    FROM registros
    WHERE DATE(salida) = ? AND cobrado = 1 AND estacionamiento_id = ?
");
$stmt->execute([$fecha_hoy, $est_id]);
$registros_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === Procesar registros con horas y total como get_ticket_corte.php ===
$registros = [];
$total_dia = 0;
$horas_minimas_total = 0;
$horas_completas_total = 0;

foreach ($registros_raw as $r) {
    if (empty($r['entrada']) || empty($r['salida'])) continue;

    $entrada = new DateTime($r['entrada']);
    $salida  = new DateTime($r['salida']);
    $diff = $entrada->diff($salida);
    $horas = $diff->days * 24 + $diff->h + ($diff->i / 60);

    $tipo = $r['tipo_vehiculo'];
    $tarifa = $tarifas[$tipo] ?? ['minimo' => 0, 'hora' => 0];

    if ($horas <= 1) {
        $total = $tarifa['minimo'];
        $horas_texto = "1 hr (mínimo)";
        $horas_minimas_total++;
    } else {
        $horas_completas = ceil($horas) - 1;
        $total = $tarifa['minimo'] + ($horas_completas * $tarifa['hora']);
        $horas_texto = "{$horas_completas} hr + mínimo";
        $horas_completas_total += $horas_completas;
        $horas_minimas_total++; // la primera hora mínima
    }

    $total_dia += $total;

    $registros[] = [
        'id' => (int)$r['id'],
		'barcode' => $r['barcode'],
        'tipo_vehiculo' => $tipo,
        'horas_cobradas' => $horas_texto,
        'total' => round($total, 2)
    ];
}

$total_registros = count($registros);

// === Insertar corte ===
$stmt = $conn->prepare("INSERT INTO cortes_caja (usuario_id, fecha, total_dia, total_registros) VALUES (?, NOW(), ?, ?)");
$stmt->execute([$usuario_id, $total_dia, $total_registros]);

// === Responder JSON ===
echo json_encode([
    'status' => 'success',
    'message' => 'Corte de caja realizado correctamente.',
    'corte' => [
        'estacionamiento' => $estacionamiento['nombre'] ?? '',
        'perfil_img' => $usuario['perfil_img'] ?? '',
        'ticket_ancho' => (int)($estacionamiento['ticket_ancho'] ?? 300),
        'total_dia' => round($total_dia, 2),
        'total_registros' => $total_registros,
        'horas_minimas' => $horas_minimas_total,
        'horas_completas' => $horas_completas_total,
        'registros' => $registros,
        'fecha' => date('d/m/Y H:i')
    ]
], JSON_NUMERIC_CHECK);
