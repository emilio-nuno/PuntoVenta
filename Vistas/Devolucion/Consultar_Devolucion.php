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

$stmtFolioVentaMax = $enlace->prepare("SELECT folio_venta FROM venta ORDER BY folio_venta DESC LIMIT 1"); //Verificamos el valor máximo de un folio
$stmtFolioVentaMax->execute();
$resultado = $stmtFolioVentaMax->get_result();
if($resultado->num_rows == 0){
  echo "No puede consultar porque no hay ninguna venta registrada... Intente después";
  header("Location: Inicio_Devolucion.php"); //Probablemente iremos a algún menú que tendremos luego
  exit();
}
else{
  $tupla = $resultado->fetch_assoc();
  $max = $tupla["folio_venta"];
}
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  </head>
  <body>
    <h1>Consulta de Devoluciones</h1>
    <p>En este sitio podrás consultar las devoluciones asociadas a una venta</p>
    <form method="post" class="pure-form">
      <input type="number" step="1" min="1" max=<?=$max?> placeholder="Folio" name="folio" required>
      <input type="submit" value="Buscar" name="buscar">
    </form>
  </body>
</html>
  
<?php
if(isset($_POST["buscar"])){
  $stmtInfoDevolucion = $enlace->prepare("SELECT folio_devolucion, fecha, rfc_empleado FROM devolucion WHERE folio_venta = ?");
  $stmtInfoDevolucion->bind_param("i", $_POST["folio"]);
  $stmtInfoDevolucion->execute();
  $resultadoInfoDevolucion = $stmtInfoDevolucion->get_result();
  
  $stmtDetalleDevolucion = $enlace->prepare("SELECT folio_devolucion, clave_producto, cantidad, motivo FROM detalle_devolucion WHERE folio_devolucion IN(SELECT folio_devolucion FROM devolucion WHERE folio_venta = ?)"); //regresa un desglose de todos los productos retornados en asociación con una venta
  $stmtDetalleDevolucion->bind_param("i", $_POST["folio"]);
  $stmtDetalleDevolucion->execute();
  $resultadoDetalle = $stmtDetalleDevolucion->get_result();
  
  $stmtContadorDevoluciones = $enlace->prepare("SELECT folio_devolucion, COUNT(*) as num_productos FROM detalle_devolucion WHERE folio_devolucion IN(SELECT folio_devolucion FROM devolucion WHERE folio_venta = ?) GROUP BY folio_devolucion"); //retorna folio de dev y num productos asociados a ese folio
  $stmtContadorDevoluciones->bind_param("i", $_POST["folio"]);
  $stmtContadorDevoluciones->execute();
  $resultadoDevoluciones = $stmtContadorDevoluciones->get_result();
?>
  <table class="pure-table">
    <thead>
      <legend>Tabla de Devoluciones asociadas a la Venta <?=$_POST["folio"]?></legend><br>
      <tr>
        <th>Folio de Devolución</th>
        <th>Fecha</th>
        <th>RFC Empleado</th>
      </tr>
    </thead>
    <tbody>
      <?php while($tuplaInfoDevolucion = $resultadoInfoDevolucion->fetch_assoc()){?>
      <tr>
          <td><?=$tuplaInfoDevolucion["folio_devolucion"]?></td>
          <td><?=$tuplaInfoDevolucion["fecha"]?></td>
          <td><?=$tuplaInfoDevolucion["rfc_empleado"]?></td>
      </tr>
      <?php }?>
    </tbody>
  </table>
  
  <?php while($tuplaVeces = $resultadoDevoluciones->fetch_assoc()){
    $numVeces = $tuplaVeces["num_productos"];?>
  
    <table class="pure-table">
      <thead>
        <br><legend>Desglose de Productos devueltos en el folio <?=$tuplaVeces["folio_devolucion"]?></legend><br>
        <tr>
          <th>Clave de Producto</th>
          <th>Cantidad</th>
          <th>Motivo</th>
        </tr>
      </thead>
      <tbody>
    <?php
    for($i = 0; $i < $numVeces; $i++){
      $tuplaDetalle = $resultadoDetalle->fetch_assoc();
    ?>
        <tr>
          <td><?=$tuplaDetalle["clave_producto"]?></td>
          <td><?=$tuplaDetalle["cantidad"]?></td>
          <td><?=$tuplaDetalle["motivo"]?></td>
          
        </tr>
      </tbody>
  <?php
    }
  }
}
?>