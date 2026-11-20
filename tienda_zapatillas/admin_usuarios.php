<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "admin") {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$usuario_editar = null;

if (isset($_GET["eliminar"])) {
    $id_borrar = $_GET["eliminar"];
    if ($id_borrar != $_SESSION["id_usuario"]) {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_borrar]);
        $mensaje = "Usuario eliminado correctamente.";
    } else {
        $mensaje = "No puedes eliminar tu propio usuario administrador mientras tienes la sesión abierta.";
    }
}

if (isset($_GET["editar"])) {
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$_GET["editar"]]);
    $usuario_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $direccion = $_POST["direccion"];
    $telefono = !empty($_POST["telefono"]) ? $_POST["telefono"] : null;
    $rol = $_POST["rol"];
    $password = $_POST["password"];

    if (!empty($_POST["id_usuario"])) {
        $id_usuario = $_POST["id_usuario"];
        if (!empty($password)) {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre=?, email=?, password=?, direccion=?, telefono=?, rol=? WHERE id_usuario=?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nombre, $email, $pass_hash, $direccion, $telefono, $rol, $id_usuario]);
        } else {
            $sql = "UPDATE usuarios SET nombre=?, email=?, direccion=?, telefono=?, rol=? WHERE id_usuario=?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nombre, $email, $direccion, $telefono, $rol, $id_usuario]);
        }
        $mensaje = "Usuario actualizado correctamente.";
    } else {
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, email, password, direccion, telefono, rol) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$nombre, $email, $pass_hash, $direccion, $telefono, $rol]);
        $mensaje = "Usuario creado correctamente.";
    }
}

$stmtLista = $conexion->query("SELECT * FROM usuarios ORDER BY id_usuario ASC");
$usuarios = $stmtLista->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios (RF01)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestión de Usuarios (RF01)</h1>
    <p><a href="admin_panel.php">Volver al Panel Admin</a> | <a href="logout.php">Cerrar sesión</a></p>

    <?php if ($mensaje != ""): ?>
        <p style="color: green;"><strong><?php echo $mensaje; ?></strong></p>
    <?php endif; ?>

    <h2><?php echo $usuario_editar ? "Modificar Usuario" : "Crear Nuevo Usuario"; ?></h2>
    <form method="POST" action="admin_usuarios.php">
        <input type="hidden" name="id_usuario" value="<?php echo $usuario_editar["id_usuario"] ?? ""; ?>">

        <label>Nombre (*):</label><br>
        <input type="text" name="nombre" value="<?php echo $usuario_editar["nombre"] ?? ""; ?>" required><br><br>

        <label>Email (*):</label><br>
        <input type="email" name="email" value="<?php echo $usuario_editar["email"] ?? ""; ?>" required><br><br>

        <label>Contraseña <?php echo $usuario_editar ? "(dejar vacía para no cambiar)" : "(*)"; ?>:</label><br>
        <input type="password" name="password" <?php echo $usuario_editar ? "" : "required"; ?>><br><br>

        <label>Dirección (*):</label><br>
        <input type="text" name="direccion" value="<?php echo $usuario_editar["direccion"] ?? ""; ?>" required><br><br>

        <label>Teléfono (Opcional):</label><br>
        <input type="text" name="telefono" value="<?php echo $usuario_editar["telefono"] ?? ""; ?>"><br><br>

        <label>Tipo de usuario (Rol) (*):</label><br>
        <select name="rol">
            <option value="cliente" <?php echo (isset($usuario_editar["rol"]) && $usuario_editar["rol"] == "cliente") ? "selected" : ""; ?>>Cliente</option>
            <option value="admin" <?php echo (isset($usuario_editar["rol"]) && $usuario_editar["rol"] == "admin") ? "selected" : ""; ?>>Administrador</option>
        </select><br><br>

        <button type="submit"><?php echo $usuario_editar ? "Guardar Cambios" : "Crear Usuario"; ?></button>
        <?php if ($usuario_editar): ?>
            <a href="admin_usuarios.php">Cancelar edición</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Listado de Usuarios Registrados</h2>
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?php echo $u["id_usuario"]; ?></td>
            <td><?php echo htmlspecialchars($u["nombre"]); ?></td>
            <td><?php echo htmlspecialchars($u["email"]); ?></td>
            <td><?php echo htmlspecialchars($u["direccion"]); ?></td>
            <td><?php echo htmlspecialchars($u["telefono"] ?? "-"); ?></td>
            <td><strong><?php echo $u["rol"]; ?></strong></td>
            <td>
                <a href="admin_usuarios.php?editar=<?php echo $u["id_usuario"]; ?>">Editar</a> |
                <a href="admin_usuarios.php?eliminar=<?php echo $u["id_usuario"]; ?>" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>