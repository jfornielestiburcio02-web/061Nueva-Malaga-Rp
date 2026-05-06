<?php
session_start(); // Iniciamos sesión para leer el rol guardado

// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. VALIDACIÓN DE SESIÓN (COOKIE Y ROL EN SESIÓN)
// Si no hay cookie o no se ha seleccionado un módulo en el paso anterior, fuera.
if (!isset($_COOKIE['auth_061_token']) || !isset($_SESSION['modulo_activo'])) {
    header("Location: /login.php");
    exit();
}

$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);
$rolSolicitado = $_SESSION['modulo_activo']; // Recuperamos el rol de la sesión privada

// 2. VALIDACIÓN DE ROL CONTRA FIREBASE (SEGURIDAD)
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tienePermiso = false;
if ($httpCode == 200) {
    $data = json_decode($response, true);
    $rolesArray = $data['fields']['roles']['arrayValue']['values'] ?? [];
    foreach ($rolesArray as $item) {
        if (isset($item['stringValue']) && $item['stringValue'] === $rolSolicitado) {
            $tienePermiso = true;
            break;
        }
    }
}

// Si intenta entrar a un rol que no tiene asignado en Firebase
if (!$tienePermiso) {
    header("Location: /modulo_acceso/controlador.061?error=permiso_denegado");
    exit();
}

// Pasamos el rol a minúsculas para las rutas de las carpetas
$rolMin = strtolower($rolSolicitado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Operativo - 061 Málaga</title>
    <style>
        /* RESET DE ESTILOS PROFESIONAL[cite: 1] */
        body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, form, fieldset, input, textarea, p, blockquote, th, td {
            margin: 0;
            padding: 0;
        }
        table { border-collapse: collapse; border-spacing: 0; }
        fieldset, img { border: 0 none; }
        ol, ul { list-style: none outside none; }

        body, html {
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: Verdana, Arial, Helvetica, sans-serif;
            background-color: #ffffff;
        }

        /* HEADER SUPERIOR FIJO */
        #frame-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #C5E1D1; /* Color corporativo de referencia[cite: 1] */
            border-top: 0 none;
        }

        /* CONTENEDOR DEL SIDEBAR (MANEJA EL HOVER) */
        #contenedor-sidebar {
            position: absolute;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 50px; /* Ancho cerrado (solo se ve una pequeña parte) */
            z-index: 50;
            background-color: #ffffff;
            border-right: 1px solid #C5E1D1;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        /* Efecto al pasar el ratón: se expande el contenedor */
        #contenedor-sidebar:hover {
            width: 200px; /* Ancho abierto */
            box-shadow: 5px 0 15px rgba(0,0,0,0.05);
        }

        /* Iframe del Sidebar: siempre tiene el ancho máximo para no deformar su contenido */
        #frame-sidebar {
            width: 200px; 
            height: 100%;
            border: 0 none;
        }

        /* IFRAME DE CONTENIDO PRINCIPAL */
        #frame-content {
            position: absolute;
            top: 60px;
            left: 50px; /* Se alinea con el sidebar cerrado */
            right: 0;
            bottom: 0;
            width: calc(100% - 50px);
            height: calc(100% - 60px);
            border: 0 none;
            z-index: 1;
        }
    </style>
</head>
<body>

    <!-- Header: Carga desde la carpeta del rol -->
    <iframe id="frame-header" src="header/<?php echo $rolMin; ?>/prin.php" name="header"></iframe>

    <!-- Sidebar: Se abre/cierra por CSS hover sobre su contenedor -->
    <div id="contenedor-sidebar">
        <iframe id="frame-sidebar" src="sidebar/<?php echo $rolMin; ?>/prin.php" name="sidebar"></iframe>
    </div>

    <!-- Contenido: Aquí se cargan las páginas mediante target="content" -->
    <iframe id="frame-content" src="content/<?php echo $rolMin; ?>/prin.php" name="content"></iframe>

</body>
</html>
