<?php
session_start();

// 1. VERIFICACIÓN DE SESIÓN Y ROL
// Comprobamos que el usuario esté logueado y que el módulo activo sea '061'
if (!isset($_COOKIE['auth_061_token']) || !isset($_SESSION['modulo_activo']) || $_SESSION['modulo_activo'] !== '061') {
    echo "<div style='color:red; font-family:sans-serif; padding:10px; font-size:12px;'>No tiene permiso</div>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* Estilo base del sidebar (el área de iconos) */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7f6; /* Color grisáceo del video */
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
        }

        /* Columna de iconos estrecha */
        .icon-bar {
            width: 50px;
            background-color: #ffffff;
            border-right: 1px solid #C5E1D1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 10px;
            z-index: 2;
        }

        /* Botón de icono individual */
        .menu-item {
            width: 40px;
            height: 40px;
            margin-bottom: 10px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 5px;
            transition: background 0.2s;
        }

        .menu-item:hover {
            background-color: #e8f5e9;
        }

        /* Espacio para la "imagen vacía" que pediste */
        .icon-placeholder {
            width: 24px;
            height: 24px;
            border: 2px solid #66bb6a; /* Verde 061 */
            border-radius: 4px;
        }

        /* Panel de submenú que se despliega lateralmente */
        .submenu-panel {
            position: absolute;
            left: 50px; /* Justo después de la barra de iconos */
            top: 0;
            bottom: 0;
            width: 150px;
            background-color: #ffffff;
            border-right: 1px solid #C5E1D1;
            display: none; /* Oculto por defecto */
            padding: 15px 10px;
            z-index: 1;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }

        .submenu-panel.active {
            display: block;
        }

        .submenu-title {
            font-size: 11px;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 15px;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        /* Enlaces del submenú */
        .submenu-link {
            display: block;
            font-size: 12px;
            color: #555;
            text-decoration: none;
            padding: 8px 5px;
            border-radius: 3px;
        }

        .submenu-link:hover {
            background-color: #f0f0f0;
            color: #000;
        }
    </style>
</head>
<body>

    <!-- BARRA DE ICONOS (Izquierda) -->
    <div class="icon-bar">
        <div class="menu-item" onclick="toggleSubmenu('menu-servicios')">
            <div class="icon-placeholder"></div>
        </div>
        <div class="menu-item" onclick="toggleSubmenu('menu-personal')">
            <div class="icon-placeholder"></div>
        </div>
        <div class="menu-item" onclick="toggleSubmenu('menu-config')">
            <div class="icon-placeholder"></div>
        </div>
    </div>

    <!-- PANELES DE SUBMENÚ (Derecha de los iconos) -->
    
    <!-- Submenú 1 -->
    <div id="menu-servicios" class="submenu-panel">
        <div class="submenu-title">Servicios</div>
        <a href="../../content/061/activas.php" target="content" class="submenu-link">Unidades Activas</a>
        <a href="../../content/061/avisos.php" target="content" class="submenu-link">Avisos Pendientes</a>
    </div>

    <!-- Submenú 2 -->
    <div id="menu-personal" class="submenu-panel">
        <div class="submenu-title">Personal</div>
        <a href="../../content/061/cuadrante.php" target="content" class="submenu-link">Mi Cuadrante</a>
        <a href="../../content/061/mensajes.php" target="content" class="submenu-link">Mensajería</a>
    </div>

    <!-- Submenú 3 -->
    <div id="menu-config" class="submenu-panel">
        <div class="submenu-title">Configuración</div>
        <a href="../../content/061/perfil.php" target="content" class="submenu-link">Mi Perfil</a>
    </div>

    <script>
        function toggleSubmenu(id) {
            // Cerramos todos primero
            const panels = document.querySelectorAll('.submenu-panel');
            let wasActive = document.getElementById(id).classList.contains('active');
            
            panels.forEach(p => p.classList.remove('active'));

            // Si no estaba abierto, lo abrimos
            if (!wasActive) {
                document.getElementById(id).classList.add('active');
            }
        }

        // Cerrar al hacer clic fuera del sidebar
        window.addEventListener('click', function(e) {
            if (!document.body.contains(e.target) || e.target === document.body) {
                const panels = document.querySelectorAll('.submenu-panel');
                panels.forEach(p => p.classList.remove('active'));
            }
        });
    </script>
</body>
</html>
