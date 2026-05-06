<?php
session_start(); // Necesario para guardar el rol elegido de forma privada

// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. PROCESAR SELECCIÓN (Si viene del formulario de abajo)
if (isset($_POST['set_modulo'])) {
    $_SESSION['modulo_activo'] = $_POST['set_modulo'];
    header("Location: CEC.xsp"); // Redirige a la URL limpia sin parámetros
    exit();
}

// 2. CHEQUEO DE COOKIE (Caché local)
if (!isset($_COOKIE['auth_061_token'])) {
    header("Location: /modulo_acceso/");
    exit();
}

// 3. RECUPERAR EL USUARIO
$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);

// 4. CONSULTA A FIRESTORE PARA SACAR LOS ROLES
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$misRoles = [];

if ($httpCode == 200) {
    $data = json_decode($response, true);
    $rolesArray = $data['fields']['roles']['arrayValue']['values'] ?? [];
    
    foreach ($rolesArray as $item) {
        if (isset($item['stringValue'])) {
            $misRoles[] = $item['stringValue'];
        }
    }
} else {
    // Si la cookie no coincide con Firebase, limpiamos y fuera
    setcookie("auth_061_token", "", time() - 3600, "/");
    header("Location: /modulo_acceso/");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>061 Málaga - Selector de Módulo</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #121212;
            font-family: 'Segoe UI', sans-serif;
            color: white;
        }
        .container {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 380px;
        }
        .logo { height: 70px; margin-bottom: 20px; }
        h2 { margin: 0 0 10px 0; color: #d32f2f; font-size: 1.5rem; }
        .user-tag { font-size: 0.85rem; color: #777; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 1px; }
        
        .btn-modulo {
            display: block;
            width: 100%;
            padding: 16px;
            margin: 12px 0;
            border: none;
            border-radius: 8px;
            background: #d32f2f;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-modulo:hover {
            background: #b71c1c;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(211, 47, 47, 0.4);
        }
        .btn-modulo.dir { background: #222; }
        .btn-modulo.dir:hover { background: #000; }

        .logout { margin-top: 20px; font-size: 0.8rem; color: #d32f2f; cursor: pointer; text-decoration: underline; }
    </style>
</head>
<body>

    <div class="container">
        <img src="/imagenes/061.png" class="logo" alt="061">
        <h2>SELECTOR DE ROL</h2>
        <div class="user-tag">Operador: <strong><?php echo htmlspecialchars($usuarioDoc); ?></strong></div>

        <!-- Formulario oculto para enviar el rol por POST -->
        <form id="formSeleccion" method="POST" action="CEC.xsp">
            <input type="hidden" name="set_modulo" id="inputModulo">
            
            <?php if (in_array("061", $misRoles)): ?>
                <button type="button" class="btn-modulo" onclick="modulo_seleccionado('061')">Acceder Terminal 061</button>
            <?php endif; ?>

            <?php if (in_array("Dir", $misRoles)): ?>
                <button type="button" class="btn-modulo dir" onclick="modulo_seleccionado('Dir')">Acceder Dirección (Dir)</button>
            <?php endif; ?>
        </form>

        <?php if (empty($misRoles)): ?>
            <p style="color: red; font-weight: bold;">Sin roles asignados en sistema.</p>
        <?php endif; ?>

        <div class="logout" onclick="window.location.href='/modulo_acceso/'">Cerrar Sesión</div>
    </div>

    <script>
        function modulo_seleccionado(modulo) {
            // Asignamos el valor al input oculto y enviamos el formulario
            document.getElementById('inputModulo').value = modulo;
            document.getElementById('formSeleccion').submit();
        }
    </script>
</body>
</html>
