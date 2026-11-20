<?php
session_start();
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_articulo"])) {
    if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "cliente") {
        header("Location: login.php");
        exit;
    }

    $id_articulo = $_POST["id_articulo"];
    $cantidad = (int)$_POST["cantidad"];

    if (!isset($_SESSION["carrito"])) {
        $_SESSION["carrito"] = [];
    }

    if (isset($_SESSION["carrito"][$id_articulo])) {
        $_SESSION["carrito"][$id_articulo] += $cantidad;
    } else {
        $_SESSION["carrito"][$id_articulo] = $cantidad;
    }

    $mensaje = "Zapatilla añadida al carrito correctamente.";
}

$stmt = $conexion->query("SELECT * FROM articulos ORDER BY id_articulo ASC");
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_carrito = isset($_SESSION["carrito"]) ? array_sum($_SESSION["carrito"]) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - Tienda de Zapatillas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Tienda Online de Zapatillas</h1>

    <p>
        <?php if (!isset($_SESSION["id_usuario"])): ?>
            <a href="login.php">Iniciar Sesión</a> | 
            <a href="registro.php">Registrarse</a>
        <?php elseif ($_SESSION["rol"] == "cliente"): ?>
            Hola, <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong> | 
            <a href="carrito.php">Ver Carrito (<?php echo $total_carrito; ?>)</a> | 
            <a href="logout.php">Cerrar Sesión</a>
        <?php elseif ($_SESSION["rol"] == "admin"): ?>
            Modo Administrador (<strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong>) | 
            <a href="admin_panel.php">Volver al Panel Admin</a> | 
            <a href="logout.php">Cerrar Sesión</a>
        <?php endif; ?>
    </p>

    <?php if ($mensaje != ""): ?>
        <p style="color: green;"><strong><?php echo $mensaje; ?></strong></p>
    <?php endif; ?>

    <hr>

    <h2>Catálogo de Zapatillas Deportivas</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Talla</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Descripción</th>
            <th>Comprar (RF03)</th>
        </tr>
        <?php foreach ($articulos as $zapa): ?>
        <tr>
            <td><?php echo htmlspecialchars($zapa["marca"]); ?></td>
            <td><?php echo htmlspecialchars($zapa["modelo"]); ?></td>
            <td><?php echo $zapa["talla"]; ?></td>
            <td><?php echo number_format($zapa["precio"], 2); ?> €</td>
            <td><?php echo $zapa["stock"]; ?> uds.</td>
            <td><?php echo htmlspecialchars($zapa["descripcion"] ?? "-"); ?></td>
            <td>
                <?php if ($zapa["stock"] <= 0): ?>
                    <span style="color: red;">Agotado</span>
                <?php elseif (isset($_SESSION["rol"]) && $_SESSION["rol"] == "cliente"): ?>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="id_articulo" value="<?php echo $zapa["id_articulo"]; ?>">
                        <input type="number" name="cantidad" value="1" min="1" max="<?php echo $zapa["stock"]; ?>" style="width: 50px;">
                        <button type="submit">Añadir al carrito</button>
                    </form>
                <?php elseif (!isset($_SESSION["id_usuario"])): ?>
                    <a href="login.php">Inicia sesión para comprar</a>
                <?php else: ?>
                    <em>Vista previa Admin</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>