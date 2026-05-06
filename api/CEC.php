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
        body, html {
            margin: 0; padding: 0;
            height: 100%; width: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5; /* Fondo gris muy claro para contraste */
        }

        /* Contenedor Principal */
        .main-layout {
            display: grid;
            grid-template-areas: 
                "header header"
                "sidebar content";
            grid-template-rows: 60px 1fr;
            grid-template-columns: 60px 1fr; /* El sidebar empieza midiendo solo 60px */
            height: 100vh;
            transition: grid-template-columns 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* EFECTO HOVER: Cuando pasas el ratón por el layout, el sidebar se expande */
        .main-layout:hover {
            grid-template-columns: 260px 1fr;
        }

        iframe {
            border: none;
            width: 100%;
            height: 100%;
            background: #fff;
        }

        /* Estilos del Header (Blanco) */
        #frame-header {
            grid-area: header;
            z-index: 100;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        /* Estilos del Sidebar (Blanco y dinámico) */
        #frame-sidebar {
            grid-area: sidebar;
            z-index: 90;
            border-right: 1px solid #e0e0e0;
            transition: all 0.4s;
            /* Ocultamos el scroll del sidebar mientras está cerrado */
            overflow: hidden;
        }

        /* Estilos del Contenido Principal */
        #frame-content {
            grid-area: content;
            background-color: #ffffff;
        }

        /* Decoración de la zona de "agarre" para que el usuario sepa que hay algo ahí */
        .sidebar-hint {
            position: absolute;
            left: 0;
            top: 60px;
            bottom: 0;
            width: 5px;
            background: #d32f2f;
            z-index: 101;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <!-- Línea roja fina que indica que hay un menú lateral interactivo -->
    <div class="sidebar-hint"></div>

    <div class="main-layout">
        <!-- Header -->
        <iframe id="frame-header" src="header/<?php echo $rolMin; ?>/prin.php" name="header"></iframe>

        <!-- Sidebar (Se expande al pasar el ratón por encima del layout) -->
        <iframe id="frame-sidebar" src="sidebar/<?php echo $rolMin; ?>/prin.php" name="sidebar"></iframe>

        <!-- Contenido -->
        <iframe id="frame-content" src="content/<?php echo $rolMin; ?>/prin.php" name="content"></iframe>
    </div>

</body>
</html>
