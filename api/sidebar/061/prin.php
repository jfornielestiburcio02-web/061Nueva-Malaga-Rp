<?php
// 1. CONFIGURACIÓN DE SESIÓN (Sin espacios antes de esto)
ini_set('session.cookie_path', '/');
session_start();

// 2. PARÁMETROS FIREBASE
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 3. CAPTURA DE DATOS
$token = $_COOKIE['auth_061_token'] ?? '';
$rolActivo = $_SESSION['modulo_activo'] ?? '061'; 

// Si no hay token, fuera
if (empty($token)) {
    exit("<div style='color:red; font-family:sans-serif; padding:10px;'>No tiene permiso (Falta Token)</div>");
}

$usuarioDoc = base64_decode($token);

// 4. VALIDACIÓN REAL EN FIREBASE
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$accesoOk = false;
if ($httpCode === 200) {
    $data = json_decode($response, true);
    $roles = $data['fields']['roles']['arrayValue']['values'] ?? [];
    foreach ($roles as $r) {
        if (($r['stringValue'] ?? '') === $rolActivo) {
            $accesoOk = true;
            break;
        }
    }
}

// 5. BLOQUEO SI NO HAY PERMISO
if (!$accesoOk) {
    exit("<div style='color:red; font-family:sans-serif; padding:10px;'>No tiene permiso</div>");
}

$rolMin = strtolower($rolActivo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sidebar</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f7f6; font-family: sans-serif; display: flex; height: 100vh; overflow: hidden; }
        
        /* BARRA DE ICONOS */
        .icon-bar { 
            width: 50px; background: #fff; border-right: 1px solid #C5E1D1; 
            display: flex; flex-direction: column; align-items: center; padding-top: 15px; z-index: 10;
        }
        .menu-btn { 
            width: 36px; height: 36px; margin-bottom: 12px; cursor: pointer; 
            border: 1px solid #C5E1D1; border-radius: 6px; display: flex; 
            justify-content: center; align-items: center; transition: 0.2s; 
        }
        .menu-btn:hover { background: #e8f5e9; }
        .img-placeholder { width: 20px; height: 20px; border: 2px solid #2e7d32; border-radius: 3px; }

        /* PANEL SUBMENÚ */
        .submenu { 
            position: absolute; left: 50px; top: 0; bottom: 0; width: 180px; 
            background: #fff; border-right: 1px solid #C5E1D1; display: none; 
            padding: 20px 15px; z-index: 5; box-shadow: 2px 0 8px rgba(0,0,0,0.05); 
        }
        .submenu.active { display: block; }
        .title { font-size: 11px; font-weight: bold; color: #d32f2f; text-transform: uppercase; border-bottom: 2px solid #f0f0f0; padding-bottom: 8px; margin-bottom: 12px; }
        .link { display: block; font-size: 13px; color: #444; text-decoration: none; padding: 10px 5px; border-radius: 4px; }
        .link:hover { background: #f5f5f5; color: #000; }
    </style>
</head>
<body>

    <div class="icon-bar">
        <div class="menu-btn" onclick="openMenu('nav-ops')"><div class="img-placeholder"></div></div>
        <div class="menu-btn" onclick="openMenu('nav-user')"><div class="img-placeholder"></div></div>
    </div>

    <div id="nav-ops" class="submenu">
        <div class="title">Operaciones <?php echo $rolActivo; ?></div>
        <a href="/content/<?php echo $rolMin; ?>/inicio.php" target="content" class="link">Escritorio</a>
        <a href="/content/<?php echo $rolMin; ?>/listado.php" target="content" class="link">Listado Datos</a>
    </div>

    <div id="nav-user" class="submenu">
        <div class="title">Configuración</div>
        <a href="/content/<?php echo $rolMin; ?>/perfil.php" target="content" class="link">Mi Cuenta</a>
        <a href="/modulo_acceso/logout.php" class="link" style="color:#d32f2f;">Salir</a>
    </div>

    <script>
        function openMenu(id) {
            const menu = document.getElementById(id);
            const wasActive = menu.classList.contains('active');
            document.querySelectorAll('.submenu').forEach(el => el.classList.remove('active'));
            if (!wasActive) menu.classList.add('active');
        }

        // Cierra si el usuario pulsa en el cuerpo del documento
        window.onclick = function(e) {
            if (!e.target.closest('.icon-bar') && !e.target.closest('.submenu')) {
                document.querySelectorAll('.submenu').forEach(el => el.classList.remove('active'));
            }
        }
    </script>
</body>
</html>
