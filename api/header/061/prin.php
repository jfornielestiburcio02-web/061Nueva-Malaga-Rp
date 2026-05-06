<?php
// 1. FORZAR CONFIGURACIÓN DE COOKIES ANTES DE NADA
ini_set('session.cookie_path', '/'); 
ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']); 
session_start();[cite: 1]

// 2. CONFIGURACIÓN FIREBASE
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 3. RECUPERAR DATOS (Salvavidas si $_SESSION se borra)
$token = $_COOKIE['auth_061_token'] ?? '';[cite: 1]

if (empty($token)) {
    die("<div style='color:red; font-family:Verdana; padding:10px; font-size:12px;'>Error: No hay Token (Inicie sesión de nuevo)</div>");
}

$usuarioDoc = base64_decode($token);

// Si la sesión se perdió pero hay cookie, forzamos el rol por defecto del directorio actual
if (!isset($_SESSION['modulo_activo'])) {
    // Si este archivo está en /sidebar/061/, asumimos que el rol es 061
    $_SESSION['modulo_activo'] = strpos($_SERVER['REQUEST_URI'], '/061/') !== false ? '061' : 'Dir';
}

$perfilActual = $_SESSION['modulo_activo'];

// 4. CONSULTA A FIREBASE
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("<div style='color:red; font-family:Verdana; padding:10px; font-size:12px;'>Error de Conexión Firebase (DNI: $usuarioDoc)</div>");
}

$data = json_decode($response, true);
$nombreUsuario = $data['fields']['nombreUsuario']['stringValue'] ?? "Usuario";
$imagenPerfil = $data['fields']['imagenPerfil']['stringValue'] ?? "";
$rolesFirebase = $data['fields']['roles']['arrayValue']['values'] ?? [];

// Verificar si tiene el rol activo y si tiene permiso de Dirección
$tienePermisoRolActual = false;
$tieneRolDir = false;

foreach ($rolesFirebase as $r) {
    $val = $r['stringValue'] ?? '';
    if ($val === $perfilActual) $tienePermisoRolActual = true;
    if ($val === 'Dir') $tieneRolDir = true;
}

if (!$tienePermisoRolActual) {
    die("<div style='color:red; font-family:Verdana; padding:10px; font-size:12px;'>No tiene permiso para el perfil: $perfilActual</div>");
}
?>


<LINK
<div id="cabecera">
    <div id="fichaSuperior">
        <?php if ($imagenPerfil): ?>
            <img src="<?php echo $imagenPerfil; ?>" class="foto" width="50" height="50">
        <?php else: ?>
            <div style="width:50px; height:50px; border-radius:50%; background:#ccc; float:left; margin-right:8px;"></div>
        <?php endif; ?>
        
        <p><?php echo htmlspecialchars($nombreUsuario); ?></p>
        <p class="textoCentro">Perfil: <strong><?php echo $perfilActual; ?></strong></p>
    </div>

    <ul id="menuAccDirPer">
        <!-- Solo mostramos botón Dir si tenemos el rol y NO estamos ya en él -->
        <?php if ($tieneRolDir && $perfilActual !== 'Dir'): ?>
            <li onclick="cambiarPerfil('Dir')">
                <div id="iconoAccDir"><div style="width:40px; height:40px; background:#2e7d32; border-radius:4px; margin:auto;"></div></div>
                <div class="nombreAccDir">DIRECCIÓN</div>
            </li>
        <?php endif; ?>

        <!-- Solo mostramos botón 061 si no estamos ya en él -->
        <?php if ($perfilActual !== '061'): ?>
            <li onclick="cambiarPerfil('061')">
                <div id="iconoAccDir"><div style="width:40px; height:40px; background:#d32f2f; border-radius:4px; margin:auto;"></div></div>
                <div class="nombreAccDir">CENTRO 061</div>
            </li>
        <?php endif; ?>
        
        <li id="salir" onclick="window.location.href='/modulo_acceso/logout.php'">
            <div class="nombreAccDir" style="color:#C00000; font-weight:bold;">SALIR</div>
        </li>
    </ul>
</div>

<script>
function cambiarPerfil(nuevo) {
    // Redirigimos al controlador para que cambie la sesión y recargue todo el frameset
    window.top.location.href = "/modulo_acceso/CEC.php?set_modulo=" + nuevo;
}
</script>
