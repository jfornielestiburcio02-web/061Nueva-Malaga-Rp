<?php
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; // Usamos la key por tus reglas privadas

$usuario = $_POST['usuario'] ?? '';
$passInput = $_POST['password'] ?? '';

if (!$usuario || !$passInput) {
    header("Location: login.php");
    exit();
}

$usuarioDoc = trim($usuario); 
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

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
        // --- LOGIN CORRECTO: Procesar Roles ---
        $rolesRaw = $json['fields']['roles']['arrayValue']['values'] ?? [];
        $misRoles = [];

        foreach ($rolesRaw as $roleItem) {
            $misRoles[] = $roleItem['stringValue'];
        }

        // Mostramos una interfaz sencilla para elegir el módulo
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Selección de Módulo - 061</title>
            <style>
                body { font-family: sans-serif; background: #1a1a1a; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; color: white; }
                .container { background: #fff; padding: 40px; border-radius: 15px; text-align: center; color: #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
                .btn-modulo { display: block; width: 200px; padding: 15px; margin: 10px auto; border: none; border-radius: 8px; background: #d32f2f; color: white; font-weight: bold; cursor: pointer; transition: 0.3s; text-decoration: none; }
                .btn-modulo:hover { background: #b71c1c; transform: translateY(-2px); }
                h2 { margin-bottom: 20px; font-size: 1.2rem; }
            </style>
        </head>
        <body>
            <div class="container">
                <img src="/imagenes/061.png" style="height: 60px; margin-bottom: 10px;">
                <h2>BIENVENIDO, <?php echo htmlspecialchars($usuarioDoc); ?></h2>
                <p>Selecciona tu terminal de acceso:</p>

                <?php if (in_array("061", $misRoles)): ?>
                    <button class="btn-modulo" onclick="modulo_seleccionado('061')">ACCESO 061</button>
                <?php endif; ?>

                <?php if (in_array("Dir", $misRoles)): ?>
                    <button class="btn-modulo" onclick="modulo_seleccionado('Dir')">DIRECCIÓN (Dir)</button>
                <?php endif; ?>

                <?php if (empty($misRoles)): ?>
                    <p style="color: red;">No tienes roles asignados.</p>
                <?php endif; ?>
            </div>

            <script>
                function modulo_seleccionado(tipo) {
                    // Redirigir a CEC.php pasando el rol seleccionado
                    window.location.href = "CEC.php?modulo=" + tipo;
                }
            </script>
        </body>
        </html>
        <?php
        exit();

    } else {
        echo "<script>alert('Contraseña Incorrecta'); window.location='login.php';</script>";
    }
} else {
    echo "<script>alert('Error de acceso: Usuario no encontrado'); window.location='login.php';</script>";
}
