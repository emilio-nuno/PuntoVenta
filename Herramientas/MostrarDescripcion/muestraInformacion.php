<?php
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

$stmtDescProducto = $enlace->prepare("SELECT descripcion FROM producto WHERE clave_producto = ?");
$stmtDescProducto->bind_param("i", $_POST["clave"]);
$stmtDescProducto->execute();
$resDescripcion = $stmtDescProducto->get_result();
$descripcion = $resDescripcion->num_rows != 0 ? $resDescripcion->fetch_assoc()["descripcion"] : "El código de producto indicado no existe";
?>

<strong><?=$descripcion?></strong>