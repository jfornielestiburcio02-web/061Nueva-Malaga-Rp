<?php
// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. CAPTURAR EL ROL (Lo enviamos desde el selector)
// Si no hay rol, volvemos al selector para que elijan uno
$rolSolicitado = $_POST['set_modulo'] ?? ''; 

if (empty($rolSolicitado)) {
    header("Location: /modulo_acceso/controlador.061");
    exit();
}

// 2. VALIDACIÓN DE SEGURIDAD (Copiado de tu código que sí funciona)
if (!isset($_COOKIE['auth_061_token'])) {
    header("Location: /modulo_acceso/");
    exit();
}

$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);

// 3. CONSULTA A FIRESTORE PARA VERIFICAR PERMISOS
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

// Si no tiene el rol que dice tener, fuera
if (!$tienePermiso) {
    header("Location: /modulo_acceso/controlador.061?error=no_access");
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
        /* Estilos de estructura limpia */
        body, html {
            margin: 0; padding: 0;
            height: 100%; width: 100%;
            overflow: hidden;
            font-family: Verdana, sans-serif;
            background-color: #fff;
        }

        /* Cabecera */
        #frame-header {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 60px;
            z-index: 100;
            border-bottom: 1px solid #C5E1D1;
        }

        /* Sidebar con efecto Hover */
        #contenedor-sidebar {
            position: absolute;
            top: 60px; left: 0; bottom: 0;
            width: 50px; /* Cerrado */
            z-index: 50;
            background: #fff;
            border-right: 1px solid #C5E1D1;
            transition: width 0.3s ease;
            overflow: hidden;
        }

        #contenedor-sidebar:hover {
            width: 200px; /* Abierto */
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
        }

        #frame-sidebar {
            width: 200px; height: 100%; border: 0;
        }

        /* Contenido */
        #frame-content {
            position: absolute;
            top: 60px; left: 50px;
            right: 0; bottom: 0;
            width: calc(100% - 50px);
            height: calc(100% - 60px);
            border: 0;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <iframe id="frame-header" src="header/<?php echo $rolMin; ?>/prin.php" name="header"></iframe>

    <!-- Sidebar -->
    <div id="contenedor-sidebar">
        <iframe id="frame-sidebar" src="sidebar/<?php echo $rolMin; ?>/prin.php" name="sidebar"></iframe>
    </div>

    <!-- Contenido -->
    <iframe id="frame-content" src="content/<?php echo $rolMin; ?>/prin.php" name="content"></iframe>

</body>
</html>
