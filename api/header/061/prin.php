<?php
// 1. SESIÓN (Sin espacios antes de esto)
ini_set('session.cookie_path', '/');
session_start();[cite: 1]

// 2. PARÁMETROS FIREBASE
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 3. CAPTURA DE DATOS
$token = $_COOKIE['auth_061_token'] ?? '';[cite: 1]
$rolActivo = $_SESSION['modulo_activo'] ?? '061'; 

if (empty($token)) {
    exit("<div style='color:red; font-family:Verdana; padding:10px;'>No tiene permiso (Falta Token)</div>");
}

$usuarioDoc = base64_decode($token);

// 4. VALIDACIÓN Y DATOS EN FIREBASE
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$accesoOk = false;
$nombreUser = "Usuario";
$imgPerfil = "";
$tieneDir = false;

if ($httpCode === 200) {
    $data = json_decode($response, true);
    
    // Extraer datos de perfil
    $nombreUser = $data['fields']['nombreUsuario']['stringValue'] ?? "Usuario";
    $imgPerfil = $data['fields']['imagenPerfil']['stringValue'] ?? "";
    
    // Validar roles
    $roles = $data['fields']['roles']['arrayValue']['values'] ?? [];
    foreach ($roles as $r) {
        $rolVal = $r['stringValue'] ?? '';
        if ($rolVal === $rolActivo) {
            $accesoOk = true;
        }
        if ($rolVal === 'Dir') {
            $tieneDir = true;
        }
    }
}

if (!$accesoOk) {
    exit("<div style='color:red; font-family:Verdana; padding:10px;'>No tiene permiso</div>");[cite: 1]
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
</head>
<body>

    <div id="cabecera">
        <!-- Izquierda: Perfil -->
        <div id="fichaSuperior">
            <?php if ($imgPerfil): ?>
                <img src="<?php echo $imgPerfil; ?>" class="foto" width="45" height="45">
            <?php else: ?>
                <div style="width:45px; height:45px; border-radius:50%; background:#ccc; float:left; margin-right:8px;"></div>
            <?php endif; ?>
            
            <p><?php echo htmlspecialchars($nombreUser); ?></p>
            <p class="textoCentro">Perfil: <strong><?php echo $rolActivo; ?></strong></p>
        </div>

        <!-- Derecha: Cambio de Perfil -->
        <ul id="menuAccDirPer">
            <?php if ($tieneDir && $rolActivo !== 'Dir'): ?>
                <li onclick="cambiarPerfil('Dir')">
                    <div id="iconoAccDir"><div style="width:30px; height:30px; background:#646361; border-radius:4px; margin:auto;"></div></div>
                    <div class="nombreAccDir">DIRECCIÓN</div>
                </li>
            <?php endif; ?>

            <?php if ($rolActivo !== '061'): ?>
                <li onclick="cambiarPerfil('061')">
                    <div id="iconoAccDir"><div style="width:30px; height:30px; background:#297A38; border-radius:4px; margin:auto;"></div></div>
                    <div class="nombreAccDir">CENTRO 061</div>
                </li>
            <?php endif; ?>
            
            <li id="salir" onclick="window.top.location.href='/modulo_acceso/logout.php'">
                <div class="nombreAccDir" style="color:#d32f2f; font-weight:bold;">SALIR</div>
            </li>
        </ul>
    </div>

    <script>
    function cambiarPerfil(nuevoRol) {
        // Redirigir al controlador principal para cambiar la sesión y recargar todo
        window.top.location.href = "/modulo_acceso/CEC.php?set_modulo=" + nuevoRol;[cite: 1]
    }
    </script>
</body>
</html>
