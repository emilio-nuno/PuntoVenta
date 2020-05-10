<?php
session_start();
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

$motivo = $_POST["motivo"];
$folio = $_POST["folio"];

$stmtInfoVenta = $enlace->prepare("SELECT clave_producto, cantidad, valor_unitario FROM detalle_venta WHERE folio_venta = ?");
$stmtInfoVenta->bind_param("i", $folio);

$stmtInfoDevolucion = $enlace->prepare("SELECT clave_producto, cantidad, motivo FROM detalle_devolucion WHERE folio_devolucion = ?");
$stmtInfoDevolucion->bind_param("i", $folio);

$stmtInfoProducto = $enlace->prepare("SELECT nombre, descripcion FROM producto WHERE clave_producto = ?");
$stmtInfoProducto->bind_param("i", $clave_producto);

$esVenta = false;

if($motivo == "compra_cliente"){
  $stmtInfoVenta->execute();
  $resultadoConsulta = $stmtInfoVenta->get_result();
  $esVenta = true;
}
else{
  $stmtInfoDevolucion->execute();
  $resultadoConsulta = $stmtInfoDevolucion->get_result();
}
?>

<table class="pure-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <?php if($esVenta){ ?>
            <th>Valor Unitario</th>
            <?php } 
            else{ ?>
            <th>Motivo</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
      <?php while($tuplaResultado = $resultadoConsulta->fetch_assoc()){ 
      $clave_producto = $tuplaResultado["clave_producto"];
      $stmtInfoProducto->execute();
      $tuplaInfoProducto = $stmtInfoProducto->get_result()->fetch_assoc();
      ?>
      <tr>
        <td><?=$tuplaResultado["clave_producto"]?></td>
        <td><?=$tuplaInfoProducto["nombre"]?></td>
        <td><?=$tuplaInfoProducto["descripcion"]?></td>
        <td><?=$tuplaResultado["cantidad"]?></td>
        <?php if($esVenta){ ?>
        <td><?=$tuplaResultado["valor_unitario"]?></td>
        <?php } 
        else{ ?>
        <td><?=$tuplaResultado["motivo"]?></td>
        <?php } ?>
      </tr>
      <?php } ?>
    </tbody>
</table>