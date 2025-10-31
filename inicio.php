<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Internet - Iniciar Sesión</title>
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
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300 min-h-screen">
    
    <!-- Toggle de tema en la esquina superior derecha -->
    <div class="absolute top-4 right-4 z-10">
        <button id="themeToggle" class="p-3 rounded-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm hover:bg-white dark:hover:bg-gray-700 transition-all duration-300 shadow-lg">
            <i class="fas fa-moon dark:hidden text-gray-600"></i>
            <i class="fas fa-sun hidden dark:inline text-yellow-400"></i>
        </button>
    </div>

    <!-- Pantalla de Login -->
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-3xl shadow-2xl p-8 w-full max-w-md border border-white/20 dark:border-gray-700/50">
            
            <!-- Logo con rueda animada -->
			<div class="text-center mb-8">
				<div class="w-24 h-24 mx-auto mb-6 rounded-full flex items-center justify-center overflow-hidden wheel-container relative cursor-pointer">
					<!-- Imagen principal del logo -->
					<img src="media/parkinginternet.png" alt="Logo" class="w-full h-full object-cover rounded-full transition-all duration-500">

					<!-- Llanta completa (oculta por defecto) -->
					<div class="absolute inset-0 tire-assembly opacity-0 transition-all duration-500">
						<!-- Neumático exterior (goma negra) -->
						<div class="absolute inset-0 bg-gray-900 rounded-full tire-rubber shadow-2xl">
							<!-- Textura del neumático -->
							<div class="absolute inset-1 border-2 border-gray-700 rounded-full"></div>
							<div class="absolute inset-0.5 border border-gray-800 rounded-full"></div>
							<!-- Surcos del neumático -->
							<div class="tire-treads absolute inset-0">
								<div class="absolute w-full h-0.5 bg-gray-700 top-4 left-0"></div>
								<div class="absolute w-full h-0.5 bg-gray-700 bottom-4 left-0"></div>
								<div class="absolute h-full w-0.5 bg-gray-700 left-4 top-0"></div>
								<div class="absolute h-full w-0.5 bg-gray-700 right-4 top-0"></div>
							</div>
						</div>

						<!-- Rin metálico -->
						<div class="absolute inset-4 bg-gradient-to-br from-gray-200 via-gray-300 to-gray-500 rounded-full wheel-rim flex items-center justify-center shadow-inner">
							<!-- Rayos del rin -->
							<div class="wheel-spokes absolute inset-0">
								<div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2"></div>
								<div class="absolute h-full w-0.5 bg-gray-600 left-1/2 top-0 transform -translate-x-1/2"></div>
								<div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2 rotate-45 origin-center"></div>
								<div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2 -rotate-45 origin-center"></div>
								<div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2 rotate-22.5 origin-center"></div>
								<div class="absolute w-full h-0.5 bg-gray-600 top-1/2 left-0 transform -translate-y-1/2 -rotate-22.5 origin-center"></div>
							</div>

							<!-- Centro del rin con imagen más grande -->
							<div class="absolute w-8 h-8 rounded-full flex items-center justify-center z-10 overflow-hidden top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 shadow-lg">
								<img src="media/parkinginternet.png" alt="Logo" class="w-full h-full object-cover">
							</div>

							<!-- Efectos de brillo metálico -->
							<div class="absolute inset-0.5 border border-gray-400 rounded-full rim-highlight"></div>
						</div>
					</div>
				</div>

				<h1 class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-3 tracking-tight">Parking Internet</h1>
				<p class="text-gray-600 dark:text-gray-400 text-lg">Sistema de Gestión de Estacionamiento</p>
				<div class="w-20 h-1 bg-gradient-to-r from-primary-500 to-primary-600 mx-auto mt-4 rounded-full"></div>
			</div>


            <!-- Formulario de Login -->
			<form id="loginForm" method="post" class="space-y-6">
				<!-- Mensaje de error -->
				<div id="loginError" class="hidden bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl text-sm backdrop-blur-sm">
					<div class="flex items-center">
						<i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
						<span id="loginErrorMessage">Usuario o contraseña incorrectos</span>
					</div>
				</div>

				<!-- Campo Usuario -->
				<div class="space-y-2">
					<label for="username" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
						<i class="fas fa-user mr-2 text-primary-500"></i>Usuario
					</label>
					<input type="text" id="username" name="username" required
						   class="w-full px-4 py-4 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-sm text-gray-900 dark:text-gray-100 transition-all duration-300 placeholder-gray-400"
						   placeholder="Ingrese su usuario">
				</div>

				<!-- Campo Contraseña -->
				<div class="space-y-2">
					<label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
						<i class="fas fa-lock mr-2 text-primary-500"></i>Contraseña
					</label>
					<div class="relative">
						<input type="password" id="password" name="password" required
							   class="w-full px-4 py-4 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-sm text-gray-900 dark:text-gray-100 transition-all duration-300 placeholder-gray-400 pr-12"
							   placeholder="Ingrese su contraseña">
						<button type="button" id="togglePassword" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
							<i class="fas fa-eye" id="eyeIcon"></i>
						</button>
					</div>
				</div>

				<!-- Opciones adicionales -->
				<div class="flex items-center justify-between">
					<label class="flex items-center cursor-pointer">
						<input type="checkbox" id="rememberMe" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-4 h-4">
						<span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Recordarme</span>
					</label>
				</div>

				<!-- Botón de Login -->
				<button type="submit" id="loginBtn" 
						class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg">
					<span id="loginBtnText" class="flex items-center justify-center">
						<i class="fas fa-sign-in-alt mr-2"></i>
						Iniciar Sesión
					</span>
					<i id="loginSpinner" class="fas fa-spinner fa-spin hidden"></i>
				</button>
			</form>

            

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    © 2025 Parking Internet. Todos los derechos reservados.
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    v1.0.0 | Contrata tecnología en www.hackersinternet.mx
                </p>
            </div>
        </div>
    </div>

    <style>
        body {
            box-sizing: border-box;
        }
        
        /* Animaciones de rueda realista */
        .wheel-container {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
            transform: scale(1.3) rotate(5deg);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .wheel-container:hover .wheel-spokes {
            animation: spin 0.8s linear infinite;
        }
        
        .wheel-container:hover .tire-treads {
            animation: spin 0.8s linear infinite;
        }
        
        .wheel-container:hover .tire-rubber {
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.7);
        }
        
        .wheel-container:hover .wheel-rim {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0, #94a3b8);
            box-shadow: inset 0 4px 8px rgba(255, 255, 255, 0.5), inset 0 -4px 8px rgba(0, 0, 0, 0.4);
        }
        
        .wheel-container:hover .rim-highlight {
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
        }
        
        .wheel-container:active {
            transform: scale(1.2) rotate(2deg);
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Efectos de profundidad del neumático */
        .tire-rubber {
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.5);
            transition: all 0.5s ease;
        }
        
        .wheel-rim {
            transition: all 0.5s ease;
            box-shadow: inset 0 3px 6px rgba(255, 255, 255, 0.4), inset 0 -3px 6px rgba(0, 0, 0, 0.4);
        }
        
        .wheel-spokes,
        .tire-treads {
            transition: all 0.5s ease;
        }
        
        .placeholder-image,
        .placeholder-fallback,
        .tire-assembly {
            transition: opacity 0.5s ease;
        }
        
        /* Efectos de glassmorphism */
        .backdrop-blur-sm {
            backdrop-filter: blur(8px);
        }
        
        /* Animaciones de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Efectos de hover en inputs */
        input:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
        }
        
        /* Efectos de hover en botones */
        button:hover {
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.25);
        }
        
        /* Gradiente animado de fondo */
        body {
            background-attachment: fixed;
        }
    </style>

    <script>
        // Toggle tema oscuro/claro
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            
            // Efecto de rotación en el botón
            themeToggle.style.transform = 'rotate(360deg)';
            setTimeout(() => {
                themeToggle.style.transform = 'rotate(0deg)';
            }, 300);
        });
        
        // Cargar tema guardado
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            html.classList.add('dark');
        } else if (savedTheme === 'light') {
            html.classList.remove('dark');
        } else {
            // Detectar preferencia del sistema
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                html.classList.add('dark');
            }
        }
        
        
        
        // Toggle mostrar/ocultar contraseña
        document.getElementById('togglePassword').addEventListener('click', () => {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
        
       
        
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', () => {
            const loginContainer = document.querySelector('.min-h-screen > div');
            loginContainer.classList.add('animate-fade-in-up');
        });
        
        // Efecto de shake para errores
        const shakeKeyframes = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `;
        
        // === Auto-focus en usuario ===
		document.getElementById('username').focus();

		// === Keyframes para shake (si ya lo tienes definido) ===
		const style = document.createElement('style');
		style.textContent = shakeKeyframes; // tu variable shakeKeyframes
		document.head.appendChild(style);

		// === Permitir enviar login con Enter ===
		document.getElementById('loginForm').addEventListener('keydown', (e) => {
			if (e.key === 'Enter') {
				e.preventDefault(); // evita el comportamiento por defecto de Enter
				document.getElementById('loginForm').requestSubmit(); // submit nativo
			}
		});

		// === Toggle contraseña (opcional) ===
		document.getElementById('togglePassword').addEventListener('click', () => {
			const passwordInput = document.getElementById('password');
			const eyeIcon = document.getElementById('eyeIcon');
			if (passwordInput.type === 'password') {
				passwordInput.type = 'text';
				eyeIcon.classList.remove('fa-eye');
				eyeIcon.classList.add('fa-eye-slash');
			} else {
				passwordInput.type = 'password';
				eyeIcon.classList.remove('fa-eye-slash');
				eyeIcon.classList.add('fa-eye');
			}
		});

		// === Submit del login ===
		document.getElementById('loginForm').addEventListener('submit', async (e) => {
			e.preventDefault();

			const username = document.getElementById('username').value.trim();
			const password = document.getElementById('password').value.trim();
			const rememberMe = document.getElementById('rememberMe').checked ? 'true' : 'false';

			const loginBtn = document.getElementById('loginBtn');
			const loginSpinner = document.getElementById('loginSpinner');
			const loginBtnText = document.getElementById('loginBtnText');
			const errorBox = document.getElementById('loginError');
			const errorMessage = document.getElementById('loginErrorMessage');

			// UI loading
			loginBtn.disabled = true;
			loginSpinner.classList.remove('hidden');
			loginBtnText.classList.add('hidden');
			errorBox.classList.add('hidden');

			try {
				const response = await fetch('controladores/login.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: new URLSearchParams({ username, password, rememberMe })
				});

				const result = await response.json();

				if (result.success) {
					window.location.href = 'index.php';
				} else {
					errorMessage.textContent = result.message || 'Usuario o contraseña incorrectos';
					errorBox.classList.remove('hidden');

					// animación shake
					const form = document.getElementById('loginForm');
					form.style.animation = 'shake 0.4s';
					form.addEventListener('animationend', () => form.style.animation = '', { once: true });
				}
			} catch (err) {
				errorMessage.textContent = 'Error de conexión con el servidor.';
				errorBox.classList.remove('hidden');
			} finally {
				loginBtn.disabled = false;
				loginSpinner.classList.add('hidden');
				loginBtnText.classList.remove('hidden');
			}
		});


    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9853d9adb0133c4e',t:'MTc1ODkwMTEyNy4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
