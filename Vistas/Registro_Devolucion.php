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
  $folio = $_SESSION["folio"];
  $fecha = date("Y-m-d");
  
  $stmtRegistroDevolucion = $enlace->prepare("INSERT INTO devolucion(clave_producto, folio_venta, cantidad, motivo, fecha_devolucion) values(?, ?, ?, ?, ?)");
  $stmtRegistroDevolucion->bind_param("iiiss", $clave, $folio, $cantidadDevolver, $motivo, $fecha);
  
  $stmtActualizarStock = $enlace->prepare("UPDATE producto SET cantidad = ? WHERE clave_producto = ?");
  $stmtActualizarStock->bind_param("ii", $cantidadGenerada, $clave);
  
  $stmtVerificarCantidad = $enlace->prepare("SELECT cantidad FROM producto where clave_producto = ?");
  $stmtVerificarCantidad->bind_param("i", $clave);
  
  //Statement para validar si existe un registro en la tabla devoluciones
  $stmtVerificarExistente = $enlace->prepare("SELECT cantidad FROM devolucion WHERE folio_venta = ? AND clave_producto = ?");
  $stmtVerificarExistente->bind_param("ii", $folio, $clave);
    
  $stmtActualizarExistente = $enlace->prepare("UPDATE devolucion SET cantidad = ? where folio_venta = ? AND clave_producto = ?");
  $stmtActualizarExistente->bind_param("iii", $cantidadActualizar, $folio, $clave);
  
  foreach($_SESSION["devolucion"] as $id=>$info){
    $existeDevolucion = false;
    $clave = $id;
    $cantidadDevolver = $_SESSION["devolucion"][$id]["cantidad"];
    $motivo = $_SESSION["devolucion"][$id]["motivo"];
    
    $stmtVerificarExistente->execute();
    $resultado = $stmtVerificarExistente->get_result();
    if($resultado->num_rows != 0){
      $row = $resultado->fetch_assoc();
      $cantidadDevuelta = $row["cantidad"];
      $cantidadActualizar = $cantidadDevuelta + $cantidadDevolver;
      $existeDevolucion = true;
    }
    
    $stmtVerificarCantidad->execute();
    $resultado = $stmtVerificarCantidad->get_result();
    $row = $resultado->fetch_assoc();
    $cantidadGenerada = $row["cantidad"];
    $cantidadGenerada += $cantidadDevolver;
    
    $stmtActualizarStock->execute();
    
    if(!$existeDevolucion){
      $stmtRegistroDevolucion->execute();
    }
    else{
      $stmtActualizarExistente->execute();
    }
  }
  
  $stmtRegistroDevolucion->close();
  $stmtActualizarStock->close();
  $stmtVerificarCantidad->close();
  $stmtVerificarExistente->close();
  $stmtActualizarExistente->close();
  
  echo "Se ha registrado la devolución de manera exitosa!";
}
else{
  echo "No hay suficiente dinero en caja para realizar la devolución. Intente luego!";
}
?>