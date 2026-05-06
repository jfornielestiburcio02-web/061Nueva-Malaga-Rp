<?php
// 1. INICIAR SESIÓN INMEDIATAMENTE (Sin espacios antes del <?php)
session_start(); 

// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 2. COMPROBACIÓN DE SEGURIDAD
// Si te echa al login, es porque o la cookie 'auth_061_token' no existe 
// o la sesión 'modulo_activo' no se guardó en el paso anterior.
if (!isset($_COOKIE['auth_061_token']) || !isset($_SESSION['modulo_activo'])) {
    header("Location: /login.php");
    exit();
}

$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);
$rolSolicitado = $_SESSION['modulo_activo']; 

// 3. VALIDACIÓN DE ROL CONTRA FIREBASE
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

// Si los roles no coinciden, fuera.
if (!$tienePermiso) {
    header("Location: /modulo_acceso/controlador.061?error=sin_permiso");
    exit();
}

$rolMin = strtolower($rolSolicitado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CEC - Panel Operativo</title>
    <style>
        /* RESET TOTAL */
        body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, form, fieldset, input, textarea, p, blockquote, th, td {
            margin: 0; padding: 0;
        }
        body, html {
            height: 100%; width: 100%;
            overflow: hidden;
            font-family: Verdana, Arial, Helvetica, sans-serif;
            background-color: #ffffff;
        }

        /* HEADER FIJO */
        #frame-header {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 60px;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #C5E1D1; /* Color de tu referencia[cite: 1] */
        }

        /* CONTENEDOR SIDEBAR (PARA EL HOVER) */
        #contenedor-sidebar {
            position: absolute;
            top: 60px; left: 0; bottom: 0;
            width: 50px; /* Tamaño cerrado */
            z-index: 50;
            background-color: #ffffff;
            border-right: 1px solid #C5E1D1;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        /* Expansión al pasar el ratón */
        #contenedor-sidebar:hover {
            width: 200px;
            box-shadow: 5px 0 15px rgba(0,0,0,0.05);
        }

        #frame-sidebar {
            width: 200px; 
            height: 100%;
            border: 0 none;
        }

        /* IFRAME DE CONTENIDO */
        #frame-content {
            position: absolute;
            top: 60px;
            left: 50px; /* Alineado con el sidebar cerrado */
            right: 0; bottom: 0;
            width: calc(100% - 50px);
            height: calc(100% - 60px);
            border: 0 none;
            z-index: 1;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <iframe id="frame-header" src="header/<?php echo $rolMin; ?>/prin.php" name="header"></iframe>

    <!-- Sidebar con hover -->
    <div id="contenedor-sidebar">
        <iframe id="frame-sidebar" src="sidebar/<?php echo $rolMin; ?>/prin.php" name="sidebar"></iframe>
    </div>

    <!-- Contenido Principal -->
    <iframe id="frame-content" src="content/<?php echo $rolMin; ?>/prin.php" name="content"></iframe>

</body>
</html>
