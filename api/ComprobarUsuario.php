<?php
// Configuración extraída de tu Firebase Config
$projectId = "yr92q8h4y5972h4y952qhy3f"; 

$usuario = $_POST['usuario'] ?? '';
$passInput = $_POST['password'] ?? '';

if (!$usuario || !$passInput) {
    header("Location: login.php");
    exit();
}

// Limpiar usuario para la URL
$usuarioDoc = preg_replace('/[^a-zA-Z0-9]/', '', $usuario);

// Consulta a la REST API de Firestore
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
    // Buscamos el campo 'contrasena' dentro del JSON de Firebase
    $passFirebase = $json['fields']['contrasena']['stringValue'] ?? '';

    if ($passInput === $passFirebase) {
        session_start();
        $_SESSION['user'] = $usuarioDoc;
        // ÉXITO: Redirigir al sistema interno
        header("Location: /panel.php");
    } else {
        echo "<script>alert('Contraseña Incorrecta'); window.location='login.php';</script>";
    }
} else {
    // Si el HTTP Code es 404, el usuario no existe
    echo "<script>alert('El usuario no existe en el sistema'); window.location='login.php';</script>";
}
?>
