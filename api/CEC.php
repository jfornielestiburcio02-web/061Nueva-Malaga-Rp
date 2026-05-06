<?php
// Configuración de tu Firebase
$projectId = "yr92q8h4y5972h4y952qhy3f"; 
$apiKey = "AIzaSyBwhUOE8XpDFGf7dsqEdfXh2FCWE94JR2w"; 

// 1. CAPTURAR EL ROL (Lo enviamos desde el selector)
// Si no hay rol, volvemos al selector para que elijan uno
$rolSolicitado = $_POST['set_modulo'] ?? ''; 

if (empty($rolSolicitado)) {
    header("Location: /modulo_acceso/controlador.061");
    exit();
}

// 2. VALIDACIÓN DE SEGURIDAD (Copiado de tu código que sí funciona)
if (!isset($_COOKIE['auth_061_token'])) {
    header("Location: /modulo_acceso/");
    exit();
}

$usuarioDoc = base64_decode($_COOKIE['auth_061_token']);

// 3. CONSULTA A FIRESTORE PARA VERIFICAR PERMISOS
$url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/empleadosX/{$usuarioDoc}?key={$apiKey}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tienePermiso = false;
if ($httpCode == 200) {
    $data = json_decode($response, true);
    $rolesArray = $data['fields']['roles']['arrayValue']['values'] ?? [];
    
    foreach ($rolesArray as $item) {
        if (isset($item['stringValue']) && $item['stringValue'] === $rolSolicitado) {
            $tienePermiso = true;
            break;
        }
    }
}

// Si no tiene el rol que dice tener, fuera
if (!$tienePermiso) {
    header("Location: /modulo_acceso/controlador.061?error=no_access");
    exit();
}

$rolMin = strtolower($rolSolicitado);
?>



<HTML>

    
    <meta charset="UTF-8">
<TITLE>CEC</TITLE>
    
    <link rel="stylesheet" href="/css/CEC.css"



    
</head>




    
        <body>


    

    <iframe id="frame-header" src="/header/<?php echo $rolMin; ?>/prin.php" name="header"></iframe>




    
    <div id="contenedor-sidebar">
        <iframe id="frame-sidebar" src="/sidebar/<?php echo $rolMin; ?>/prin.php" name="sidebar"></iframe>
    </div>


    
    
    <iframe id="frame-content" src="/contenidoEmpleado/<?php echo $rolMin; ?>/prin.php" name="content"></iframe>

</body>









    
</HTML>
