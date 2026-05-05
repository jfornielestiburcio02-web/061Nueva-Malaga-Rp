<?php
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$destinoFinal = "/modulo_acceso/controlador.061";

// Verificar cache local (Cookie)
if (isset($_COOKIE['auth_061_token'])) {
    header("Location: $destinoFinal");
    exit();
}

$usuario = $_POST['usuario'] ?? '';
$passInput = $_POST['password'] ?? '';

if (!$usuario || !$passInput) {
    header("Location: login.php");
    exit();
}

// IMPORTANTE: Asegúrate de que el usuario jmatamorosd se escriba igual que en la DB
$usuarioDoc = trim($usuario); 

// URL CORREGIDA: La colección debe ser "empleadosX" tal cual sale en tu foto
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
        // Login exitoso
        setcookie("auth_061_token", base64_encode($usuarioDoc), time() + 86400, "/");
        header("Location: $destinoFinal");
        exit();
    } else {
        echo "<script>alert('Contraseña Incorrecta'); window.location='login.php';</script>";
    }
} else {
    // Si entra aquí, es que no encuentra el DOCUMENTO o la COLECCIÓN
    echo "<script>alert('El usuario no existe en el sistema (Error: $httpCode)'); window.location='login.php';</script>";
}
?>
