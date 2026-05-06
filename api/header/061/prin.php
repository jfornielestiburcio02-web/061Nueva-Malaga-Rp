<?php
// 1. SESIÓN Y ERRORES (Cero espacios antes de esta línea)
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();[cite: 1]

// 2. CONFIGURACIÓN
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 3. IDENTIFICACIÓN (Igual que el sidebar)
$token = $_COOKIE['auth_061_token'] ?? '';[cite: 1]
$rolActivo = $_SESSION['modulo_activo'] ?? '061'; 

if (empty($token)) {
    die("Error: No hay token");
}

$usuarioDoc = base64_decode($token);

// 4. CURL A FIREBASE
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 5. PROCESAR DATOS
$nombre = "Usuario";
$img = "";
$tieneDir = false;
$accesoOk = false;

if ($httpCode === 200) {
    $data = json_decode($response, true);
    
    // Extraer Nombre e Imagen
    $nombre = $data['fields']['nombreUsuario']['stringValue'] ?? "Usuario";
    $img = $data['fields']['imagenPerfil']['stringValue'] ?? "";
    
    // Validar Roles
    $roles = $data['fields']['roles']['arrayValue']['values'] ?? [];
    foreach ($roles as $r) {
        $val = $r['stringValue'] ?? '';
        if ($val === $rolActivo) $accesoOk = true;
        if ($val === 'Dir') $tieneDir = true;
    }
}

if (!$accesoOk) {
    die("Acceso denegado al perfil: " . htmlspecialchars($rolActivo));[cite: 1]
}
?>
<div id="cabecera">
    <div id="fichaSuperior">
        <?php if ($img): ?>
            <img src="<?php echo $img; ?>" class="foto" width="50" height="50">
        <?php else: ?>
            <div style="width:50px; height:50px; border-radius:50%; background:#297A38; float:left; margin-right:8px;"></div>
        <?php endif; ?>
        
        <p><?php echo htmlspecialchars($nombre); ?></p>
        <p class="textoCentro">Perfil activo: <strong><?php echo $rolActivo; ?></strong></p>
    </div>

    <ul id="menuAccDirPer">
        <!-- Si tiene rol Dir y no estoy en él, muestro botón -->
        <?php if ($tieneDir && $rolActivo !== 'Dir'): ?>
            <li onclick="irA('Dir')">
                <div id="iconoAccDir"><div style="width:35px; height:35px; background:#646361; border-radius:50%; margin:auto;"></div></div>
                <div class="nombreAccDir">DIRECCIÓN</div>
            </li>
        <?php endif; ?>

        <!-- Si no estoy en 061, muestro botón volver -->
        <?php if ($rolActivo !== '061'): ?>
            <li onclick="irA('061')">
                <div id="iconoAccDir"><div style="width:35px; height:35px; background:#297A38; border-radius:50%; margin:auto;"></div></div>
                <div class="nombreAccDir">CENTRO 061</div>
            </li>
        <?php endif; ?>
        
        <li id="salir" onclick="window.top.location.href='/modulo_acceso/logout.php'">
            <div class="nombreAccDir" style="color:#d32f2f; font-weight:bold;">SALIR</div>
        </li>
    </ul>
</div>

<script>
function irA(perfil) {
    // Redirigimos al controlador principal para que cambie la sesión y recargue los frames
    window.top.location.href = "/modulo_acceso/CEC.php?set_modulo=" + perfil;
}
</script>
