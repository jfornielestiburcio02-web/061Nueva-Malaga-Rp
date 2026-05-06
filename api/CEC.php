<?php
// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. VALIDACIÓN DE SESIÓN (COOKIE)
if (!isset($_COOKIE['auth_061_token'])) {
    header("Location: /modulo_acceso/");
    exit();
}

$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);
$rolSolicitado = $_GET['modulo'] ?? ''; 

// 2. VALIDACIÓN DE ROL (SEGURIDAD PRIVADA)
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

if (!$tienePermiso) {
    header("Location: /modulo_acceso/controlador.061?error=permiso_denegado");
    exit();
}

$rolMin = strtolower($rolSolicitado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Operativo - 061 Málaga</title>
    <style>
        /* RESET ESTILO SEGÚN TU REFERENCIA */
        body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, form, fieldset, input, textarea, p, blockquote, th, td {
            margin: 0;
            padding: 0;
        }
        ol, ul { list-style: none outside none; }
        
        body, html {
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: Verdana, Arial, Helvetica, sans-serif;
            background-color: #ffffff;
        }

        /* HEADER FIJO ABAJO DEL TODO (Z-INDEX ALTO) */
        #frame-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #C5E1D1; /* Color de tu referencia */
            border-top: 0 none;
        }

        /* CONTENEDOR DEL CONTENIDO (SE AJUSTA AL MARGEN IZQUIERDO DEL SIDEBAR CERRADO) */
        #frame-content {
            position: absolute;
            top: 60px;
            left: 50px; /* Ancho del sidebar cuando está cerrado */
            right: 0;
            bottom: 0;
            width: calc(100% - 50px);
            height: calc(100% - 60px);
            border: 0 none;
            z-index: 1;
            transition: left 0.3s ease, width 0.3s ease;
        }

        /* SIDEBAR QUE SE ABRE AL PASAR EL RATÓN */
        #contenedor-sidebar {
            position: absolute;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 50px; /* Tamaño cerrado */
            z-index: 50;
            background-color: #ffffff;
            border-right: 1px solid #C5E1D1;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        /* Cuando el ratón entra en el contenedor del sidebar */
        #contenedor-sidebar:hover {
            width: 200px; /* Tamaño abierto */
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
        }

        /* Ajustamos el iframe dentro del contenedor para que no se mueva raro */
        #frame-sidebar {
            width: 200px; /* El iframe interno siempre mide lo mismo para no romper el layout */
            height: 100%;
            border: 0 none;
        }

        /* OPCIONAL: Si quieres que el contenido se desplace al abrir el sidebar, descomenta esto:
        #contenedor-sidebar:hover ~ #frame-content {
            left: 200px;
            width: calc(100% - 200px);
        }
        */

    </style>
</head>
<body>

    <!-- Header Superior -->
    <iframe id="frame-header" src="header/<?php echo $rolMin; ?>/prin.php" name="header"></iframe>

    <!-- Sidebar dinámico (Contenedor controla el hover) -->
    <div id="contenedor-sidebar">
        <iframe id="frame-sidebar" src="sidebar/<?php echo $rolMin; ?>/prin.php" name="sidebar"></iframe>
    </div>

    <!-- Contenido Principal -->
    <iframe id="frame-content" src="content/<?php echo $rolMin; ?>/prin.php" name="content"></iframe>

</body>
</html>
