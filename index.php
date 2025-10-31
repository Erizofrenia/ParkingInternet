<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    if (isset($_COOKIE['usuario_id']) && isset($_COOKIE['usuario']) && isset($_COOKIE['estacionamiento_id'])) {
        $_SESSION['usuario_id'] = $_COOKIE['usuario_id'];
        $_SESSION['usuario'] = $_COOKIE['usuario'];
        $_SESSION['estacionamiento_id'] = $_COOKIE['estacionamiento_id'];
    } else {
        header("Location: inicio.php");
        exit;
    }
}

require 'controladores/conexion.php';
$userId = $_SESSION['usuario_id'];
$parkingId = $_SESSION['estacionamiento_id'] ?? 0;

// Datos del usuario
$stmt = $conn->prepare("SELECT nombre, email, telefono, puesto, perfil_img FROM usuarios WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$nombre   = $userData['nombre'] ?? '';
$email    = $userData['email'] ?? '';
$telefono = $userData['telefono'] ?? '';
$puesto   = $userData['puesto'] ?? '';
$foto     = $userData['perfil_img'] ?? '';

// Datos del estacionamiento
$stmt2 = $conn->prepare("SELECT nombre, direccion, mensaje, espacios, ticket_ancho FROM estacionamientos WHERE id = :id LIMIT 1");
$stmt2->bindParam(':id', $parkingId, PDO::PARAM_INT);
$stmt2->execute();
$parkingData = $stmt2->fetch(PDO::FETCH_ASSOC);

$estacionamiento_nombre    = $parkingData['nombre'] ?? '';
$estacionamiento_mensaje    = $parkingData['mensaje'] ?? '';
$estacionamiento_direccion = $parkingData['direccion'] ?? '';
$estacionamiento_espacios  = $parkingData['espacios'] ?? 10;
$ticket_ancho              = $parkingData['ticket_ancho'] ?? 80;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Estacionamiento</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<?php include 'common/head_common.php'; ?>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 pb-20">
    <!-- Header con toggle de tema -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 p-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-primary-600 dark:text-primary-400">Parking Internet</h1>
            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-600 dark:text-gray-400" id="userWelcome">Bienvenido</span>
                <button id="themeToggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:inline"></i>
                </button>
                <button onclick="logout()" class="p-2 rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-600 dark:text-red-400 transition-colors" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="max-w-7xl mx-auto p-4" id="mainContent">
        <!-- Pantalla de bienvenida -->
        <div id="welcome" class="text-center py-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mb-8">
                <div class="w-24 h-24 rounded-full mx-auto mb-6 overflow-hidden flex items-center justify-center">
					<img src="media/parkinginternet.png" alt="Parking Internet" class="w-full h-full object-cover">
				</div>
                <h2 class="text-3xl font-bold mb-4 text-primary-600 dark:text-primary-400">Bienvenido a Parking Internet</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                    Sistema integral de gestión de estacionamiento con control de entrada y salida, 
                    gestión de tarifas y generación automática de tickets.
                </p>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg info-card" onclick="showSection('profile')">
                        <div class="text-2xl mb-3 text-primary-500 info-icon"><i class="fas fa-user"></i></div>
                        <h3 class="font-semibold mb-2">Perfil</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Gestiona tu información personal</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg info-card" onclick="showSection('rates')">
                        <div class="text-2xl mb-3 text-primary-500 info-icon"><i class="fas fa-dollar-sign"></i></div>
                        <h3 class="font-semibold mb-2">Tarifas</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Configura precios por tipo de vehículo</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg info-card" onclick="showSection('records')">
                        <div class="text-2xl mb-3 text-primary-500 info-icon"><i class="fas fa-clipboard-list"></i></div>
                        <h3 class="font-semibold mb-2">Registros</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Consulta historial de vehículos</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg info-card" onclick="showSection('register')">
                        <div class="text-2xl mb-3 text-primary-500 info-icon"><i class="fas fa-car"></i></div>
                        <h3 class="font-semibold mb-2">Registrar</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Entrada y salida de vehículos</p>
                    </div>
                </div>
				
            </div>
			<!-- Nueva tarjeta para Corte de Caja -->
			<div class="mt-8 flex justify-center">
				<div id="btnCorteCaja" class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg info-card cursor-pointer hover:shadow-xl transition">
					<div class="text-2xl mb-3 text-yellow-500 info-icon"><i class="fas fa-cash-register"></i></div>
					<h3 class="font-semibold mb-2">Corte de Caja</h3>
					<p class="text-sm text-gray-600 dark:text-gray-400">Realiza y consulta cortes de caja</p>
				</div>
			</div>
			<!-- Botón Corte de Caja 
				<div class="flex justify-center mb-4">
					<button 
						class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-5 py-2 rounded-lg transition">
						Hacer corte de caja
					</button>
				</div>-->
				<!-- Contenedor del ticket de corte -->
				<div id="ticketCorteCaja" class="hidden mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6"></div>
        </div>

        <!-- Sección Perfil -->
		<div id="profile" class="hidden">
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
				<h2 class="text-2xl font-bold mb-6">Mi Perfil</h2>

				<div class="flex flex-col md:flex-row items-start gap-6">

					<!-- Imagen del Estacionamiento -->
					<div class="relative flex-shrink-0">
						<div class="w-32 h-32 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors overflow-hidden" onclick="document.getElementById('profileImageInput').click()">
							<img id="profileImage" src="<?= $foto ? $foto : '' ?>" alt="Perfil" class="w-full h-full object-cover rounded-full <?= $foto ? '' : 'hidden' ?>">
							<i id="profileIcon" class="fas fa-user text-4xl text-gray-600 dark:text-gray-300 <?= $foto ? 'hidden' : '' ?>"></i>
						</div>
						<div class="absolute -bottom-2 -right-2 bg-primary-500 rounded-full p-2 cursor-pointer hover:bg-primary-600 transition-colors" onclick="document.getElementById('profileImageInput').click()">
							<i class="fas fa-camera text-white text-sm"></i>
						</div>
						<input type="file" id="profileImageInput" accept="image/*" class="hidden">
					</div>

					<div class="flex-1">

						<!-- Configuración del Estacionamiento -->
						<div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-6">
							<h3 class="text-xl font-semibold mb-4">Configuración del Estacionamiento</h3>
							<div class="grid md:grid-cols-2 gap-4">
								<div>
									<label class="block text-sm font-medium mb-1">Nombre del estacionamiento</label>
									<input type="text" id="parkingName" value="<?= htmlspecialchars($estacionamiento_nombre) ?>" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
								</div>
								<div>
									<label class="block text-sm font-medium mb-1">Dirección</label>
									<input type="text" id="parkingAddress" value="<?= htmlspecialchars($estacionamiento_direccion ?? '') ?>" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
								</div>
								<div>
									<label class="block text-sm font-medium mb-1">Espacios Totales</label>
									<input type="number" id="parkingSpaces" value="<?= htmlspecialchars($estacionamiento_espacios) ?>" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
								</div>
								<div>
									<label class="block text-sm font-medium mb-1">Ancho Ticket (mm)</label>
									<select id="ticketSize" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
										<option value="58" <?= $ticket_ancho == 58 ? 'selected' : '' ?>>58 mm</option>
										<option value="80" <?= $ticket_ancho == 80 ? 'selected' : '' ?>>80 mm</option>
									</select>
								</div>
								<!-- NUEVO CAMPO MENSAJE -->
								<div class="md:col-span-2">
									<label class="block text-sm font-medium mb-1">Mensaje</label>
									<textarea id="parkingMessage" rows="3" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700"><?= htmlspecialchars($estacionamiento_mensaje ?? '') ?></textarea>
								</div>
							</div>
						</div>

						<!-- Datos del Usuario -->
						<div>
							<h3 class="text-xl font-semibold mb-4">Datos del Usuario</h3>
							<div class="grid md:grid-cols-2 gap-4">
								<div>
									<label class="block text-sm font-medium mb-1">Nombre</label>
									<input type="text" id="profileName" value="<?= htmlspecialchars($nombre) ?>" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
								</div>
								<div>
									<label class="block text-sm font-medium mb-1">Email</label>
									<input type="email" id="profileEmail" value="<?= htmlspecialchars($email) ?>" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
								</div>
								<div>
									<label class="block text-sm font-medium mb-1">Teléfono</label>
									<input type="tel" id="profilePhone" value="<?= htmlspecialchars($telefono) ?>" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
								</div>
								<div>
									<label class="block text-sm font-medium mb-1">Puesto</label>
									<input type="text" id="profileJob" value="<?= htmlspecialchars($puesto) ?>" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
								</div>
							</div>
						</div>

						<!-- Botón Guardar -->
						<div class="mt-6">
							<button id="saveProfileBtn" class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-2 rounded-lg transition-colors">
								Guardar Cambios
							</button>
						</div>

					</div>
				</div>
			</div>
		</div>



        <!-- Sección Tarifas -->
        <div id="rates" class="hidden">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Gestión de Tarifas</h2>
                    <button id="newRateBtn" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                        + Nueva Tarifa
                    </button>
                </div>
                
                <!-- Tabla desktop -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left p-3">Tipo de Vehículo</th>
                                <th class="text-left p-3">Precio por Hora</th>
                                <th class="text-left p-3">Precio Mínimo</th>
                                <th class="text-left p-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="ratesTable">
							<tr>
								<td colspan="4" class="p-3 text-center text-gray-500">Cargando tarifas...</td>
							</tr>
						</tbody>

                    </table>
                </div>
                
                <!-- Vista móvil -->
				<div id="ratesMobile" class="md:hidden space-y-4">
					<div class="p-3 text-center text-gray-500">Cargando tarifas...</div>
				</div>
				<div id="ratesPagination" class="flex justify-center mt-4 space-x-2"></div>
				
				<div id="ratesPagination" class="flex justify-center mt-4 space-x-2"></div>
            </div>
        </div>
		

		
		<!-- Modal Corte de Caja -->
		<div id="corteCajaModal" class="fixed inset-0 flex bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
			<div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-5xl grid md:grid-cols-2 gap-6">
				
				<!-- LADO IZQUIERDO: Tabla de cortes -->
				<div class="overflow-y-auto max-h-[70vh]">
					<h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100">Historial de Cortes de Caja</h3>
					<table class="min-w-full text-sm text-left text-gray-600 dark:text-gray-300">
						<thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-xs">
							<tr>
								<th class="px-3 py-2">Fecha</th>
								<th class="px-3 py-2">Total Día</th>
								<th class="px-3 py-2">Registros</th>
								<th class="px-3 py-2 text-center">Ticket</th>
							</tr>
						</thead>
						<tbody id="tablaCortesUsuario">
							<tr><td colspan="4" class="text-center py-4 text-gray-500">Cargando cortes...</td></tr>
						</tbody>
					</table>
					<div class="flex justify-center items-center mt-4 space-x-2">
						<button id="prevCortesUsuario" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">Anterior</button>
						<span id="paginaCortesUsuario" class="text-sm text-gray-500 dark:text-gray-400"></span>
						<button id="nextCortesUsuario" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">Siguiente</button>
					</div>
				</div>

				<!-- LADO DERECHO: Confirmar corte -->
				<div>
					<h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100">Confirmar Corte de Caja</h3>
					<p class="mb-2 text-gray-700 dark:text-gray-300">
						Solo se puede realizar un corte por día.
					</p>
					<p class="mb-4 font-semibold text-gray-800 dark:text-gray-200">
						Hoy es <span id="fechaCorteCaja"></span>.
					</p>
					<label for="passwordConfirm" class="block mb-2 font-semibold">Contraseña:</label>
					<input id="passwordConfirm" type="password" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded mb-4">
					<div class="flex justify-end space-x-2">
						<button id="cancelCorteCaja" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
						<button id="confirmCorteCaja" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Aceptar</button>
					</div>
				</div>
			</div>
		</div>


        <!-- Sección Registros -->
		<div id="records" class="hidden">
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
				<h2 class="text-2xl font-bold mb-6">Registros de Vehículos</h2>
				
				<!-- Barra de búsqueda -->
				<div class="mb-6">
					<input id="searchRegistros" type="text" 
						   placeholder="Buscar por placa, modelo, color o asignación..." 
						   class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
				</div>
				
				<!-- Tabla desktop -->
				<div class="hidden md:block overflow-x-auto">
					<table id="registrosTable" class="w-full border-collapse">
						<thead>
							<tr class="border-b border-gray-200 dark:border-gray-700">
								<th class="text-left p-3">Placa</th>
								<th class="text-left p-3">Modelo / Color</th>
								<th class="text-left p-3">Asignación</th>
								<th class="text-left p-3">Entrada</th>
								<th class="text-left p-3">Estado</th>
								<th class="text-left p-3">Acciones</th>
							</tr>
						</thead>
						<tbody id="registrosBody">
							<!-- Aquí se insertan dinámicamente los registros -->
						</tbody>
					</table>
				</div>
				
				<!-- Vista móvil -->
				<div id="registrosMobile" class="md:hidden space-y-4">
					<!-- Aquí se insertan dinámicamente los registros en formato móvil -->
				</div>
				
				<!-- Paginación -->
				<div class="flex justify-center mt-6">
					<div id="paginationRegistros" class="flex space-x-2">
						<!-- Botones de paginación generados dinámicamente -->
					</div>
				</div>
				
			</div>
		</div>

                
             

        <!-- Sección Registrar -->
        <div id="register" class="hidden">
            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold mb-4 text-green-600">Estacionar Vehículo</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Registra la entrada de un nuevo vehículo</p>
                    <button onclick="openParkModal()" class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg transition-colors">
                        <i class="fas fa-car mr-2"></i> Registrar Entrada
                    </button>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                    <h3 class="text-xl font-bold mb-4 text-red-600">Salida de Vehículo</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Procesa la salida y cobro</p>
                    <button onclick="openExitModal()" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i> Procesar Salida
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Navegación inferior -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 py-2">
        <div class="flex justify-around items-center max-w-md mx-auto">
            <button onclick="showSection('profile')" class="nav-btn flex flex-col items-center p-2 text-gray-600 dark:text-gray-400 hover:text-primary-500">
                <i class="fas fa-user text-lg mb-1"></i>
                <span class="text-xs">Perfil</span>
            </button>
            <button onclick="showSection('rates')" class="nav-btn flex flex-col items-center p-2 text-gray-600 dark:text-gray-400 hover:text-primary-500">
                <i class="fas fa-dollar-sign text-lg mb-1"></i>
                <span class="text-xs">Tarifas</span>
            </button>
            
            <!-- Logo central -->
            <div class="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden wheel-container relative cursor-pointer" onclick="showSection('welcome')">
                <!-- Imagen placeholder estática -->
                <img src="media/parkinginternet.png" alt="Logo" class="w-full h-full object-cover rounded-full transition-all duration-500">
                
                <!-- Llanta completa (oculta por defecto) -->
                <div class="absolute inset-0 tire-assembly opacity-0 transition-all duration-500">
                    <!-- Neumático exterior (goma negra) -->
                    <div class="absolute inset-0 bg-gray-900 rounded-full tire-rubber">
                        <!-- Textura del neumático -->
                        <div class="absolute inset-1 border-2 border-gray-700 rounded-full"></div>
                        <div class="absolute inset-0.5 border border-gray-800 rounded-full"></div>
                        <!-- Surcos del neumático -->
                        <div class="tire-treads absolute inset-0">
                            <div class="absolute w-full h-0.5 bg-gray-700 top-2 left-0"></div>
                            <div class="absolute w-full h-0.5 bg-gray-700 bottom-2 left-0"></div>
                            <div class="absolute h-full w-0.5 bg-gray-700 left-2 top-0"></div>
                            <div class="absolute h-full w-0.5 bg-gray-700 right-2 top-0"></div>
                        </div>
                    </div>
                    
                    <!-- Rin metálico -->
                    <div class="absolute inset-2 bg-gradient-to-br from-gray-200 via-gray-300 to-gray-500 rounded-full wheel-rim flex items-center justify-center">
                        <!-- Rayos del rin -->
                        <div class="wheel-spokes absolute inset-0">
                            <div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2"></div>
                            <div class="absolute h-full w-0.5 bg-gray-600 left-1/2 top-0 transform -translate-x-1/2"></div>
                            <div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2 rotate-45 origin-center"></div>
                            <div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2 -rotate-45 origin-center"></div>
                            <div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2 rotate-22.5 origin-center"></div>
                            <div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2 -rotate-22.5 origin-center"></div>
                        </div>
                        <!-- Centro del rin con imagen -->
						<div class="absolute w-5 h-5 rounded-full flex items-center justify-center z-10 overflow-hidden top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
							<img src="media/parkinginternet.png" alt="Logo" class="w-full h-full object-cover">
						</div>
                        <!-- Efectos de brillo metálico -->
                        <div class="absolute inset-0.5 border border-gray-400 rounded-full rim-highlight"></div>
                    </div>
                </div>
            </div>
            
            <button onclick="showSection('records')" class="nav-btn flex flex-col items-center p-2 text-gray-600 dark:text-gray-400 hover:text-primary-500">
                <i class="fas fa-clipboard-list text-lg mb-1"></i>
                <span class="text-xs">Registros</span>
            </button>
            <button onclick="showSection('register')" class="nav-btn flex flex-col items-center p-2 text-gray-600 dark:text-gray-400 hover:text-primary-500">
                <i class="fas fa-car text-lg mb-1"></i>
                <span class="text-xs">Registrar</span>
            </button>
        </div>
    </nav>

    <!-- Modal Nueva Tarifa -->
	<div id="rateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
		<div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md">
			<h3 class="text-xl font-bold mb-4" id="rateModalTitle">Nueva Tarifa</h3>
			<form id="rateForm">
				<div class="mb-4">
					<label class="block text-sm font-medium mb-1">Tipo de Vehículo</label>
					<select id="rateVehicleType" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
						<option value="Automóvil">Automóvil</option>
						<option value="Motocicleta">Motocicleta</option>
						<option value="Camioneta">Camioneta</option>
						<option value="Bicicleta">Bicicleta</option>
						<option value="Otro">Otro</option>
					</select>
					<input type="text" id="rateVehicleOther" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 mt-2 hidden" placeholder="Escribe el tipo de vehículo">
				</div>
				<div class="mb-4">
					<label class="block text-sm font-medium mb-1">Precio por Hora</label>
					<input type="number" step="0.01" id="rateHourlyPrice" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
				</div>
				<div class="mb-6">
					<label class="block text-sm font-medium mb-1">Precio Mínimo</label>
					<input type="number" step="0.01" id="rateMinPrice" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
				</div>
				<div class="flex gap-3">
					<button type="button" onclick="closeModal('rateModal')" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
						Cancelar
					</button>
					<button type="submit" class="flex-1 bg-primary-500 hover:bg-primary-600 text-white py-2 rounded-lg transition-colors">
						Guardar
					</button>
				</div>
			</form>
		</div>
	</div>

    <!-- Modal Actualizar Estado -->
	<div id="updateModal" data-barcode="" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
		<div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md">
			<h3 class="text-xl font-bold mb-4">Actualizar Estado del Vehículo</h3>
			<div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
				<h4 class="font-semibold mb-2">Información del Vehículo</h4>
				<p><strong>Placa:</strong> <span id="updatePlate"></span></p>
				<p><strong>Modelo:</strong> <span id="updateModel"></span></p>
				<p><strong>Entrada:</strong> <span id="updateEntry"></span></p>
				<p><strong>Tiempo:</strong> <span id="updateTime">-</span></p>
			</div>

			<div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg mb-4">
				<h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Total a Cobrar</h4>
				<p class="text-2xl font-bold text-blue-600 dark:text-blue-300" id="updateTotalAmount">$0.00</p>
				<p class="text-sm text-gray-600 dark:text-gray-300 mt-1"><strong>Detalle:</strong> <span id="updateTarifaDetalle">-</span></p>
			</div>

			<div class="mb-4">
				<label class="block text-sm font-medium mb-1">Monto Recibido</label>
				<input type="number" step="0.01" id="updateReceivedAmount" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
			</div>

			<div class="mb-4">
				<p class="text-lg"><strong>Cambio:</strong> $<span id="updateChangeAmount">0.00</span></p>
			</div>

			<div class="flex gap-3">
				<button onclick="closeModal('updateModal')" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
					Cancelar
				</button>
				<button onclick="completeUpdate()" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg transition-colors">
					Completar Salida
				</button>
			</div>
		</div>
	</div>


    <!-- Modal Estacionar -->
	<div id="parkModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
		<div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-2xl">
			<h3 class="text-xl font-bold mb-4">Registrar Vehículo</h3>
			<form id="parkForm">
				<!-- Tipo -->
				<div class="mb-4">
					<label for="vehicleType" class="block text-sm font-medium mb-1">Tipo de Vehículo</label>
					<select id="vehicleType" name="vehicleType" 
						class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
						<!-- Opciones cargadas dinámicamente desde la BD -->
					</select>
				</div>
				<!-- Placa -->
				<div class="mb-4">
					<label class="block text-sm font-medium mb-1">Placa</label>
					<input type="text" id="licensePlate" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700" placeholder="ABC-123">
				</div>
				<!-- Modelo -->
				<div class="mb-4">
					<label class="block text-sm font-medium mb-1">Modelo y Color</label>
					<input type="text" id="modelColor" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700" placeholder="Honda Civic Azul">
				</div>
				<!-- Cliente -->
				<div class="mb-6">
					<label class="block text-sm font-medium mb-1">Nombre del Cliente (Opcional)</label>
					<input type="text" id="customerName" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
				</div>

				<!-- Selección de espacio -->
				<div hidden class="mb-6">
					<label class="block text-sm font-medium mb-2">Seleccionar Espacio</label>
					<div id="parkingGrid" class="grid gap-2"></div>
					<input type="hidden" id="selectedSpace" name="selectedSpace">
				</div>

				<!-- Botones -->
				<div class="flex gap-3">
					<button type="button" onclick="closeModal('parkModal')" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
						Cancelar
					</button>
					<button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg transition-colors">
						Registrar
					</button>
				</div>
			</form>
		</div>
	</div>


    <!-- Modal Salida -->
	<div id="exitModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
		<div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md">
			<h3 class="text-xl font-bold mb-4">Procesar Salida</h3>
			<div id="exitStep1">
				<div class="mb-4">
					<label class="block text-sm font-medium mb-1">Código de Barras del Ticket</label>
					<input type="text" id="barcodeInput" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700" placeholder="Escanear o ingresar código">
				</div>
				<button onclick="processExit()" class="w-full bg-primary-500 hover:bg-primary-600 text-white py-2 rounded-lg transition-colors">
					Buscar Vehículo
				</button>
			</div>
			
			<div id="exitStep2" class="hidden">
				<div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
					<h4 class="font-semibold mb-2">Información del Vehículo</h4>
					<p><strong>Placa:</strong> <span id="exitPlate">ABC-123</span></p>
					<p><strong>Modelo:</strong> <span id="exitModel">Honda Civic Azul</span></p>
					<p><strong>Entrada:</strong> <span id="exitEntry">14:30</span></p>
					<p><strong>Tiempo:</strong> <span id="exitTime">2h 30min</span></p>
				</div>
				
				<div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg mb-4">
					<h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Total a Cobrar</h4>
					<p class="text-2xl font-bold text-blue-600 dark:text-blue-300" id="totalAmount">$37.50</p>
					<p class="text-sm text-gray-600 dark:text-gray-300 mt-1"><strong>Detalle:</strong> <span id="exitTarifaDetalle"></span></p>
				</div>
				
				<div class="mb-4">
					<label class="block text-sm font-medium mb-1">Monto Recibido</label>
					<input type="number" step="0.01" id="receivedAmount" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
				</div>
				
				<div class="mb-4">
					<p class="text-lg"><strong>Cambio:</strong> $<span id="changeAmount">0.00</span></p>
				</div>
				
				<div class="flex gap-3">
					<button onclick="closeModal('exitModal')" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
						Cancelar
					</button>
					<button onclick="completeSale()" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg transition-colors">
						Completar Venta
					</button>
				</div>
			</div>
		</div>
	</div>

    <!-- Modal Ticket de Estacionamiento -->
	<div id="ticketModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
		<div class="bg-white rounded-xl p-6 w-full max-w-sm">
			<div id="parkingTicket" class="text-center text-black bg-white">
				<div class="border-2 border-dashed border-gray-300 p-4">
					
					<!-- Logotipo -->
					<div class="w-16 h-16 mx-auto mb-2">
						<img id="ticketLogo" src="media/parkinginternet.png" class="w-16 h-16 rounded-full object-cover" alt="Logo">
					</div>

					<!-- Nombre estacionamiento -->
					<h3 class="font-bold text-lg mb-2 text-black" id="ticketEstacionamiento">Nombre Estacionamiento</h3>

					<div class="text-sm space-y-1 mb-4 text-black">
						<p><strong>Placa:</strong> <span id="ticketPlate"></span></p>
						<p><strong>Vehículo:</strong> <span id="ticketVehicle"></span></p>
						<p><strong>Entrada:</strong> <span id="ticketEntry"></span></p>
						<p><strong>Asignación:</strong> <span id="ticketAsigna"></span></p>
						<p><strong>Tarifa:</strong> <span id="ticketRate"></span></p>
					</div>

					<!-- Código de barras real -->
					<svg id="ticketBarcode"></svg>
					<p class="text-xs text-black mt-1" id="ticketCode">PS-2024-001</p>
					<p><span id="estMensaje"></span></p>
					<div class="mt-4 text-xs text-gray-600">
						<p>Contrata Tecnología en Hackers Internet www.hackersinternet.mx</p>
					</div>

				</div>
			</div>

			<div class="flex gap-3 mt-4">
				<button onclick="closeModal('ticketModal')" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
					Cerrar
				</button>
				<button onclick="printTicket()" class="flex-1 bg-primary-500 hover:bg-primary-600 text-white py-2 rounded-lg transition-colors">
					<i class="fas fa-print mr-2"></i> Imprimir
				</button>
			</div>
		</div>
	</div>
	
	<!-- Modal Ticket de Salida -->
	<div id="ticketSalidaModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
		<div class="bg-white rounded-xl p-6 w-full max-w-sm">
			<div id="parkingTicketSalida" class="text-center text-black bg-white">
				<div class="border-2 border-dashed border-gray-300 p-4">
					
					<!-- Logotipo -->
					<div class="w-16 h-16 mx-auto mb-2">
						<img id="ticketSalidaLogo" src="media/parkinginternet.png" class="w-16 h-16 rounded-full object-cover" alt="Logo">
					</div>

					<!-- Nombre del estacionamiento -->
					<h3 id="ticketSalidaEstacionamiento" class="font-bold text-lg mb-1 text-black">Nombre Estacionamiento</h3>
					<p id="ticketSalidaDireccion" class="text-xs text-gray-600 mb-3">Dirección del estacionamiento</p>

					<!-- Datos del ticket -->
					<div class="text-sm space-y-1 mb-4 text-black">
						<p><strong>Cliente:</strong> <span id="ticketSalidaCliente">-</span></p>
						<p><strong>Placa:</strong> <span id="ticketSalidaPlaca">-</span></p>
						<p><strong>Vehículo:</strong> <span id="ticketSalidaVehiculo">-</span></p>
						<p><strong>Entrada:</strong> <span id="ticketSalidaEntrada">-</span></p>
						<p><strong>Salida:</strong> <span id="ticketSalidaSalida">-</span></p>
						<p><strong>Horas cobradas:</strong> <span id="ticketSalidaHoras">-</span></p>
						<p><strong>Tarifa hora:</strong> <span id="ticketSalidaTarifa">-</span></p>
						<p><strong>Tarifa mínima:</strong> <span id="ticketSalidaTarifaMin">-</span></p>
						<p><strong>Total cobrado:</strong> <span id="ticketSalidaTotal">-</span></p>
						<p><strong>Cajero:</strong> <span id="ticketSalidaCajero">-</span></p>
					</div>

					<!-- Código de barras -->
					<svg id="ticketSalidaBarcode"></svg>
					<p id="ticketSalidaCodigo" class="text-xs text-black mt-1">---</p>

					<!-- Pie de ticket -->
					<div class="mt-4 text-xs text-gray-600">
						<p>Contrata Tecnología en Hackers Internet www.hackersinternet.mx</p>
					</div>

				</div>
			</div>

			<!-- Botones -->
			<div class="flex gap-3 mt-4">
				<button onclick="closeModal('ticketSalidaModal')" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg transition-colors">
					Cerrar
				</button>
				<button onclick="printTicketSalida()" class="flex-1 bg-primary-500 hover:bg-primary-600 text-white py-2 rounded-lg transition-colors">
					<i class="fas fa-print mr-2"></i> Imprimir
				</button>
			</div>
		</div>
	</div>


	<!-- JsBarcode -->
	<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>


    <style>
        body {
            box-sizing: border-box;
        }
        
        /* Animaciones de rueda realista */
        .wheel-container {
            transition: all 0.3s ease;
        }
        
        /* Estado normal - mostrar placeholder */
        .wheel-container .placeholder-image,
        .wheel-container .placeholder-fallback {
            opacity: 1;
        }
        
        .wheel-container .tire-assembly {
            opacity: 0;
        }
        
        /* Estado hover - mostrar llanta y ocultar placeholder */
        .wheel-container:hover .placeholder-image,
        .wheel-container:hover .placeholder-fallback {
            opacity: 0;
        }
        
        .wheel-container:hover .tire-assembly {
            opacity: 1;
        }
        
        .wheel-container:hover {
            transform: scale(1.2);
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
        }
        
        .wheel-container:hover .wheel-spokes {
            animation: spin 0.6s linear infinite;
        }
        
        .wheel-container:hover .tire-treads {
            animation: spin 0.6s linear infinite;
        }
        
        .wheel-container:hover .tire-rubber {
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.6);
        }
        
        .wheel-container:hover .wheel-rim {
            background: linear-gradient(135deg, #f3f4f6, #d1d5db, #9ca3af);
            box-shadow: inset 0 3px 6px rgba(255, 255, 255, 0.4), inset 0 -3px 6px rgba(0, 0, 0, 0.4);
        }
        
        .wheel-container:hover .rim-highlight {
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        .wheel-container:active {
            transform: scale(1.1);
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Efectos de profundidad del neumático */
        .tire-rubber {
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }
        
        .wheel-rim {
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.3), inset 0 -2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .wheel-spokes,
        .tire-treads {
            transition: all 0.3s ease;
        }
        
        .placeholder-image,
        .placeholder-fallback,
        .tire-assembly {
            transition: opacity 0.5s ease;
        }
        
        /* Efectos de brillo en hover para las tarjetas */
        .info-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 197, 253, 0.1));
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .info-card:hover .info-icon {
            transform: scale(1.2);
            color: #3b82f6;
        }
        
        .info-icon {
            transition: all 0.3s ease;
        }
    </style>
	<script>
	
	</script>
    <script>
		document.addEventListener('DOMContentLoaded', () => {
			const btnCorteCaja = document.getElementById('btnCorteCaja');
			const modal = document.getElementById('corteCajaModal');
			const cancelBtn = document.getElementById('cancelCorteCaja');
			const confirmBtn = document.getElementById('confirmCorteCaja');
			const fechaSpan = document.getElementById('fechaCorteCaja');
			const ticketDiv = document.getElementById('ticketCorteCaja');

			const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
			const hoy = new Date();
			const fechaStr = `${dias[hoy.getDay()]} ${hoy.toLocaleDateString('es-MX')}`;
			fechaSpan.textContent = fechaStr;

			btnCorteCaja.addEventListener('click', () => {
				modal.classList.remove('hidden');
				loadCortesUsuario(); // cargar cortes al abrir el modal
			});

			cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

			confirmBtn.addEventListener('click', async () => {
				const password = document.getElementById('passwordConfirm').value.trim();
				if (!password) {
					alert('Por favor, ingresa tu contraseña.');
					return;
				}

				try {
					const res = await fetch('controladores/hacer_corte_caja.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: `password=${encodeURIComponent(password)}`
					});

					const data = await res.json();

					if (data.status === 'error') {
						alert(data.message);
						return;
					}

					modal.classList.add('hidden');

					const c = data.corte; // asegúrate que tu PHP devuelve todo como en get_ticket_corte.php
					const ancho = c.ticket_ancho || 300;

					let registrosHTML = "";
					c.registros.forEach(r => {
						registrosHTML += `
							<tr>
								<td>${r.barcode}</td>
								<td>${r.horas_cobradas}</td>
								<td>$${parseFloat(r.total).toFixed(2)}</td>
							</tr>
						`;
					});

					const ticketHTML = `
						<div class="ticket">
							<img src="${c.perfil_img}" alt="Logo" style="max-height:60px;">
							<h3>${c.estacionamiento}</h3>
							<p><strong>Corte de Caja</strong></p>
							<p>${fechaStr}</p>
							<hr>
							<table style="width:100%; font-size:12px; border-collapse:collapse;">
								<thead>
									<tr><th>Folio</th><th>Horas</th><th>Total</th></tr> 
								</thead>
								<tbody>
									${registrosHTML}
								</tbody>
							</table>
							<hr>
							<p><strong>Total cobrado:</strong> $${c.total_dia}</p>
							<p><strong>Total registros:</strong> ${c.total_registros}</p>
							<p><strong>Horas mínimas:</strong> ${c.horas_minimas}</p>
							<p><strong>Horas completas:</strong> ${c.horas_completas}</p>
							<hr>
							<p style="font-size:10px; text-align:center; margin-top:6px;">
								https://parqueo.hackersinternet.mx
							</p>
						</div>
					`;

					// Mostrar en modal si quieres, opcional
					ticketDiv.innerHTML = ticketHTML;
					ticketDiv.classList.remove('hidden');

					// Imprimir automáticamente
					const printWindow = window.open('', '_blank');
					printWindow.document.write(`
						<html>
						<head>
							<title>Corte de Caja</title>
							<style>
								@page { size: ${ancho}mm auto; margin: 0; }
								body { font-family: Arial, sans-serif; margin: 0; padding: 5px; width: ${ancho}mm; text-align:center; }
								table, th, td { border:none; text-align:center; }
								th, td { padding: 2px 0; }
								img { display:block; margin:0 auto 5px; }
								hr { border:0; border-top:1px dashed #000; margin:4px 0; }
							</style>
						</head>
						<body>
							${ticketHTML}
							<script>
								window.onload = function() { window.print(); window.close(); };
							<\/script>
						</body>
						</html>
					`);
					printWindow.document.close();

					loadCortesUsuario(); // refrescar tabla después del corte

				} catch (err) {
					console.error(err);
					alert("Error al generar el ticket del corte");
				}
			});

		});

		let currentPageCortes = 1;

		// Cargar cortes del usuario
		async function loadCortesUsuario(page = 1) {
			try {
				const res = await fetch(`controladores/get_cortes_usuario.php?page=${page}`);
				const data = await res.json();
				const tabla = document.getElementById('tablaCortesUsuario');
				if (!tabla) return;

				tabla.innerHTML = '';

				if (!data.cortes || data.cortes.length === 0) {
					tabla.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">No tienes cortes registrados.</td></tr>`;
					return;
				}

				data.cortes.forEach(corte => {
					// Forzar que id sea numérico
					const corteId = parseInt(corte.id, 10);
					tabla.innerHTML += `
						<tr class="border-b border-gray-200 dark:border-gray-700">
							<td class="px-3 py-2">${corte.fecha}</td>
							<td class="px-3 py-2">$${parseFloat(corte.total_dia).toFixed(2)}</td>
							<td class="px-3 py-2">${corte.total_registros}</td>
							<td class="px-3 py-2 text-center">
								<button class="text-yellow-500 hover:text-yellow-600" onclick="verTicket(${corteId})">
									<i class="fas fa-receipt"></i>
								</button>
							</td>
						</tr>`;
				});

				document.getElementById('paginaCortesUsuario').textContent = `Página ${data.page} de ${data.total_pages}`;
				document.getElementById('prevCortesUsuario').disabled = page <= 1;
				document.getElementById('nextCortesUsuario').disabled = page >= data.total_pages;

				currentPageCortes = page;
			} catch (err) {
				console.error("Error al cargar cortes:", err);
			}
		}

		document.getElementById('prevCortesUsuario').addEventListener('click', () => loadCortesUsuario(currentPageCortes - 1));
		document.getElementById('nextCortesUsuario').addEventListener('click', () => loadCortesUsuario(currentPageCortes + 1));

		let currentTicketWidth = 300; // igual que en los demás tickets

		async function verTicket(idCorte) {
			try {
				// Forzar id a número
				idCorte = parseInt(idCorte, 10);
				if (isNaN(idCorte) || idCorte <= 0) {
					alert("ID de corte inválido.");
					return;
				}

				const res = await fetch(`controladores/get_ticket_corte.php?id=${idCorte}`);
				const data = await res.json();

				if (!data.success) {
					alert(data.message || "No se pudo cargar el ticket del corte");
					return;
				}

				const c = data.corte;
				const ancho = c.ticket_ancho || 300;

				let registrosHTML = "";
				c.registros.forEach(r => {
					registrosHTML += `
						<tr>
							<td>${r.barcode}</td>
							<td>${r.horas_cobradas}</td>
							<td>${new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(r.total)}</td>
						</tr>
					`;
				});

				const ticketHTML = `
					<div class="ticket">
						<img src="${c.perfil_img}" alt="Logo" style="max-height:60px;">
						<h3>${c.estacionamiento}</h3>
						<p><strong>Corte de Caja</strong></p>
						<p>${c.fecha}</p>
						<hr>
						<table style="width:100%; font-size:12px; border-collapse:collapse;">
							<thead>
								<tr><th>Folio</th><th>Horas</th><th>Total</th></tr>
							</thead>
							<tbody>
								${registrosHTML}
							</tbody>
						</table>
						<hr>
						<p><strong>Total cobrado:</strong> ${new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(c.total_dia)}</p>
						<p><strong>Total registros:</strong> ${c.total_registros}</p>
						<p><strong>Horas mínimas:</strong> ${c.horas_minimas}</p>
						<p><strong>Horas completas:</strong> ${c.horas_completas}</p>
						<hr>
						<p style="font-size:10px; text-align:center; margin-top:6px;">
							https://parqueo.hackersinternet.mx
						</p>
					</div>
				`;

				const printWindow = window.open('', '_blank');
				printWindow.document.write(`
					<html>
					<head>
						<title>Corte de Caja</title>
						<style>
							@page { size: ${ancho}mm auto; margin: 0; }
							body {
								font-family: Arial, sans-serif;
								margin: 0;
								padding: 5px;
								width: ${ancho}mm;
								text-align: center;
							}
							table, th, td {
								border: none;
								text-align: center;
							}
							th, td {
								padding: 2px 0;
							}
							img {
								display: block;
								margin: 0 auto 5px;
							}
							hr {
								border: 0;
								border-top: 1px dashed #000;
								margin: 4px 0;
							}
						</style>
					</head>
					<body>
						${ticketHTML}
						<script>
							window.onload = function() {
								window.print();
								window.close();
							};
						<\/script>
					</body>
					</html>
				`);
				printWindow.document.close();

			} catch (err) {
				console.error(err);
				alert("Error al generar el ticket del corte");
			}
		}








        // Toggle tema oscuro/claro
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
		let currentRecordPage = 1;
        
        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });
        
        // Cargar tema guardado o establecer modo oscuro por defecto
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            html.classList.remove('dark');
        } else {
            // Por defecto modo oscuro
            html.classList.add('dark');
            if (!savedTheme) {
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Navegación entre secciones
        function showSection(sectionId) {
            // Ocultar todas las secciones
            const sections = ['welcome', 'profile', 'rates', 'records', 'register'];
            sections.forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            
            // Mostrar sección seleccionada
            document.getElementById(sectionId).classList.remove('hidden');
            
            // Actualizar botones de navegación
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('text-primary-500');
                btn.classList.add('text-gray-600', 'dark:text-gray-400');
            });
            event.target.closest('.nav-btn').classList.add('text-primary-500');
        }
        
        // Funciones de modales
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.getElementById(modalId).classList.add('flex');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.getElementById(modalId).classList.remove('flex');
			
			// Limpiar input de "Otro" al cerrar modal
			if (modalId === 'rateModal') {
				rateVehicleOther.classList.add('hidden');
				rateVehicleOther.value = '';
			}
        }
        
        // Abrir modal para nueva tarifa
		document.getElementById('newRateBtn').addEventListener('click', () => {
			editingRateId = null; // reset
			document.getElementById('rateModalTitle').textContent = 'Nueva Tarifa';
			document.getElementById('rateVehicleType').value = 'Automóvil';
			document.getElementById('rateHourlyPrice').value = '';
			document.getElementById('rateMinPrice').value = '';
			openModal('rateModal');
		});
        
        // Guardar tarifa (nueva o edición)
		
		const rateVehicleType = document.getElementById('rateVehicleType');
		const rateVehicleOther = document.getElementById('rateVehicleOther');

		rateVehicleType.addEventListener('change', () => {
			if (rateVehicleType.value === 'Otro') {
				rateVehicleOther.classList.remove('hidden');
				rateVehicleOther.focus();
			} else {
				rateVehicleOther.classList.add('hidden');
				rateVehicleOther.value = '';
			}
		});
		
		
		document.getElementById('rateForm').addEventListener('submit', (e) => {
			e.preventDefault();
			//rateVehicleOther.classList.add('hidden');
			// Usar el valor de "Otro" si se selecciona
			let tipo = rateVehicleType.value;
			if (tipo === 'Otro') {
				tipo = rateVehicleOther.value.trim(); // Tomar lo que escribió el usuario
				if (!tipo) {
					alert('Por favor ingresa el tipo de vehículo');
					return; // Detener envío si está vacío
				}
			}
			//rateVehicleOther.classList.add('hidden');
			const precio_hora = parseFloat(document.getElementById('rateHourlyPrice').value);
			const precio_minimo = parseFloat(document.getElementById('rateMinPrice').value);

			const payload = {
				tipo_vehiculo: tipo,
				precio_hora,
				precio_minimo
			};

			// Llamada fetch
			const url = editingRateId ? 'controladores/update_tarifa.php' : 'controladores/add_tarifa.php';
			if (editingRateId) payload.id = editingRateId;

			fetch(url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload)
			})
			.then(res => res.json())
			.then(data => {
				if (data.success) {
					alert('Tarifa guardada correctamente');
					closeModal('rateModal');
					loadRates();
				} else {
					alert(data.message || 'Error al guardar tarifa');
				}
			})
			.catch(err => alert('Error de conexión con el servidor'));
		});
        
        async function loadParkingSpaces() {
			try {
				const res = await fetch("controladores/get_espacios.php");
				const data = await res.json();

				if (!data.success) {
					document.getElementById("parkingGrid").innerHTML = 
						`<p class="text-red-500 text-sm">${data.message || "Error al cargar espacios"}</p>`;
					return;
				}

				const total = data.espacios;
				const ocupados = data.ocupados || [];
				const grid = document.getElementById("parkingGrid");
				grid.innerHTML = "";
				grid.className = "grid grid-cols-5 gap-2";

				let letraIndex = 0;
				let letra = "A";
				let firstFreeButton = null; // guardará el primer espacio libre

				for (let i = 1; i <= total; i++) {
					if ((i - 1) % 5 === 0) letra = String.fromCharCode(65 + letraIndex++);
					const code = `${letra}${((i - 1) % 5) + 1}`;
					const ocupado = ocupados.includes(code);

					const btn = document.createElement("button");
					btn.textContent = code;
					btn.type = "button";

					if (ocupado) {
						btn.className = `
							p-2 rounded-lg text-sm font-medium border
							bg-orange-300 text-white cursor-not-allowed
							dark:bg-orange-500
						`;
						btn.disabled = true;
					} else {
						btn.className = `
							p-2 rounded-lg text-sm font-medium border
							bg-blue-200 hover:bg-blue-300 text-gray-800
							dark:bg-blue-700 dark:hover:bg-blue-600 dark:text-white
						`;

						// guardar el primero libre si aún no se ha asignado
						if (!firstFreeButton) firstFreeButton = btn;
					}

					btn.addEventListener("click", () => {
						document.querySelectorAll("#parkingGrid button").forEach(b => b.classList.remove("ring-4", "ring-blue-400"));
						btn.classList.add("ring-4", "ring-blue-400");
						document.getElementById("selectedSpace").value = code;
					});

					grid.appendChild(btn);
				}

				// Seleccionar automáticamente el primer espacio libre
				if (firstFreeButton) {
					firstFreeButton.classList.add("ring-4", "ring-blue-400");
					document.getElementById("selectedSpace").value = firstFreeButton.textContent;
				} else {
					document.getElementById("selectedSpace").value = "";
					console.warn("No hay espacios disponibles.");
				}

			} catch (err) {
				document.getElementById("parkingGrid").innerHTML = `<p class="text-red-500">Error de conexión</p>`;
				console.error(err);
			}
		}




		// Cargar espacios cuando se abre el modal
		function openParkModal() {
			openModal("parkModal");
			loadParkingSpaces();
			loadTarifas();
		}
		
		
		
		
		
		
		// Al enviar el formulario
		document.getElementById("parkForm").addEventListener("submit", async function(e) {
			e.preventDefault();

			const licensePlate = document.getElementById("licensePlate").value.trim();
			const modelColor = document.getElementById("modelColor").value.trim();
			const selectedSpace = document.getElementById("selectedSpace").value;
			const vehicleTypeId = document.getElementById("vehicleType").value;
			const customerName = document.getElementById("customerName").value.trim();

			// Validación: al menos placa o modelo
			if (!licensePlate && !modelColor) {
				alert("Debes escribir al menos la Placa o el Modelo y Color del vehículo");
				return;
			}

			if (!selectedSpace) {
				alert("Debes seleccionar un espacio");
				return;
			}

			if (!vehicleTypeId) {
				alert("Debes seleccionar un tipo de vehículo");
				return;
			}

			const formData = new FormData();
			formData.append("licensePlate", licensePlate);
			formData.append("modelColor", modelColor);
			formData.append("selectedSpace", selectedSpace);
			formData.append("vehicleTypeId", vehicleTypeId);
			formData.append("customerName", customerName);

			try {
				const res = await fetch("controladores/registrar_vehiculo.php", {
					method: "POST",
					body: formData
				});
				const data = await res.json();

				if (data.success) {
					alert(data.message);
					closeModal("parkModal");
					loadParkingSpaces();
					loadRegistros();
					this.reset();
					document.getElementById("selectedSpace").value = "";
					
					// Mostrar ticket automáticamente
					if (data.barcode) {
						showTicketByBarcode(data.barcode);
					}
					
				} else {
					alert(data.message || "Error al registrar");
				}
			} catch (err) {
				alert("Error de conexión con el servidor");
				console.error(err);
			}
		});




        
        function openExitModal() {
            openModal('exitModal');
        }
        
        // Función para cambiar imagen de perfil
        document.getElementById('profileImageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const profileImage = document.getElementById('profileImage');
                    const profileIcon = document.getElementById('profileIcon');
                    
                    profileImage.src = e.target.result;
                    profileImage.classList.remove('hidden');
                    profileIcon.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
		
        // Abrir modal para editar tarifa
        function editRate(vehicleType, hourlyPrice, minPrice, rateId) {
			editingRateId = rateId; // guardar id para actualizar
			document.getElementById('rateModalTitle').textContent = 'Editar Tarifa';
			document.getElementById('rateHourlyPrice').value = hourlyPrice;
			document.getElementById('rateMinPrice').value = minPrice;

			let optionExists = Array.from(rateVehicleType.options).some(opt => opt.value === vehicleType);
			if (optionExists) {
				rateVehicleType.value = vehicleType;
				rateVehicleOther.classList.add('hidden');
				rateVehicleOther.value = '';
			} else {
				rateVehicleType.value = 'Otro';
				rateVehicleOther.classList.remove('hidden');
				rateVehicleOther.value = vehicleType;
			}

			openModal('rateModal');
}

        
        // Eliminar tarifa
		function deleteRate(rateId) {
			if (!confirm('¿Está seguro de que desea eliminar esta tarifa?')) return;

			fetch('controladores/delete_tarifa.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ id: rateId })
			})
			.then(res => res.json())
			.then(data => {
				if (data.success) {
					alert('Tarifa eliminada exitosamente');
					loadRates();
				} else {
					alert(data.message || 'Error al eliminar tarifa');
				}
			})
			.catch(err => alert('Error de conexión con el servidor'));
		}

        
        // Función para actualizar estado del vehículo
		async function openUpdateModal(barcode) {
			try {
				const res = await fetch(`controladores/get_exit.php?barcode=${encodeURIComponent(barcode)}`);
				const data = await res.json();

				if (!data.success) {
					alert(data.message || 'Error al obtener datos del vehículo');
					return;
				}

				const r = data.data;
				const modal = document.getElementById('updateModal');

				// Llenar modal con info real
				document.getElementById('updatePlate').textContent = r.placa || '-';
				document.getElementById('updateModel').textContent = r.modelo_color || '-';
				document.getElementById('updateEntry').textContent = new Date(r.entrada).toLocaleString();
				document.getElementById('updateTime').textContent = r.tiempo || '-';
				document.getElementById('updateTotalAmount').textContent = `$${r.total}`;
				//document.getElementById('updateTotalAmount').textContent = `$${parseFloat(r.total).toFixed(2)}`;
				document.getElementById('updateTarifaDetalle').textContent = r.detalle_tarifa || '-';

				// Guardar datos importantes en dataset del modal
				modal.dataset.barcode = r.barcode;        // barcode del registro
				modal.dataset.total = parseFloat(r.total); // total real

				// Limpiar campos de entrada y cambio
				document.getElementById('updateReceivedAmount').value = '';
				document.getElementById('updateChangeAmount').textContent = '0.00';

				// Abrir modal
				openModal('updateModal');

			} catch (err) {
				console.error(err);
				alert("Error al cargar información del vehículo");
			}
		}



		
		/////////////////////////// TICKET NUEVO
		// Mostrar ticket
		//let currentTicketWidth = 300; // valor por defecto

		async function showTicketByBarcode(barcode) {
			try {
				const res = await fetch(`controladores/get_ticket.php?barcode=${barcode}`);
				const data = await res.json();

				if (!data.success) {
					alert(data.message || "No se pudo cargar el ticket");
					return;
				}

				const r = data.data;

				// Guardar el ancho del ticket (o 300 si no está definido)
				currentTicketWidth = r.ticket_ancho ? parseInt(r.ticket_ancho) : 300;

				document.getElementById('ticketPlate').textContent = r.placa || '-';
				document.getElementById('ticketVehicle').textContent = r.modelo_color || r.tipo_vehiculo;
				document.getElementById('ticketEntry').textContent = new Date(r.entrada).toLocaleString();
				document.getElementById('ticketAsigna').textContent = r.asignacion || '-';
				document.getElementById('ticketRate').textContent = `$${parseFloat(r.precio_minimo).toFixed(2)} min / $${parseFloat(r.precio_hora).toFixed(2)} hr`;
				document.getElementById('ticketCode').textContent = r.barcode;
				document.getElementById('ticketLogo').src = r.perfil_img;
				document.getElementById('ticketEstacionamiento').textContent = r.estacionamiento_nombre;
				document.getElementById('estMensaje').textContent = r.estacionamiento_mensaje;
				

				// Generar código de barras real
				JsBarcode("#ticketBarcode", r.barcode, {
					format: "CODE128",
					lineColor: "#000",
					width: 2,
					height: 50,
					displayValue: false
				});

				// Abrir modal (opcional)
				openModal('ticketModal');

				// Imprimir automáticamente
				printTicket();

			} catch (err) {
				console.error(err);
				alert("Error al cargar el ticket");
			}
		}






		
		function printTicket() {
			const ticketContent = document.getElementById('parkingTicket').innerHTML;
			const printWindow = window.open('', '_blank');
			printWindow.document.write(`
				<html>
					<head>
						<title>Ticket de Estacionamiento</title>
						<style>
							@page {
								size: ${currentTicketWidth}mm auto; /* ancho en mm, alto automático */
								margin: 0;
							}
							body {
								font-family: Arial, sans-serif;
								margin: 0;
								padding: 0;
								width: ${currentTicketWidth}mm;
							}
							.ticket { 
								width: ${currentTicketWidth}mm; 
								margin: 0 auto; 
								padding: 5px; 
								text-align: center;
							}
							.ticket img {
								display: block;
								margin: 0 auto 5px;
								max-width: 20%;
								height: auto;
							}
							.ticket h3 { 
								font-size: 16px;
								margin: 5px 0;
							}
							.ticket p { 
								margin: 2px 0; 
								font-size: 14px; 
							}
							svg { 
								display: block; 
								margin: 5px auto; 
							}
						</style>
					</head>
					<body>
						<div class="ticket">${ticketContent}</div>
						<script>
							window.onload = function() { 
								window.print(); 
								window.close(); 
							};
						<\/script>
					</body>
				</html>
			`);
			printWindow.document.close();
		}





		///////////////////////////IMPRIMIR TICKET SALIDA
		
		async function showTicketSalida(barcode) {
			try {
				const res = await fetch(`controladores/get_ticket_salida.php?barcode=${barcode}`);
				const data = await res.json();

				if (!data.success) {
					alert(data.message || "No se pudo cargar el ticket de salida");
					return;
				}

				const r = data.data;
				currentTicketWidth = r.ticket_ancho ? parseInt(r.ticket_ancho) : 300;

				// Si el vehículo aún no ha salido, mostramos alerta y salimos
				if (!r.salida) {
					alert("El vehículo aún no ha salido del estacionamiento.");
					return;
				}

				// Si sí tiene salida, llenamos el ticket normalmente
				document.getElementById('ticketSalidaLogo').src = r.perfil_img || "media/parkinginternet.png";
				document.getElementById('ticketSalidaEstacionamiento').textContent = r.estacionamiento_nombre;
				document.getElementById('ticketSalidaDireccion').textContent = r.estacionamiento_direccion || '';
				document.getElementById('ticketSalidaCliente').textContent = r.cliente_nombre || '-';
				document.getElementById('ticketSalidaPlaca').textContent = r.placa;
				document.getElementById('ticketSalidaVehiculo').textContent = r.modelo_color || r.tipo_vehiculo;
				document.getElementById('ticketSalidaEntrada').textContent = new Date(r.entrada).toLocaleString();
				document.getElementById('ticketSalidaSalida').textContent = new Date(r.salida).toLocaleString();
				document.getElementById('ticketSalidaHoras').textContent = `${r.horas_cobradas} hrs`;
				document.getElementById('ticketSalidaTarifa').textContent = `$${parseFloat(r.precio_hora).toFixed(2)} / hr`;
				document.getElementById('ticketSalidaTarifaMin').textContent = `$${parseFloat(r.precio_minimo).toFixed(2)} / frac`;
				document.getElementById('ticketSalidaTotal').textContent = `$${parseFloat(r.total).toFixed(2)}`;
				document.getElementById('ticketSalidaCajero').textContent = r.usuario_nombre || "-";
				document.getElementById('ticketSalidaCodigo').textContent = r.barcode;

				// Generar código de barras
				JsBarcode("#ticketSalidaBarcode", r.barcode, {
					format: "CODE128",
					lineColor: "#000",
					width: 2,
					height: 50,
					displayValue: false
				});

				openModal('ticketSalidaModal');

			} catch (err) {
				console.error(err);
				alert("Error al cargar el ticket de salida");
			}
		}

		function printTicketSalida() {
			const ticketContent = document.getElementById('parkingTicketSalida').innerHTML;
			const printWindow = window.open('', '_blank');
			printWindow.document.write(`
				<html>
					<head>
						<title>Ticket de Salida</title>
						<style>
							@page { size: ${currentTicketWidth}mm auto; margin: 0; }
							body { font-family: Arial, sans-serif; margin: 0; padding: 0; width: ${currentTicketWidth}mm; }
							.ticket { width: ${currentTicketWidth}mm; margin: 0 auto; padding: 5px; text-align: center; }
							.ticket img { display: block; margin: 0 auto 5px; max-width: 20%; height: auto; }
							.ticket h3 { font-size: 16px; margin: 5px 0; }
							.ticket p { margin: 2px 0; font-size: 14px; }
							svg { display: block; margin: 5px auto; }
						</style>
					</head>
					<body>
						<div class="ticket">${ticketContent}</div>
						<script>
							window.onload = function() { window.print(); window.close(); };
						<\/script>
					</body>
				</html>
			`);
			printWindow.document.close();
		}

		
		///////////////////////////
		
		
		
        // Función para mostrar ticket
        function showTicket(plate, model, entry, rate) {
            document.getElementById('ticketPlate').textContent = plate;
            document.getElementById('ticketVehicle').textContent = model;
            document.getElementById('ticketEntry').textContent = entry;
            document.getElementById('ticketRate').textContent = rate;
            document.getElementById('ticketCode').textContent = 'PS-' + Date.now();
            openModal('ticketModal');
        }
        
        // === CALCULAR CAMBIO EN ACTUALIZACIÓN ===
		document.getElementById('updateReceivedAmount').addEventListener('input', (e) => {
			const totalStr = document.getElementById('updateTotalAmount').textContent.replace('$','').replace(/,/g,'');
			const total = parseFloat(totalStr) || 0;

			// Recibir el valor del input, también eliminando posibles comas
			const receivedStr = e.target.value.replace(/,/g,'');
			const received = parseFloat(receivedStr) || 0;

			const change = Math.max(0, received - total);
			document.getElementById('updateChangeAmount').textContent = change.toFixed(2);
		});

        
        // Completar actualización
        async function completeUpdate() {
			const barcode = document.getElementById('updateModal').dataset.barcode; 
			const total = parseFloat(document.getElementById('updateTotalAmount').textContent.replace('$','').replace(/,/g,'')) || 0;

			const received = parseFloat(document.getElementById('updateReceivedAmount').value) || 0;

			if (!barcode) {
				alert("No se encontró código de barras");
				return;
			}

			if (received < total) {
				alert("El monto recibido es menor al total");
				return;
			}

			try {
				const res = await fetch('controladores/update_exit.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: `barcode=${encodeURIComponent(barcode)}&total=${total}&received=${received}`
				});
				const data = await res.json();

				if (data.success) {
					alert("Salida registrada correctamente.");
					closeModal('updateModal');
					loadRegistros(currentRecordPage); // refrescar tabla
				} else {
					alert(data.message || "Error al actualizar salida");
				}
			} catch (err) {
				console.error(err);
				alert("Error de conexión al actualizar salida");
			}
		}




        
        
        
        // === PROCESAR SALIDA (abrir modal) ===
		async function processExit() {
			const barcode = document.getElementById('barcodeInput').value.trim();
			if (!barcode) {
				alert('Por favor ingrese el código de barras');
				closeModal('exitModal');
				return;
			}

			try {
				const res = await fetch(`controladores/get_exit.php?barcode=${encodeURIComponent(barcode)}`);
				const data = await res.json();

				if (!data.success) {
					alert(data.message || "No se pudo cargar la información del vehículo");
					return;
				}

				const r = data.data;

				// Llenar modal con info
				document.getElementById('exitPlate').textContent = r.placa;
				document.getElementById('exitModel').textContent = r.modelo_color;
				document.getElementById('exitEntry').textContent = new Date(r.entrada).toLocaleString();
				document.getElementById('exitTime').textContent = r.tiempo;
				document.getElementById('totalAmount').textContent = `$${r.total}`;
				document.getElementById('exitTarifaDetalle').textContent = r.detalle_tarifa; // 👈 nuevo detalle
				document.getElementById('exitModal').dataset.barcode = r.barcode; // guardar para el POST

				// Mostrar step 2
				document.getElementById('exitStep1').classList.add('hidden');
				document.getElementById('exitStep2').classList.remove('hidden');

			} catch (err) {
				console.error(err);
				alert("Error al consultar ticket");
			}
		}

		// === CALCULAR CAMBIO EN SALIDA ===
		document.getElementById('receivedAmount').addEventListener('input', (e) => {
			const total = parseFloat(document.getElementById('totalAmount').textContent.replace('$','')) || 0;
			const received = parseFloat(e.target.value) || 0;
			const change = Math.max(0, received - total);
			document.getElementById('changeAmount').textContent = change.toFixed(2);
		});
        
        // Completar venta
		async function completeSale() {
			const barcode = document.getElementById('exitModal').dataset.barcode;
			const total = parseFloat(document.getElementById('totalAmount').textContent.replace('$','').replace(/,/g,'')) || 0;

			const received = parseFloat(document.getElementById('receivedAmount').value) || 0;

			if (!barcode) {
				alert("No hay ticket cargado");
				return;
			}

			if (received < total) {
				alert("El monto recibido es menor al total");
				return;
			}

			try {
				const res = await fetch('controladores/update_exit.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: `barcode=${encodeURIComponent(barcode)}&total=${total}&received=${received}`
				});
				const data = await res.json();

				if (data.success) {
					alert("Salida registrada correctamente.");

					// Cerrar modal de cobro y recargar la vista
					closeModal('exitModal');
					loadRates();
					loadRegistros(currentRecordPage);
					document.getElementById('exitStep1').classList.remove('hidden');
					document.getElementById('exitStep2').classList.add('hidden');

					// Esperar un momento y mostrar + imprimir el ticket de salida
					setTimeout(async () => {
						try {
							await showTicketSalida(barcode); // Carga y abre el modal de salida
							setTimeout(() => {
								printTicketSalida(); // Imprime automáticamente cuando el modal ya cargó
							}, 600); // Pequeña pausa para asegurar que el DOM y JsBarcode estén listos
						} catch (err) {
							console.error("Error al mostrar o imprimir el ticket de salida:", err);
						}
					}, 500);

				} else {
					alert(data.message || "Error al registrar salida");
				}

			} catch (err) {
				console.error(err);
				alert("Error de conexión al registrar salida");
			}
		}



        
		// Guardar cambios de perfil y estacionamiento
		document.getElementById('saveProfileBtn').addEventListener('click', async () => {
			// Datos del usuario
			const name = document.getElementById('profileName').value.trim();
			const email = document.getElementById('profileEmail').value.trim();
			const phone = document.getElementById('profilePhone').value.trim();
			const job = document.getElementById('profileJob').value.trim();

			// Datos del estacionamiento
			const parkingName = document.getElementById('parkingName').value.trim();
			const parkingMessage = document.getElementById('parkingMessage').value.trim();
			const parkingAddress = document.getElementById('parkingAddress').value.trim();
			const parkingSpaces = document.getElementById('parkingSpaces').value.trim();
			const ticketSize = document.getElementById('ticketSize').value;

			const formData = new FormData();
			formData.append('name', name);
			formData.append('email', email);
			formData.append('phone', phone);
			formData.append('job', job);

			formData.append('parkingName', parkingName);
			formData.append('parkingMessage', parkingMessage);
			formData.append('parkingAddress', parkingAddress);
			formData.append('parkingSpaces', parkingSpaces);
			formData.append('ticketSize', ticketSize);

			// Imagen de perfil (si se cambió)
			const fileInput = document.getElementById('profileImageInput');
			if (fileInput.files.length > 0) {
				formData.append('photo', fileInput.files[0]);
			}

			try {
				const response = await fetch('controladores/update_profile.php', {
					method: 'POST',
					body: formData
				});

				const result = await response.json();
				if (result.success) {
					alert('Perfil y estacionamiento actualizados correctamente');
					if (result.newPhoto) {
						document.getElementById('profileImage').src = result.newPhoto;
						document.getElementById('profileImage').classList.remove('hidden');
						document.getElementById('profileIcon').classList.add('hidden');
					}
				} else {
					alert(result.message || 'Error al actualizar los datos');
				}
			} catch (error) {
				alert('Error de conexión con el servidor');
			}
		});

		async function loadRates(page = 1) {
			try {
				const response = await fetch(`controladores/get_tarifas.php?page=${page}`);
				const result = await response.json();

				const tableBody = document.getElementById('ratesTable');
				const mobileContainer = document.getElementById('ratesMobile');
				const pagination = document.getElementById('ratesPagination');

				tableBody.innerHTML = '';
				mobileContainer.innerHTML = '';
				pagination.innerHTML = '';

				if (!result.success) {
					tableBody.innerHTML = `<tr><td colspan="4" class="p-3 text-center text-red-500">${result.message}</td></tr>`;
					mobileContainer.innerHTML = `<div class="p-3 text-center text-red-500">${result.message}</div>`;
					return;
				}

				if (result.total === 0) {
					tableBody.innerHTML = `<tr><td colspan="4" class="p-3 text-center text-gray-500">No se ha agregado ninguna tarifa</td></tr>`;
					mobileContainer.innerHTML = `<div class="p-3 text-center text-gray-500">No se ha agregado ninguna tarifa</div>`;
					return;
				}

				result.tarifas.forEach(t => {
					const rateId = t.id;

					// === Vista escritorio ===
					const row = `
						<tr class="border-b border-gray-100 dark:border-gray-700">
							<td class="p-3">${t.tipo_vehiculo}</td>
							<td class="p-3">$${parseFloat(t.precio_hora).toFixed(2)}</td>
							<td class="p-3">$${parseFloat(t.precio_minimo).toFixed(2)}</td>
							<td class="p-3">
								<button class="text-primary-500 hover:text-primary-600 mr-2" 
									onclick="editRate('${t.tipo_vehiculo}', '${t.precio_hora}', '${t.precio_minimo}', ${rateId})">Editar</button>
								<button class="text-red-500 hover:text-red-600" 
									onclick="deleteRate(${rateId})">Eliminar</button>
							</td>
						</tr>
					`;
					tableBody.insertAdjacentHTML('beforeend', row);

					// === Vista móvil ===
					const card = `
						<div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
							<div class="space-y-2">
								<div class="flex justify-between">
									<span class="font-medium">Tipo de Vehículo:</span>
									<span>${t.tipo_vehiculo}</span>
								</div>
								<div class="flex justify-between">
									<span class="font-medium">Precio por Hora:</span>
									<span>$${parseFloat(t.precio_hora).toFixed(2)}</span>
								</div>
								<div class="flex justify-between">
									<span class="font-medium">Precio Mínimo:</span>
									<span>$${parseFloat(t.precio_minimo).toFixed(2)}</span>
								</div>
								<div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
									<button class="text-primary-500 hover:text-primary-600 mr-2" 
										onclick="editRate('${t.tipo_vehiculo}', '${t.precio_hora}', '${t.precio_minimo}', ${rateId})">Editar</button>
									<button class="text-red-500 hover:text-red-600" 
										onclick="deleteRate(${rateId})">Eliminar</button>
								</div>
							</div>
						</div>
					`;
					mobileContainer.insertAdjacentHTML('beforeend', card);
				});

				// === Paginación ===
				for (let i = 1; i <= result.pages; i++) {
					const btn = document.createElement('button');
					btn.textContent = i;
					btn.className = `px-3 py-1 rounded ${i === result.page ? 'bg-primary-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'}`;
					btn.addEventListener('click', () => loadRates(i));
					pagination.appendChild(btn);
				}
			} catch (err) {
				tableBody.innerHTML = `<tr><td colspan="4" class="p-3 text-center text-red-500">Error al cargar tarifas</td></tr>`;
				mobileContainer.innerHTML = `<div class="p-3 text-center text-red-500">Error al cargar tarifas</div>`;
			}
		}

		// Cargar tarifas al entrar
		document.addEventListener('DOMContentLoaded', () => {
			loadRates();
		});

		
		function logout() {
			fetch('controladores/logout.php', {
				method: 'POST'
			})
			.then(() => {
				// Redirigir al inicio
				window.location.href = 'inicio.php';
			})
			.catch(err => {
				alert('Error al cerrar sesión');
				console.error(err);
			});
		}
		
		// ==========================
		// Manejo de REGISTROS
		// ==========================
		

		async function loadRegistros(page = 1, search = '') {
			currentRecordPage = page;
			try {
				const res = await fetch(`controladores/get_registros.php?page=${page}&search=${encodeURIComponent(search)}`);
				const data = await res.json();

				if (!data.success) {
					alert(data.message || 'Error al cargar registros');
					return;
				}

				// Tabla Desktop
				const tbody = document.getElementById('registrosBody');
				tbody.innerHTML = '';
				if (data.data.length === 0) {
					tbody.innerHTML = `<tr><td colspan="6" class="p-3 text-center text-gray-500">No hay registros</td></tr>`;
				} else {
					data.data.forEach(r => {
						tbody.innerHTML += `
							<tr class="border-b border-gray-100 dark:border-gray-700">
								<td class="p-3">${r.placa}</td>
								<td class="p-3">${r.modelo_color}</td>
								<td class="p-3">${r.asignacion || '-'}</td>
								<td class="p-3">${r.hora_entrada}</td>
								<td class="p-3">
									<span class="px-2 py-1 rounded-full text-sm ${r.estado === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800'}">
										${r.estado}
									</span>
								</td>
								<td class="p-3 flex flex-wrap gap-2">
									<button class="text-primary-500 hover:text-primary-600 flex items-center gap-1" 
										onclick="openUpdateModal('${r.barcode}')">
										<i class="fas fa-edit"></i> Actualizar
									</button>

									<button class="text-blue-500 hover:text-blue-600 flex items-center gap-1" 
										onclick="showTicketByBarcode('${r.barcode}')">
										<i class="fas fa-ticket-alt"></i> Ver Ticket
									</button>
									
									<button class="text-blue-500 hover:text-blue-600 flex items-center gap-1" 
										onclick="showTicketSalida('${r.barcode}')">
										<i class="fas fa-door-open"></i> Ver Salida
									</button>
								</td>
							</tr>
						`;
					});
				}

				// Vista Móvil
				const mobileContainer = document.getElementById('registrosMobile');
				mobileContainer.innerHTML = '';
				if (data.data.length === 0) {
					mobileContainer.innerHTML = `<div class="p-3 text-center text-gray-500">No hay registros</div>`;
				} else {
					data.data.forEach(r => {
						mobileContainer.innerHTML += `
							<div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
								<div class="space-y-2">
									<div class="flex justify-between"><span class="font-medium">Placa:</span><span>${r.placa}</span></div>
									<div class="flex justify-between"><span class="font-medium">Modelo:</span><span>${r.modelo_color}</span></div>
									<div class="flex justify-between"><span class="font-medium">Asignación:</span><span>${r.asignacion || '-'}</span></div>
									<div class="flex justify-between"><span class="font-medium">Entrada:</span><span>${r.hora_entrada}</span></div>
									<div class="flex justify-between"><span class="font-medium">Estado:</span>
										<span class="px-2 py-1 rounded-full text-sm ${r.estado === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800'}">
											${r.estado}
										</span>
									</div>
									<div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
										<button class="text-primary-500 hover:text-primary-600 mr-2" 
											onclick="openUpdateModal('${r.barcode}')">
											Actualizar
										</button>

										<button class="text-blue-500 hover:text-blue-600" 
											onclick="showTicketByBarcode('${r.barcode}')">
											Ver Ticket
										</button>
									</div>
								</div>
							</div>
						`;
					});
				}

				// Paginación
				const pagination = document.getElementById('paginationRegistros');
				pagination.innerHTML = '';
				for (let i = 1; i <= data.totalPages; i++) {
					pagination.innerHTML += `
						<button onclick="loadRegistros(${i}, document.getElementById('searchRegistros').value)" 
							class="px-3 py-1 border rounded ${i === data.currentPage ? 'bg-primary-500 text-white' : 'border-gray-300 dark:border-gray-600'}">
							${i}
						</button>
					`;
				}

			} catch (err) {
				alert('Error de conexión con el servidor');
			}
		}


		// Buscador en vivo
		document.addEventListener('DOMContentLoaded', () => {
			const searchInput = document.getElementById('searchRegistros');
			if (searchInput) {
				searchInput.addEventListener('input', () => {
					loadRegistros(1, searchInput.value.trim());
				});
			}
			// Cargar registros al inicio
			loadRegistros();
		});
		// ==========================
		// Cargar tarifas en el modal
		// ==========================
		async function loadTarifas() {
			try {
				const res = await fetch('controladores/tarifas_lista.php');
				const data = await res.json();

				const select = document.getElementById('vehicleType');
				select.innerHTML = ''; // limpiar opciones

				if (!data.success || !data.data || data.data.length === 0) {
					select.innerHTML = `<option value="">No hay tarifas registradas</option>`;
					return;
				}

				data.data.forEach(t => {
					const option = document.createElement('option');
					option.value = t.id; // guardas el id de la tarifa
					option.textContent = `${t.tipo_vehiculo} - $${t.precio_hora}/hr`;
					select.appendChild(option);
				});
			} catch (err) {
				console.error(err);
				alert("Error al cargar tarifas");
			}
		}

		


    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9853d5c7e3d93c4e',t:'MTc1ODkwMDk2Ny4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
