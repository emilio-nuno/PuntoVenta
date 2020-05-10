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

$rfc = $_SESSION["empleado"];
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  <script src="http://code.jquery.com/jquery-latest.js"></script>
  <script src="../../Herramientas/MostrarDescripcion/mostrarDescripcion.js"></script>
  <title>Registro de Movimientos</title>
</head>

<body>
  <form method="post" class="pure-form pure-form-stacked">
    <fieldset>
      <label for="fecha">Fecha</label>
      <input type="date" id="fecha" value=<?=date("Y-m-d")?> required name="fecha" required>
      <label for="motivo">Motivo</label>
      
      <select id="motivo" name="motivo" required>
        <option value="compra_cliente">Salida - Compra de Cliente</option>
        <option value="devolucion_cliente">Entrada - Devolución de Cliente</option>
      </select>
      
      <button type="submit" class="pure-button pure-button-primary" name="consultar">Consultar Folios Generadores</button>
    </fieldset>
  </form>
</body>
</html>

<?php
if(isset($_POST["consultar"])){
  $_SESSION["movimiento"]["motivo"] =  $_POST["motivo"];
  $_SESSION["movimiento"]["fecha"] =  $_POST["fecha"];
  $_SESSION["movimiento"]["tipo"] =  $_POST["motivo"] == "compra_cliente" ? "salida" : "entrada";
  
  if($_POST["motivo"] == "compra_cliente"){
    $stmtFoliosVenta = $enlace->prepare("SELECT folio_venta, fecha_venta FROM venta ORDER BY folio_venta ASC");
    $stmtFoliosVenta->execute();
    $resultadoFoliosVenta = $stmtFoliosVenta->get_result();
    ?>
    <table class="pure-table">
      <thead>
          <tr>
            <th>Folio de Venta</th>
            <th>Fecha</th>
          </tr>
      </thead>
      <tbody>
        <?php while($tuplaFolioVenta = $resultadoFoliosVenta->fetch_assoc()){
        $max = $tuplaFolioVenta["folio_venta"];
        ?>
        <tr>
          <td><?=$tuplaFolioVenta["folio_venta"]?></td>
          <td><?=$tuplaFolioVenta["fecha_venta"]?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
<?php
  }
  else{
    $stmtFoliosDevolucion = $enlace->prepare("SELECT folio_devolucion, fecha FROM devolucion ORDER BY folio_devolucion ASC");
    $stmtFoliosDevolucion->execute();
    $resultadoFoliosDevolucion = $stmtFoliosDevolucion->get_result();?>

    <table class="pure-table">
      <thead>
          <tr>
            <th>Folio de Devolución</th>
            <th>Fecha</th>
          </tr>
      </thead>
      <tbody>
        <?php while($tuplaFolioDevolucion = $resultadoFoliosDevolucion->fetch_assoc()){
        $max = $tuplaFolioDevolucion["folio_devolucion"];
        ?>
        <tr>
          <td><?=$tuplaFolioDevolucion["folio_devolucion"]?></td>
          <td><?=$tuplaFolioDevolucion["fecha"]?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>
  
  <br>
  <div id="resultado">

  </div>
  
  <form class="pure-form pure-form-stacked" method="post">
    <fieldset>
        <label for="folio">Folio Generador</label>
        <input type="number" name="folio" id="folio" min="1" max="<?=$max?>" onchange="MostrarInfoFolio('<?=$_SESSION["movimiento"]["motivo"]?>', '#folio', '#resultado')" required>
        <button type="submit" name="registrar" class="button-secondary pure-button">Registrar Movimiento</button>
    </fieldset>
  </form>
<?php
}
?>

<?php
  if(isset($_POST["registrar"])){
    $fecha = $_SESSION["movimiento"]["fecha"];
    $tipo = $_SESSION["movimiento"]["tipo"];
    $motivo = $_SESSION["movimiento"]["motivo"];
    $folio_gen = $_POST["folio"];
    
    $stmtInsertarMovimiento = $enlace->prepare("INSERT INTO movimiento_almacen ( fecha ,  tipo ,  id_empleado ,  motivo ,  folio_generador ) VALUES ( ? , ? , ? , ?, ? )");
    $stmtInsertarMovimiento->bind_param("ssssi", $fecha, $tipo, $rfc, $motivo, $folio_gen);
    $stmtInsertarMovimiento->execute();
    
    unset($_SESSION["movimiento"]);
    
    if($enlace->affected_rows == 0){
      echo "Hubo un problema al intentar crear el registro, intente luego";
    }
    else{
      echo "Registro creado con éxito!";
    }
    
  }
?>