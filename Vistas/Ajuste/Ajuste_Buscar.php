<?php
/*TODO:
*Agregar funcionalidad pare regresar a a menú principal
*Agregar funcionalidad para mostrar rfc de empleado que generó el ajuste
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

$stmtFolioAjusteMax = $enlace->prepare("SELECT folio_ajuste FROM ajuste_inventario ORDER BY folio_ajuste DESC LIMIT 1"); //Verificamos el valor máximo de un folio
$stmtFolioAjusteMax->execute();
$resultado = $stmtFolioAjusteMax->get_result();
if($resultado->num_rows == 0){
  echo "No puede consultar porque no hay ningún ajuste registrado... Intente después";
  header("Location: Inicio_Devolucion.php"); //Probablemente iremos a algún menú que tendremos luego
  exit();
}
else{
  $tupla = $resultado->fetch_assoc();
  $max = $tupla["folio_ajuste"];
}
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  </head>
  <body>
    <h1>Consulta de Ajustes de Inventario</h1>
    <p>En este sitio podrás consultar los ajustes de inventario</p>
    <form method="post" class="pure-form">
      <input type="number" step="1" min="1" max=<?=$max?> placeholder="Folio" name="folio" required>
      <input type="submit" value="Buscar" name="buscar">
    </form>
  </body>
</html>
  
<?php
if(isset($_POST["buscar"])){
  $stmtInfoAjuste = $enlace->prepare("SELECT folio_ajuste , fecha FROM ajuste_inventario WHERE folio_ajuste = ?");
  $stmtInfoAjuste->bind_param("i", $_POST["folio"]);
  $stmtInfoAjuste->execute();
  $resultadoAjuste = $stmtInfoAjuste->get_result();
  
  $stmtDetalleAjuste = $enlace->prepare("SELECT folio_ajuste , clave_producto, cantidad, motivo FROM  detalle_ajuste WHERE folio_ajuste = ?"); //regresa un desglose de todos los productos asociados a un ajuste
  $stmtDetalleAjuste->bind_param("i", $_POST["folio"]);
  $stmtDetalleAjuste->execute();
  $resultadoDetalle = $stmtDetalleAjuste->get_result();
?>
  <table class="pure-table">
    <thead>
      <legend>Tabla de información del ajuste con folio <?=$_POST["folio"]?></legend><br>
      <tr>
        <th>Folio de Ajuste</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody>
      <?php while($tuplaAjuste = $resultadoAjuste->fetch_assoc()){?>
      <tr>
          <td><?=$tuplaAjuste["folio_ajuste"]?></td>
          <td><?=$tuplaAjuste["fecha"]?></td>
      </tr>
      <?php }?>
    </tbody>
  </table>
  
    <table class="pure-table">
      <thead>
        <br><legend>Desglose de Productos retirados en el folio <?=$_POST["folio"]?></legend><br>
        <tr>
          <th>Clave de Producto</th>
          <th>Cantidad</th>
          <th>Motivo</th>
        </tr>
      </thead>
      <tbody>
        <?php while($tuplaDetalle = $resultadoDetalle->fetch_assoc()){?>
        <tr>
          <td><?=$tuplaDetalle["clave_producto"]?></td>
          <td><?=$tuplaDetalle["cantidad"]?></td>
          <td><?=$tuplaDetalle["motivo"]?></td>
          
        </tr>
      </tbody>
  <?php
    }
}
?>