<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "cliente") {
    header("Location: login.php");
    exit;
}

$mensaje = "";

if (isset($_GET["vaciar"])) {
    $_SESSION["carrito"] = [];
    $mensaje = "El carrito se ha vaciado.";
}

if (isset($_GET["quitar"])) {
    $id_quitar = $_GET["quitar"];
    unset($_SESSION["carrito"][$id_quitar]);
    $mensaje = "Artículo eliminado del carrito.";
}

$items_carrito = [];
$total_compra = 0;

if (!empty($_SESSION["carrito"])) {
    foreach ($_SESSION["carrito"] as $id_art => $cantidad) {
        $stmt = $conexion->prepare("SELECT * FROM articulos WHERE id_articulo = ?");
        $stmt->execute([$id_art]);
        $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($articulo) {
            $subtotal = $articulo["precio"] * $cantidad;
            $total_compra += $subtotal;
            $items_carrito[] = [
                "id_articulo" => $articulo["id_articulo"],
                "marca" => $articulo["marca"],
                "modelo" => $articulo["modelo"],
                "precio" => $articulo["precio"],
                "cantidad" => $cantidad,
                "subtotal" => $subtotal
            ];
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirmar_compra"]) && !empty($items_carrito)) {
    try {
        $conexion->beginTransaction();

        $stmtPedido = $conexion->prepare("INSERT INTO pedidos (id_usuario, total) VALUES (?, ?)");
        $stmtPedido->execute([$_SESSION["id_usuario"], $total_compra]);
        $id_pedido_nuevo = $conexion->lastInsertId();

        $stmtDetalle = $conexion->prepare("INSERT INTO detalle_pedidos (id_pedido, id_articulo, cantidad, precio_unit) VALUES (?, ?, ?, ?)");
        $stmtStock = $conexion->prepare("UPDATE articulos SET stock = stock - ? WHERE id_articulo = ?");

        foreach ($items_carrito as $item) {
            $stmtDetalle->execute([$id_pedido_nuevo, $item["id_articulo"], $item["cantidad"], $item["precio"]]);
            $stmtStock->execute([$item["cantidad"], $item["id_articulo"]]);
        }

        $conexion->commit();

        $_SESSION["carrito"] = [];
        $items_carrito = [];
        $mensaje = "¡Compra confirmada con éxito! Número de pedido: #" . $id_pedido_nuevo;
    } catch (Exception $e) {
        $conexion->rollBack();
        $mensaje = "Error al procesar la compra: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito de Compras (RF03)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Mi Carrito de Compras (RF03)</h1>
    <p><a href="index.php">Seguir comprando en el catálogo</a> | <a href="logout.php">Cerrar sesión</a></p>

    <?php if ($mensaje != ""): ?>
        <p style="color: green;"><strong><?php echo $mensaje; ?></strong></p>
    <?php endif; ?>

    <?php if (empty($items_carrito)): ?>
        <p>Tu carrito está vacío en este momento.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>Zapatilla</th>
                <th>Precio Unitario</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($items_carrito as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item["marca"] . " " . $item["modelo"]); ?></td>
                <td><?php echo number_format($item["precio"], 2); ?> €</td>
                <td><?php echo $item["cantidad"]; ?></td>
                <td><?php echo number_format($item["subtotal"], 2); ?> €</td>
                <td><a href="carrito.php?quitar=<?php echo $item["id_articulo"]; ?>">Quitar</a></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" align="right"><strong>TOTAL A PAGAR:</strong></td>
                <td colspan="2"><strong><?php echo number_format($total_compra, 2); ?> €</strong></td>
            </tr>
        </table>

        <br>
        <form method="POST" action="carrito.php">
            <button type="submit" name="confirmar_compra" onclick="return confirm('¿Deseas confirmar y finalizar tu compra?');">
                Confirmar Compra
            </button>
            <a href="carrito.php?vaciar=1" style="margin-left: 15px;">Vaciar carrito</a>
        </form>
    <?php endif; ?>
</body>
</html>