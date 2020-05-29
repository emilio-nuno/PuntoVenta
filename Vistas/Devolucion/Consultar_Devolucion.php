<?php
/*TODO:
*Agregar funcionalidad pare regresar a a menú principal
*/
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

//Sacamos el último folio registrado y hacemos que el valor máximo del input sea el último folio
//Si no existe ningún registro se redirecciona a otro sitio

$stmtFolioDevolucionMax = $enlace->prepare("SELECT folio_devolucion FROM devolucion ORDER BY folio_devolucion DESC LIMIT 1"); //Verificamos el valor máximo de un folio de devolución
$stmtFolioDevolucionMax->execute();
$resultado = $stmtFolioDevolucionMax->get_result();
if($resultado->num_rows == 0){
  echo "No puede consultar porque no hay ninguna devolución registrada... Intente después";
  header("Location: ../../Pantallas/Vendedor.php"); 
  exit();
}
else{
  $tupla = $resultado->fetch_assoc();
  $max = $tupla["folio_devolucion"];
}

$montoDevolucion = 0;
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  </head>
  <body>
    <a href="../../Pantallas/Vendedor.php">Regresar</a>
    <h1>Consulta de Devoluciones</h1>
    <p>En este sitio podrás consultar las devoluciones registradas</p>
    <form method="post" class="pure-form">
      <input type="number" step="1" min="1" max=<?=$max?> placeholder="Folio" name="folio" required>
      <input type="submit" value="Buscar" name="buscar">
    </form>
  </body>
</html>
  
<?php
if(isset($_POST["buscar"])){
  $stmtInfoMontoVenta = $enlace->prepare("SELECT SUM(cantidad * valor_unitario) as 'importe' FROM detalle_venta WHERE folio_venta = ?");
  $stmtInfoMontoVenta->bind_param("i", $folio_venta);
  
  $stmtInfoDevolucion = $enlace->prepare("SELECT * FROM devolucion WHERE folio_devolucion = ?");
  $stmtInfoDevolucion->bind_param("i", $_POST["folio"]);
  $stmtInfoDevolucion->execute();
  $resultadoInfoDevolucion = $stmtInfoDevolucion->get_result();
  $tuplaInfoDevolucion = $resultadoInfoDevolucion->fetch_assoc();
  
  $stmtDetalleDevolucion = $enlace->prepare("SELECT * FROM detalle_devolucion WHERE folio_devolucion = ?"); //regresa un desglose de todos los productos retornados en asociación con una venta
  $stmtDetalleDevolucion->bind_param("i", $_POST["folio"]);
  $stmtDetalleDevolucion->execute();
  $resultadoDetalle = $stmtDetalleDevolucion->get_result();
  
  $stmtInfoVenta = $enlace->prepare("SELECT fecha_venta, iva, id_cliente FROM venta WHERE folio_venta = ?");
  $stmtInfoVenta->bind_param("i", $folio_venta);
  
  $stmtInfoEmpleado = $enlace->prepare("SELECT nombre_empleado FROM empleado WHERE rfc_empleado = ?");
  $stmtInfoEmpleado->bind_param("s", $rfc_emp);
  
  $stmtInfoCliente = $enlace->prepare("SELECT nombre FROM cliente WHERE rfc = ?");
  $stmtInfoCliente->bind_param("s", $id_cliente);
  
  $stmtInfoProducto = $enlace->prepare("SELECT nombre, descripcion, precio FROM producto WHERE clave_producto = ?");
  $stmtInfoProducto->bind_param("i", $clav_prod);
?>
  <table class="pure-table">
    <thead>
      <legend>Tabla de Información de la Devolución <?=$_POST["folio"]?></legend><br>
      <tr>
        <th>Folio de Devolución</th>
        <th>Fecha de la Devolución</th>
        <th>Folio de Venta asociada</th>
        <th>Fecha de la Venta</th>
        <th>RFC Empleado</th>
        <th>Nombre del Empleado</th>
        <th>RFC Cliente</th>
        <th>Nombre del Cliente</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $folio_venta = $tuplaInfoDevolucion["folio_venta"];
        $rfc_emp = $tuplaInfoDevolucion["rfc_empleado"];
  
        $stmtInfoVenta->execute();
  
        $resultadoFecha = $stmtInfoVenta->get_result();
        $tuplaInfoVenta = $resultadoFecha->fetch_assoc();
  
        $iva = $tuplaInfoVenta["iva"];
        $id_cliente = $tuplaInfoVenta["id_cliente"];
  
        $stmtInfoEmpleado->execute();
  
        $resultadoEmpleado = $stmtInfoEmpleado->get_result();
        $tuplaInfoEmpleado = $resultadoEmpleado->fetch_assoc();
  
        $stmtInfoCliente->execute();
  
        $resultadoCliente = $stmtInfoCliente->get_result();
        $tuplaInfoCliente = $resultadoCliente->fetch_assoc();
      ?>
      <tr>
        <td><?=$_POST["folio"]?></td>
        <td><?=$tuplaInfoDevolucion["fecha"]?></td>
        <td><?=$tuplaInfoDevolucion["folio_venta"]?></td>
        <td><?=$tuplaInfoVenta["fecha_venta"]?></td>
        <td><?=$tuplaInfoDevolucion["rfc_empleado"]?></td>
        <td><?=$tuplaInfoEmpleado["nombre_empleado"]?></td>
        <td><?=$id_cliente?></td>
        <td><?=$tuplaInfoCliente["nombre"]?></td>
      </tr>
    </tbody>
  </table>
  
  <table class="pure-table">
    <thead>
      <br><legend>Desglose de productos devueltos en folio: <?=$_POST["folio"]?></legend><br>
      <tr>
        <th>Clave del Producto</th>
        <th>Nombre del Producto</th>
        <th>Descripción del Producto</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>Importe</th>
        <th>Motivo</th>
      </tr>
    </thead>
    <tbody>
      <?php while($tuplaInfoDetalle = $resultadoDetalle->fetch_assoc()){
      $clav_prod = $tuplaInfoDetalle["clave_producto"];
      $stmtInfoProducto->execute();
      $resultadoInfoProducto = $stmtInfoProducto->get_result();
      $tuplaInfoProducto = $resultadoInfoProducto->fetch_assoc();
        
      $montoDevolucion += $tuplaInfoDetalle["cantidad"] * $tuplaInfoProducto["precio"];
      ?>
      <tr>
        <td><?=$clav_prod?></td>
        <td><?=$tuplaInfoProducto["nombre"]?></td>
        <td><?=$tuplaInfoProducto["descripcion"]?></td>
        <td><?=$tuplaInfoDetalle["cantidad"]?></td>
        <td><?=$tuplaInfoProducto["precio"]?></td>
        <td><?=$tuplaInfoDetalle["cantidad"] * $tuplaInfoProducto["precio"]?></td>
        <td><?=$tuplaInfoDetalle["motivo"]?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  
  <table class="pure-table">
    <thead>
      <br><legend>Tabla de Información de Pago de la Devolución</legend><br>
      <tr>
        <th>Subtotal</th>
        <th>IVA</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?=$montoDevolucion?></td>
        <td><?=$montoDevolucion * $iva?></td>
        <td><?=($montoDevolucion + ($montoDevolucion * $iva))?></td>
      </tr>
    </tbody>
  </table>
<?php
}
?>