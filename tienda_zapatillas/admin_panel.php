<?php
session_start();

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "admin") {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Tienda de Zapatillas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Panel de Administración</h1>
    <p>Bienvenido, <strong><?php echo $_SESSION["nombre"]; ?></strong> (Rol: Administrador)</p>

    <ul>
        <li><a href="admin_usuarios.php">Gestión de Usuarios (RF01 - Crear, modificar y eliminar)</a></li>
        <li><a href="admin_articulos.php">Gestión de Zapatillas (RF02 - Crear, modificar y eliminar)</a></li>
        <li><a href="index.php">Ver catálogo público de la tienda</a></li>
        <li><a href="logout.php">Cerrar sesión</a></li>
    </ul>
</body>
</html>