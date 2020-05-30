<?php
/*TODO:
*Agregar campo de nombre de empleado y desplegar información del folio generado (venta o devolución)
*/
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

$stmtFolioMovimientoMax = $enlace->prepare("SELECT folio_movimiento FROM movimiento_almacen ORDER BY folio_movimiento DESC LIMIT 1"); //Verificamos el valor máximo de un folio
$stmtFolioMovimientoMax->execute();
$resultado = $stmtFolioMovimientoMax->get_result();
if($resultado->num_rows == 0){
  echo "No puede consultar porque no hay ninguna devolución registrada... Intente después";
  //header("Location: Inicio_Devolucion.php"); //Probablemente iremos a algún menú que tendremos luego
  exit();
}
else{
  $tupla = $resultado->fetch_assoc();
  $max = $tupla["folio_movimiento"];
}

$stmtInfoProducto = $enlace->prepare("SELECT nombre, descripcion, cantidad FROM producto WHERE clave_producto = ?");
$stmtInfoProducto->bind_param("i", $claveProducto);
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  </head>
  <body>
    <a href="../../Pantallas/Almacenista.php">Regresar</a>
    <h1>Consulta de Movimientos</h1>
    <p>En este sitio podrás consultar los movimientos de almacén registrados</p>
    <form method="post" class="pure-form">
      <input type="number" step="1" min="1" max=<?=$max?> placeholder="Folio" name="folio" required>
      <input type="submit" value="Buscar" name="buscar">
    </form>
  </body>
</html>

