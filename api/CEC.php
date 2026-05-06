
<?php
// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. VALIDACIÓN DE SESIÓN (COOKIE)
if (!isset($_COOKIE['auth_061_token'])) {
    header("Location: /login.php");
    exit();
}

$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);
$rolSolicitado = $_GET['modulo'] ?? ''; // Ej: '061' o 'Dir'

if (empty($rolSolicitado)) {
    die("Error: No se ha especificado un módulo de acceso.");
}

// 2. VALIDACIÓN DE ROL EN TIEMPO REAL (SEGURIDAD)
// Consultamos a Firebase para asegurar que el usuario no está intentando entrar a un rol que no tiene
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

// Si no tiene el rol, lo expulsamos
if (!$tienePermiso) {
    header("Location: /modulo_acceso/controlador.061?error=permiso_denegado");
    exit();
}

// 3. PREPARAR RUTAS (En minúsculas como pediste)
$rolMin = strtolower($rolSolicitado);
$rutaHeader  = "header/{$rolMin}/prin.php";
$rutaSidebar = "sidebar/{$rolMin}/prin.php";
$rutaContent = "content/{$rolMin}/prin.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CEC - Panel Operativo (<?php echo htmlspecialchars($rolSolicitado); ?>)</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden; /* Evita scroll en la ventana principal */
            font-family: Arial, sans-serif;
            background-color: #000;
        }

        /* Contenedor de toda la interfaz */
        .wrapper {
            display: grid;
            grid-template-areas: 
                "header header"
                "sidebar content";
            grid-template-rows: 60px 1fr; /* Header de 60px y el resto para contenido */
            grid-template-columns: 250px 1fr; /* Sidebar de 250px y el resto para contenido */
            height: 100vh;
            width: 100vw;
        }

        iframe {
            border: none;
            width: 100%;
            height: 100%;
        }

        #frame-header {
            grid-area: header;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0,0,0,0.5);
        }

        #frame-sidebar {
            grid-area: sidebar;
            border-right: 1px solid #333;
        }

        #frame-content {
            grid-area: content;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <!-- Iframe Superior -->
        <iframe id="frame-header" src="<?php echo $rutaHeader; ?>" name="header"></iframe>

        <!-- Iframe Lateral -->
        <iframe id="frame-sidebar" src="<?php echo $rutaSidebar; ?>" name="sidebar"></iframe>

        <!-- Iframe Principal -->
        <iframe id="frame-content" src="<?php echo $rutaContent; ?>" name="content"></iframe>
    </div>

</body>
</html>
