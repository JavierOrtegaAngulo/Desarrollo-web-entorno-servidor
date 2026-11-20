<?php
$servidor = "localhost";
$base_datos = "tienda_zapatillas";
$usuario = "root";
$clave = "";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $clave);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $error) {
    die("Error al conectar con la base de datos: " . $error->getMessage());
}
?>