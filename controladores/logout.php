<?php
session_start();
session_unset();
session_destroy();

// Borrar cookies
setcookie("usuario_id", "", time() - 3600, "/");
setcookie("usuario", "", time() - 3600, "/");
setcookie("estacionamiento_id", "", time() - 3600, "/"); // <-- falta esto

header("Location: ../inicio.php");
exit;
