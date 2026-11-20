<?php
session_start();
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $direccion = $_POST["direccion"];
    $telefono = !empty($_POST["telefono"]) ? $_POST["telefono"] : null;

    if (empty($nombre) || empty($email) || empty($password) || empty($direccion)) {
        $mensaje = "Por favor, rellena todos los campos obligatorios (*).";
    } else {
        $password_cifrada = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (nombre, email, password, direccion, telefono, rol) 
                    VALUES (?, ?, ?, ?, ?, 'cliente')";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nombre, $email, $password_cifrada, $direccion, $telefono]);

            $mensaje = "Usuario registrado correctamente. Ya puedes iniciar sesión.";
        } catch (PDOException $e) {
            $mensaje = "Error: Ese correo electrónico ya está registrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Cliente - Tienda de Zapatillas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Registro de Nuevo Cliente</h1>
    <p><a href="index.php">Volver al catálogo</a> | <a href="login.php">Iniciar sesión</a></p>

    <?php if ($mensaje != ""): ?>
        <p><strong><?php echo $mensaje; ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="registro.php">
        <label>Nombre completo (*):</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Correo electrónico (*):</label><br>
        <input type="email" name="email" required><br><br>

        <label>Contraseña (*):</label><br>
        <input type="password" name="password" required><br><br>

        <label>Dirección de envío (*):</label><br>
        <input type="text" name="direccion" required><br><br>

        <label>Teléfono (Opcional):</label><br>
        <input type="text" name="telefono"><br><br>

        <button type="submit">Registrarse</button>
    </form>
</body>
</html>