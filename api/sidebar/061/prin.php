<?php
session_start();

// 1. CONFIGURACIÓN FIREBASE
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 2. VERIFICACIÓN DE SEGURIDAD (COOKIE + SESIÓN)
if (!isset($_COOKIE['auth_061_token']) || !isset($_SESSION['modulo_activo'])) {
    die("<div style='color:red; font-family:sans-serif; padding:10px; font-size:12px; font-weight:bold;'>No tiene permiso</div>");
}

$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);
$rolSolicitado = $_SESSION['modulo_activo'];

// 3. VALIDACIÓN REAL CONTRA FIREBASE
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$accesoConfirmado = false;
if ($httpCode == 200) {
    $data = json_decode($response, true);
    $rolesEnFirebase = $data['fields']['roles']['arrayValue']['values'] ?? [];
    
    foreach ($rolesEnFirebase as $item) {
        if (isset($item['stringValue']) && $item['stringValue'] === $rolSolicitado) {
            $accesoConfirmado = true;
            break;
        }
    }
}

// Si Firebase dice que no, o la cookie es falsa, cortamos
if (!$accesoConfirmado) {
    die("<div style='color:red; font-family:sans-serif; padding:10px; font-size:12px; font-weight:bold;'>No tiene permiso</div>");
}

// Definimos la ruta de contenido según el rol validado
$rolMin = strtolower($rolSolicitado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0; padding: 0;
            background-color: #f4f7f6;
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Columna de iconos estrecha */
        .icon-bar {
            width: 50px;
            background-color: #ffffff;
            border-right: 1px solid #C5E1D1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 10px;
            z-index: 10;
        }

        .menu-item {
            width: 40px; height: 40px;
            margin-bottom: 10px;
            cursor: pointer;
            display: flex; justify-content: center; align-items: center;
            border-radius: 5px;
            transition: background 0.2s;
        }

        .menu-item:hover { background-color: #e8f5e9; }

        .icon-placeholder {
            width: 24px; height: 24px;
            border: 2px solid #2e7d32;
            border-radius: 4px;
        }

        /* Panel de submenú */
        .submenu-panel {
            position: absolute;
            left: 50px; top: 0; bottom: 0;
            width: 160px;
            background-color: #ffffff;
            border-right: 1px solid #C5E1D1;
            display: none;
            padding: 15px 10px;
            z-index: 5;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }

        .submenu-panel.active { display: block; }

        .submenu-title {
            font-size: 11px; font-weight: bold;
            color: #d32f2f; /* Color Rayuela */
            margin-bottom: 15px;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .submenu-link {
            display: block;
            font-size: 12px; color: #555;
            text-decoration: none;
            padding: 8px 5px;
            border-radius: 3px;
        }

        .submenu-link:hover { background-color: #f0f0f0; color: #000; }
    </style>
</head>
<body>

    <div class="icon-bar">
        <div class="menu-item" onclick="toggleSubmenu('m1')"><div class="icon-placeholder"></div></div>
        <div class="menu-item" onclick="toggleSubmenu('m2')"><div class="icon-placeholder"></div></div>
    </div>

    <!-- Menús dinámicos usando la variable $rolMin -->
    <div id="m1" class="submenu-panel">
        <div class="submenu-title">Operaciones</div>
        <a href="../../content/<?php echo $rolMin; ?>/lista.php" target="content" class="submenu-link">Listado</a>
        <a href="../../content/<?php echo $rolMin; ?>/Mapa.php" target="content" class="submenu-link">Mapa</a>
    </div>

    <div id="m2" class="submenu-panel">
        <div class="submenu-title">Gestión</div>
        <a href="../../content/<?php echo $rolMin; ?>/informes.php" target="content" class="submenu-link">Informes</a>
    </div>

    <script>
        function toggleSubmenu(id) {
            const panels = document.querySelectorAll('.submenu-panel');
            let target = document.getElementById(id);
            let isVisible = target.style.display === 'block';
            
            panels.forEach(p => p.style.display = 'none');
            target.style.display = isVisible ? 'none' : 'block';
        }

        // Cerrar si hacen clic en el área de contenido
        window.onclick = function(event) {
            if (!event.target.closest('.icon-bar') && !event.target.closest('.submenu-panel')) {
                document.querySelectorAll('.submenu-panel').forEach(p => p.style.display = 'none');
            }
        }
    </script>
</body>
</html>
