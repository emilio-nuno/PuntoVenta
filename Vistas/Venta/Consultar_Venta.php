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

$stmtFolioMax = $enlace->prepare("SELECT folio_venta FROM venta ORDER BY folio_venta DESC LIMIT 1"); //Verificamos el valor máximo de un folio
$stmtFolioMax->execute();
$resultado = $stmtFolioMax->get_result();
if($resultado->num_rows == 0){
  echo "No puede consultar porque no hay ninguna venta registrada... Intente después";
  header("Location: Verificar_Cliente.php"); //Probablemente iremos a algún menú que tendremos luego
  exit();
}
else{
  $tupla = $resultado->fetch_assoc();
  $max = $tupla["folio_venta"];
}
$stmtFolioMax->close();
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  </head>
  <body>
    <h1>Consulta de Ventas</h1>
    <p>En este sitio podrás consultar las ventas que se encuentren registradas en el sistema</p>
    <form method="post" class="pure-form">
      <input type="number" step="1" min="1" max=<?=$max?> placeholder="Folio" name="folio" required>
      <input type="submit" value="Buscar" name="buscar">
    </form>
  </body>
</html>
  
<?php
if(isset($_POST["buscar"])){
  $stmtVenta = $enlace->prepare("SELECT fecha_venta, rfc_empleado, id_cliente, iva FROM venta WHERE folio_venta = ?");
  $stmtVenta->bind_param("i", $_POST["folio"]);
  $stmtVenta->execute();
  $resultadoVenta = $stmtVenta->get_result();
  $tuplaVenta = $resultadoVenta->fetch_assoc();
  
  $stmtDetalleVenta = $enlace->prepare("SELECT clave_producto, cantidad, valor_unitario FROM detalle_venta WHERE folio_venta = ?");
  $stmtDetalleVenta->bind_param("i", $_POST["folio"]);
  $stmtDetalleVenta->execute();
  $resultadoDetalle = $stmtDetalleVenta->get_result();
?>
  <table class="pure-table">
    <thead>
      <legend>Tabla de Información de Venta</legend><br>
      <tr>
        <th>Folio de Venta</th>
        <th>Fecha</th>
        <th>RFC Empleado</th>
        <th>RFC Cliente</th>
        <th>IVA</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?=$_POST["folio"]?></td>
        <td><?=$tuplaVenta["fecha_venta"]?></td>
        <td><?=$tuplaVenta["rfc_empleado"]?></td>
        <td><?=$tuplaVenta["id_cliente"]?></td>
        <td><?=$tuplaVenta["iva"]?></td>
      </tr>
    </tbody>
  </table>
  
  <table class="pure-table">
    <thead>
      <br><legend>Tabla de Productos</legend><br>
      <tr>
        <th>Clave de Producto</th>
        <th>Cantidad</th>
        <th>Valor Unitario</th>
      </tr>
    </thead>
    <tbody>
      <?php while($tupla = $resultadoDetalle->fetch_assoc()){?>
      <tr>
        <td><?=$tupla["clave_producto"]?></td>
        <td><?=$tupla["cantidad"]?></td>
        <td><?=$tupla["valor_unitario"]?></td>
      </tr>
      <?php }?>
    </tbody>
  </table>
<?php
  $stmtVenta->close();
  $stmtDetalleVenta->close();
}
?>