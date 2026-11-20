<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "admin") {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$articulo_editar = null;

if (isset($_GET["eliminar"])) {
    try {
        $stmt = $conexion->prepare("DELETE FROM articulos WHERE id_articulo = ?");
        $stmt->execute([$_GET["eliminar"]]);
        $mensaje = "Zapatilla eliminada correctamente.";
    } catch (PDOException $e) {
        $mensaje = "No se puede eliminar esta zapatilla porque ya forma parte de pedidos realizados.";
    }
}

if (isset($_GET["editar"])) {
    $stmt = $conexion->prepare("SELECT * FROM articulos WHERE id_articulo = ?");
    $stmt->execute([$_GET["editar"]]);
    $articulo_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $modelo = $_POST["modelo"];
    $marca = $_POST["marca"];
    $talla = $_POST["talla"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];
    $descripcion = $_POST["descripcion"];

    if (!empty($_POST["id_articulo"])) {
        $sql = "UPDATE articulos SET modelo=?, marca=?, talla=?, precio=?, stock=?, descripcion=? WHERE id_articulo=?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$modelo, $marca, $talla, $precio, $stock, $descripcion, $_POST["id_articulo"]]);
        $mensaje = "Zapatilla actualizada correctamente.";
    } else {
        $sql = "INSERT INTO articulos (modelo, marca, talla, precio, stock, descripcion) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$modelo, $marca, $talla, $precio, $stock, $descripcion]);
        $mensaje = "Zapatilla añadida correctamente al catálogo.";
    }
}

$stmtLista = $conexion->query("SELECT * FROM articulos ORDER BY id_articulo ASC");
$articulos = $stmtLista->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Zapatillas (RF02)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestión de Artículos / Zapatillas (RF02)</h1>
    <p><a href="admin_panel.php">Volver al Panel Admin</a> | <a href="logout.php">Cerrar sesión</a></p>

    <?php if ($mensaje != ""): ?>
        <p style="color: green;"><strong><?php echo $mensaje; ?></strong></p>
    <?php endif; ?>

    <h2><?php echo $articulo_editar ? "Modificar Zapatilla" : "Añadir Nueva Zapatilla"; ?></h2>
    <form method="POST" action="admin_articulos.php">
        <input type="hidden" name="id_articulo" value="<?php echo $articulo_editar["id_articulo"] ?? ""; ?>">

        <label>Marca (*):</label><br>
        <input type="text" name="marca" value="<?php echo $articulo_editar["marca"] ?? ""; ?>" required><br><br>

        <label>Modelo (*):</label><br>
        <input type="text" name="modelo" value="<?php echo $articulo_editar["modelo"] ?? ""; ?>" required><br><br>

        <label>Talla (*):</label><br>
        <input type="number" step="0.5" name="talla" value="<?php echo $articulo_editar["talla"] ?? ""; ?>" required><br><br>

        <label>Precio (€) (*):</label><br>
        <input type="number" step="0.01" name="precio" value="<?php echo $articulo_editar["precio"] ?? ""; ?>" required><br><br>

        <label>Stock disponible (*):</label><br>
        <input type="number" name="stock" value="<?php echo $articulo_editar["stock"] ?? ""; ?>" required><br><br>

        <label>Descripción (Opcional):</label><br>
        <textarea name="descripcion" rows="3" cols="40"><?php echo $articulo_editar["descripcion"] ?? ""; ?></textarea><br><br>

        <button type="submit"><?php echo $articulo_editar ? "Guardar Cambios" : "Crear Zapatilla"; ?></button>
        <?php if ($articulo_editar): ?>
            <a href="admin_articulos.php">Cancelar edición</a>
        <?php endif; ?>
    </form>

    <hr>

    <h2>Inventario de Zapatillas</h2>
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Talla</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($articulos as $a): ?>
        <tr>
            <td><?php echo $a["id_articulo"]; ?></td>
            <td><?php echo htmlspecialchars($a["marca"]); ?></td>
            <td><?php echo htmlspecialchars($a["modelo"]); ?></td>
            <td><?php echo $a["talla"]; ?></td>
            <td><?php echo number_format($a["precio"], 2); ?> €</td>
            <td><?php echo $a["stock"]; ?></td>
            <td><?php echo htmlspecialchars($a["descripcion"] ?? "-"); ?></td>
            <td>
                <a href="admin_articulos.php?editar=<?php echo $a["id_articulo"]; ?>">Editar</a> |
                <a href="admin_articulos.php?eliminar=<?php echo $a["id_articulo"]; ?>" onclick="return confirm('¿Seguro que deseas eliminar esta zapatilla?');">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>