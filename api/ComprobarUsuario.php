<?php
// Configuración de tu proyecto Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$destinoFinal = "/modulo_acceso/controlador.061";

// 1. Verificar si ya existe una cookie de acceso previo (Cache Local)
if (isset($_COOKIE['auth_061_token'])) {
    // En un sistema real, aquí podrías validar el token contra la DB. 
    // Por ahora, si la cookie existe, le damos paso.
    header("Location: $destinoFinal");
    exit();
}

// 2. Si no hay cookie, procesamos el formulario POST
$usuario = $_POST['usuario'] ?? '';
$passInput = $_POST['password'] ?? '';

if (!$usuario || !$passInput) {
    header("Location: login.php");
    exit();
}

// Limpiamos el ID del documento para la URL de Firestore
$usuarioDoc = preg_replace('/[^a-zA-Z0-9]/', '', $usuario);

// 3. Consulta privada a la REST API de Firestore
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $json = json_decode($response, true);
    $passFirebase = $json['fields']['contrasena']['stringValue'] ?? '';

    if ($passInput === $passFirebase) {
        // --- LOGIN EXITOSO ---
        
        // A) Iniciamos sesión en el servidor
        session_start();
        $_SESSION['user'] = $usuarioDoc;

        // B) Creamos la COOKIE (Cache Local) - Expira en 24 horas (86400 segundos)
        // El '/' hace que la cookie esté disponible en toda la web
        setcookie("auth_061_token", base64_encode($usuarioDoc), time() + 86400, "/");

        // C) Redirección a la ruta solicitada
        header("Location: $destinoFinal");
        exit();

    } else {
        echo "<script>alert('Contraseña Incorrecta'); window.location='login.php';</script>";
    }
} else {
    // Si el HTTP Code es 404, el usuario no existe en la colección empleadosX
    echo "<script>alert('Acceso Denegado: Usuario no registrado'); window.location='login.php';</script>";
}
?>
