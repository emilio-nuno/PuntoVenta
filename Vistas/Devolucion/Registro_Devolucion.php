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
/*TODO
*Agregar funcionalidad con dinero en caja real
*Cambiar la manera en que se registra la cantidad de productos para que una devolucion que tome lugar en diferentes fechas del mismo producto no se registren en dos ocasiones. Por ejemplo de devuelve un producto 1 un dia y el otro dia se devuelve un producto 1 entonces en el registro debe tomar esa devolucion de 1 y cambiar la cantidad a 2
*/
$dineroCaja = true;
if($dineroCaja){
  $folio_venta = $_SESSION["folio_venta"];
  $folio_devolucion = $_SESSION["folio_actual"];
  $rfc = $_SESSION["empleado"];
  $fecha = date("Y-m-d");
  
  $stmtRegistroDevolucion = $enlace->prepare("INSERT INTO devolucion(folio_venta, fecha, rfc_empleado) values(?, ?, ?)");
  $stmtRegistroDevolucion->bind_param("iss", $folio_venta, $fecha, $rfc);
  
  $stmtRegistroDetalle = $enlace->prepare("INSERT INTO detalle_devolucion(folio_devolucion, clave_producto, cantidad, motivo) VALUES(?, ?, ?, ?)");
  $stmtRegistroDetalle->bind_param("iiis", $folio_devolucion, $clave, $cantidadDevolver, $motivo);
  
  $stmtActualizarStock = $enlace->prepare("UPDATE producto SET cantidad = ? WHERE clave_producto = ?");
  $stmtActualizarStock->bind_param("ii", $cantidadGenerada, $clave);
  
  $stmtVerificarCantidad = $enlace->prepare("SELECT cantidad FROM producto where clave_producto = ?");
  $stmtVerificarCantidad->bind_param("i", $clave);
  
  $stmtRegistroDevolucion->execute();
  
  foreach($_SESSION["devolucion"] as $id=>$info){
    $existeDevolucion = false;
    $clave = $id;
    $cantidadDevolver = $_SESSION["devolucion"][$id]["cantidad"];
    $motivo = $_SESSION["devolucion"][$id]["motivo"];
    
    $stmtVerificarCantidad->execute();
    $resultado = $stmtVerificarCantidad->get_result();
    $row = $resultado->fetch_assoc();
    $cantidadGenerada = $row["cantidad"];
    $cantidadGenerada += $cantidadDevolver;
    
    $stmtActualizarStock->execute();
    $stmtRegistroDetalle->execute();
  }
  
  $stmtRegistroDevolucion->close();
  $stmtActualizarStock->close();
  $stmtVerificarCantidad->close();
  $stmtRegistroDetalle->close();
  
  echo "Se ha registrado la devolución de manera exitosa!";
}
else{
  echo "No hay suficiente dinero en caja para realizar la devolución. Intente luego!";
}
?>