<?php
if(isset($_POST["buscar"])){
  $stmtBuscar = $enlace->prepare("SELECT * FROM movimiento_almacen WHERE folio_movimiento = ?");
  $stmtBuscar->bind_param("i", $_POST["folio"]);
  $stmtBuscar->execute();
  $tuplaMovimiento = $stmtBuscar->get_result()->fetch_assoc();
  
  $stmtProductosVenta = $enlace->prepare("SELECT clave_producto, cantidad, valor_unitario FROM detalle_venta WHERE folio_venta = ?");
  $stmtProductosVenta->bind_param("i", $tuplaMovimiento["folio_generador"]);
  
  $stmtProductosDevolucion = $enlace->prepare("SELECT clave_producto, cantidad, motivo FROM detalle_devolucion WHERE folio_devolucion = ?");
  $stmtProductosDevolucion->bind_param("i", $tuplaMovimiento["folio_generador"]);
  
  $stmtProductosMovimiento = $enlace->prepare("SELECT clave_producto, cantidad, motivo FROM detalle_movimiento WHERE folio_movimiento = ?");
  $stmtProductosMovimiento->bind_param("i", $_POST["folio"]);
  
  $rfc_emp = $tuplaMovimiento["id_empleado"];
  
  $stmtNombreEmpleado = $enlace->prepare("SELECT nombre_empleado FROM empleado WHERE rfc_empleado = ?");
  $stmtNombreEmpleado->bind_param("s", $rfc_emp);
  $stmtNombreEmpleado->execute();
  $tuplaNombreEmpleado = $stmtNombreEmpleado->get_result()->fetch_assoc();
  
  $stmtNombreCliente = $enlace->prepare("SELECT nombre FROM cliente WHERE rfc = ?");
  $stmtNombreCliente->bind_param("s", $rfc_cli);
  ?>
  <table class="pure-table">
    <thead>
      <legend>Tabla de Información de Movimiento</legend><br>
      <tr>
        <th>Folio de Movimiento</th>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>RFC Empleado</th>
        <th>Nombre Empleado</th>
        <th>Motivo</th>
        <th>Folio Generador</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?=$_POST["folio"]?></td>
        <td><?=$tuplaMovimiento["fecha"]?></td>
        <td><?=$tuplaMovimiento["tipo"]?></td>
        <td><?=$tuplaMovimiento["id_empleado"]?></td>
        <td><?=$tuplaNombreEmpleado["nombre_empleado"]?></td>
        <td><?=$tuplaMovimiento["motivo"]?></td>
        <td><?=$tuplaMovimiento["folio_generador"]?></td>
      </tr>
    </tbody>
  </table>
  <?php
  if($tuplaMovimiento["motivo"] == "compra_cliente" || $tuplaMovimiento["motivo"] == "devolucion_cliente"){
  if($tuplaMovimiento["motivo"] == "compra_cliente"){
    $stmtVenta = $enlace->prepare("SELECT fecha_venta, rfc_empleado, id_cliente, iva, metodo_pago FROM venta WHERE folio_venta = ?");
    $stmtVenta->bind_param("i", $tuplaMovimiento["folio_generador"]);
    $stmtVenta->execute();
    $resultadoVenta = $stmtVenta->get_result();
    $tuplaVenta = $resultadoVenta->fetch_assoc();
    
    $rfc_emp = $tuplaVenta["rfc_empleado"];
    $stmtNombreEmpleado->execute();
    $tuplaNombreEmpleado = $stmtNombreEmpleado->get_result()->fetch_assoc();
    
    $rfc_cli = $tuplaVenta["id_cliente"];
    $stmtNombreCliente->execute();
    $tuplaNombreCliente = $stmtNombreCliente->get_result()->fetch_assoc();
    
    $stmtProductosVenta->execute();
    $resultadoProductos = $stmtProductosVenta->get_result();
    ?>
    <table class="pure-table">
    <thead>
      <br><legend>Tabla de Información de Venta</legend><br>
      <tr>
        <th>Folio de Venta</th>
        <th>Fecha</th>
        <th>RFC Empleado</th>
        <th>Nombre Empleado</th>
        <th>RFC Cliente</th>
        <th>Nombre Cliente</th>
        <th>IVA</th>
        <th>Método de Pago</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?=$tuplaMovimiento["folio_generador"]?></td>
        <td><?=$tuplaVenta["fecha_venta"]?></td>
        <td><?=$tuplaVenta["rfc_empleado"]?></td>
        <td><?=$tuplaNombreEmpleado["nombre_empleado"]?></td>
        <td><?=$tuplaVenta["id_cliente"]?></td>
        <td><?=$tuplaNombreCliente["nombre"]?></td>
        <td><?=$tuplaVenta["iva"]?></td>
        <td><?=$tuplaVenta["metodo_pago"]?></td>
      </tr>
    </tbody>
  </table>
  <?php
  }
  else{
    $stmtInfoDevolucion = $enlace->prepare("SELECT * FROM devolucion WHERE folio_devolucion = ?");
    $stmtInfoDevolucion->bind_param("i", $tuplaMovimiento["folio_generador"]);
    $stmtInfoDevolucion->execute();
    $tuplaInfoDevolucion = $stmtInfoDevolucion->get_result()->fetch_assoc();
    
    $stmtInfoVenta = $enlace->prepare("SELECT fecha_venta FROM venta WHERE folio_venta = ?");
    $stmtInfoVenta->bind_param("i", $tuplaInfoDevolucion["folio_venta"]);
    $stmtInfoVenta->execute();
    $tuplaFechaVenta = $stmtInfoVenta->get_result()->fetch_assoc();
  
    $stmtInfoEmpleado = $enlace->prepare("SELECT nombre_empleado FROM empleado WHERE rfc_empleado = ?");
    $stmtInfoEmpleado->bind_param("s", $tuplaInfoDevolucion["rfc_empleado"]);
    $stmtInfoEmpleado->execute();
    $tuplaInfoEmpleado = $stmtInfoEmpleado->get_result()->fetch_assoc();
    
    $stmtProductosDevolucion->execute();
    $resultadoProductos = $stmtProductosDevolucion->get_result();
    ?>
    <table class="pure-table">
      <thead>
        <br><legend>Tabla de Información de la Devolución</legend><br>
        <tr>
          <th>Folio de Devolución</th>
          <th>Fecha de la Devolución</th>
          <th>Folio de Venta asociada</th>
          <th>Fecha de la Venta</th>
          <th>RFC Empleado</th>
          <th>Nombre del Empleado</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?=$tuplaMovimiento["folio_generador"]?></td>
          <td><?=$tuplaInfoDevolucion["fecha"]?></td>
          <td><?=$tuplaInfoDevolucion["folio_venta"]?></td>
          <td><?=$tuplaFechaVenta["fecha_venta"]?></td>
          <td><?=$tuplaInfoDevolucion["rfc_empleado"]?></td>
          <td><?=$tuplaInfoEmpleado["nombre_empleado"]?></td>
        </tr>
      </tbody>
    </table>
  
<?php 
  } } else{
    $stmtProductosMovimiento->execute();
    $resultadoProductos = $stmtProductosMovimiento->get_result();
  }
  ?>
  <table class="pure-table">
    <thead>
      <br><legend>Desglose de Productos</legend><br>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <?= $tuplaMovimiento["motivo"] == "compra_cliente" ? "<th>Valor Unitario</th><th>Importe</th>" : "" //tal vez agregar lo mismo para proveedor ?>
        <?= ($tuplaMovimiento["motivo"] == "devolucion_cliente" or $tuplaMovimiento["motivo"] == "devolucion_proveedor") ? "<th>Motivo</th>" : ""?>
      </tr>
    </thead>
    <tbody>
      <?php while($tuplaProductos = $resultadoProductos->fetch_assoc()){
        $claveProducto = $tuplaProductos["clave_producto"];
        $stmtInfoProducto->execute();
        $tuplaProducto = $stmtInfoProducto->get_result()->fetch_assoc();
      ?>
      <tr>
        <td><?=$claveProducto?></td>
        <td><?=$tuplaProducto["nombre"]?></td>
        <td><?=$tuplaProducto["descripcion"]?></td>
        <td><?=$tuplaProductos["cantidad"]?></td>
        <?= $tuplaMovimiento["motivo"] == "compra_cliente" ? "<td>" . $tuplaProductos["valor_unitario"] . "</td>" . "<td>" . $tuplaProductos["cantidad"] * $tuplaProductos["valor_unitario"]. "</td>" : ""?>
        <?= ($tuplaMovimiento["motivo"] == "devolucion_cliente" or $tuplaMovimiento["motivo"] == "devolucion_proveedor") ? "<td>" . $tuplaProductos["motivo"] . "</td>" : ""?>
      </tr>
      <?php } ?>
    </tbody>
  </table>
<?php } ?>