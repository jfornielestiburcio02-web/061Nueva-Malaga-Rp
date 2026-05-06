<?php
session_start();

// 1. CONFIGURACIÓN Y SEGURIDAD
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// Verificamos token y rol básico (061)
if (!isset($_COOKIE['auth_061_token']) || !isset($_SESSION['modulo_activo'])) {
    exit("<div style='color:red; font-family:Verdana; padding:10px;'>Sesión no válida</div>");
}

$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);
$perfilActual = $_SESSION['modulo_activo']; // Ej: '061' o 'Dir'

// 2. OBTENER DATOS DE USUARIO DESDE FIREBASE
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// Mapeo de campos de Firebase
$nombreUsuario = $data['fields']['nombreUsuario']['stringValue'] ?? "Usuario";
$imagenPerfil = $data['fields']['imagenPerfil']['stringValue'] ?? "/images/default_user.png";
$roles = $data['fields']['roles']['arrayValue']['values'] ?? [];

// Comprobar si el usuario tiene el rol de Dirección (Dir) disponible
$tieneRolDir = false;
foreach ($roles as $rol) {
    if ($rol['stringValue'] === 'Dir') {
        $tieneRolDir = true;
        break;
    }
}
?>


<LINK REL="STYLESHEET" HREF="/css/modalPopUp.css/>

  
  
  
  
  <div id="cabecera">
    <!-- Lado Izquierdo: Ficha de usuario -->
    <div id="fichaSuperior">
        <img src="<?php echo $imagenPerfil; ?>" class="foto" width="50" height="50" alt="Perfil">
        <p><?php echo htmlspecialchars($nombreUsuario); ?></p>
        <p class="textoCentro">Conectado como: <strong><?php echo $perfilActual; ?></strong></p>
    </div>

    >
    <ul id="menuAccDirPer">
        <?php if ($tieneRolDir && $perfilActual !== 'Dir'): ?>
            <li onclick="cambiarPerfil('Dir')">
                <div id="iconoAccDir">
                    <img src="../images/icon_dir.png" alt="Dirección">
                </div>
                <div class="nombreAccDir">DIRECCIÓN</div>
            </li>
        <?php endif; ?>

        <?php if ($perfilActual !== '061'): ?>
            <li onclick="cambiarPerfil('061')">
                <div id="iconoAccDir">
                    <img src="../images/icon_061.png" alt="061">
                </div>
                <div class="nombreAccDir">CENTRO 061</div>
            </li>
        <?php endif; ?>
        
        <li id="salir" onclick="window.location.href='/modulo_acceso/logout.php'">
            <div class="nombreAccDir" style="color:tomato;">SALIR</div>
        </li>
    </ul>
</div>

<script>
/**
 * Cambia el perfil y actualiza todos los iframes del sistema
 * @param {string} nuevoPerfil - El nombre de la carpeta del perfil (dir o 061)
 */
function cambiarPerfil(nuevoPerfil) {
    // 1. Notificamos al servidor el cambio de perfil (opcional, vía fetch o recarga)
    // 2. Buscamos todos los iframes en la página (menú, contenido, etc.)
    const iframes = document.querySelectorAll('iframe');
    
    iframes.forEach(frame => {
        try {
            let currentPath = frame.contentWindow.location.pathname;
            
            // Reemplazamos la carpeta del perfil actual por la nueva en la URL
            // Busca patrones como /061/ o /dir/ y los intercambia
            let newPath = currentPath.replace(/\/(061|dir|Dir)\//i, '/' + nuevoPerfil.toLowerCase() + '/');
            
            if (currentPath !== newPath) {
                frame.src = newPath;
            }
        } catch (e) {
            console.error("Error al acceder al iframe (Cross-Origin):", e);
            // Si el iframe es de otro origen, al menos intentamos recargar el principal
            window.location.reload();
        }
    });

    // Opcional: Recargar la página completa para refrescar la sesión PHP con el nuevo perfil
    // window.location.href = "/modulo_acceso/cambiar_contexto.php?rol=" + nuevoPerfil;
}
</script>
