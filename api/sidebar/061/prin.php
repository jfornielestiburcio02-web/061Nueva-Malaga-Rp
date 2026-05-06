<?php
// Forzamos que la sesión sea accesible desde cualquier carpeta del dominio
ini_set('session.cookie_path', '/'); 
session_start();

// 1. CONFIGURACIÓN FIREBASE
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 2. VALIDACIÓN DE IDENTIDAD
// Si no hay cookie o la sesión se perdió, intentamos recuperar el rol de la cookie si fuera necesario
$token = $_COOKIE['auth_061_token'] ?? '';
$rolActivo = $_SESSION['modulo_activo'] ?? '';

if (empty($token) || empty($rolActivo)) {
    die("<div style='color:red; font-family:sans-serif; padding:10px; font-size:12px;'>No tiene permiso (Sesión expirada)</div>");[cite: 1]
}

$usuarioDoc = base64_decode($token);

// 3. CONSULTA DE SEGURIDAD A FIRESTORE
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
    $rolesArray = $data['fields']['roles']['arrayValue']['values'] ?? [];
    foreach ($rolesArray as $item) {
        if (isset($item['stringValue']) && $item['stringValue'] === $rolActivo) {
            $accesoConfirmado = true;
            break;
        }
    }
}

if (!$accesoConfirmado) {
    die("<div style='color:red; font-family:sans-serif; padding:10px; font-size:12px;'>No tiene permiso (Rol no válido)</div>");[cite: 1]
}

$rolMin = strtolower($rolActivo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { margin: 0; padding: 0; background-color: #f4f7f6; font-family: sans-serif; height: 100vh; display: flex; overflow: hidden; }
        
        /* BARRA ICONOS */
        .icon-bar {
            width: 50px; background: #fff; border-right: 1px solid #C5E1D1;
            display: flex; flex-direction: column; align-items: center; padding-top: 10px; z-index: 10;
        }
        .menu-item {
            width: 38px; height: 38px; margin-bottom: 8px; cursor: pointer;
            display: flex; justify-content: center; align-items: center;
            border-radius: 4px; transition: 0.2s;
        }
        .menu-item:hover { background: #e8f5e9; }
        .icon-box { width: 22px; height: 22px; border: 2px solid #66bb6a; border-radius: 3px; }

        /* PANELES SUBMENÚ */
        .submenu {
            position: absolute; left: 50px; top: 0; bottom: 0; width: 180px;
            background: #fff; border-right: 1px solid #C5E1D1;
            display: none; padding: 15px; z-index: 5; box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        .submenu.active { display: block; }
        .title { font-size: 11px; font-weight: bold; color: #2e7d32; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid #eee; }
        .link { display: block; font-size: 13px; color: #444; text-decoration: none; padding: 8px 0; }
        .link:hover { color: #000; font-weight: bold; }
    </style>
</head>
<body>

    <div class="icon-bar">
        <div class="menu-item" onclick="openNav('m1')"><div class="icon-box"></div></div>
        <div class="menu-item" onclick="openNav('m2')"><div class="icon-box"></div></div>
    </div>

    <div id="m1" class="submenu">
        <div class="title">Servicios <?php echo $rolActivo; ?></div>
        <!-- Usamos rutas absolutas para evitar fallos en Vercel -->
        <a href="/content/<?php echo $rolMin; ?>/avisos.php" target="content" class="link">Avisos</a>
        <a href="/content/<?php echo $rolMin; ?>/unidades.php" target="content" class="link">Unidades</a>
    </div>

    <div id="m2" class="submenu">
        <div class="title">Sistema</div>
        <a href="/content/<?php echo $rolMin; ?>/perfil.php" target="content" class="link">Mi Perfil</a>
    </div>

    <script>
        function openNav(id) {
            const current = document.getElementById(id);
            const isOpen = current.style.display === 'block';
            document.querySelectorAll('.submenu').forEach(el => el.style.display = 'none');
            current.style.display = isOpen ? 'none' : 'block';
        }
        
        // Cerrar al hacer clic en el frame de contenido (si es posible detectarlo)
        window.onclick = function(event) {
            if (!event.target.closest('.icon-bar') && !event.target.closest('.submenu')) {
                document.querySelectorAll('.submenu').forEach(el => el.style.display = 'none');
            }
        }
    </script>
</body>
</html>
