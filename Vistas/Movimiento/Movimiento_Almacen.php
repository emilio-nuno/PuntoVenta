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

$stmtFolioActual = $enlace->prepare("SELECT folio_movimiento FROM movimiento_almacen ORDER BY folio_movimiento DESC");
$stmtFolioActual->execute();

$folioActual = $stmtFolioActual->get_result()->fetch_assoc()["folio_movimiento"] + 1;

$stmtAumentarCantidadStock = $enlace->prepare("UPDATE producto SET cantidad = cantidad + ? WHERE clave_producto = ?");
$stmtAumentarCantidadStock->bind_param("ii", $cantidad, $id);

$stmtDisminuirCantidadStock = $enlace->prepare("UPDATE producto SET cantidad = cantidad - ? WHERE clave_producto = ?");
$stmtDisminuirCantidadStock->bind_param("ii", $cantidad, $id);

$stmtBuscarFolioGen = $enlace->prepare("SELECT * FROM movimiento_almacen WHERE motivo = ? AND folio_generador = ?"); //verificamos que el folio no exista para algún otro registro con el mismo motivo
$stmtBuscarFolioGen->bind_param("si", $motivo, $folio_gen);
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
  <p>Folio del Movimiento actual: <?=$folioActual?></p>
  <form method="post" class="pure-form pure-form-stacked">
    <fieldset>
      <label for="fecha">Fecha</label>
      <input type="date" id="fecha" value=<?=date("Y-m-d")?> required name="fecha" required>
      <label for="motivo">Motivo</label>
      
      <select id="motivo" name="motivo" required>
        <option value="compra_cliente">Salida - Compra de Cliente</option>
        <option value="devolucion_proveedor">Salida - Devolución a Proveedor</option>
        <option value="devolucion_cliente">Entrada - Devolución de Cliente</option>
        <option value="compra_proveedor">Entrada - Compra a Proveedor</option>
      </select>
      
      <button type="submit" class="pure-button pure-button-primary" name="consultar">Consultar Folios Generadores</button>
    </fieldset>
  </form>
</body>
</html>

<!--Poner registro de movimiento aqui-->
<?php
if(isset($_POST["confirmar"])){ //este es para operaciones de proveedor
   $fecha = $_SESSION["movimiento"]["fecha"];
    $tipo = $_SESSION["movimiento"]["tipo"];
    $motivo = $_SESSION["movimiento"]["motivo"];
    $folio_gen = $_POST["folio"];
  
    //buscarn folio dado en base de datos y si existe, no insertar nada y mostrar mensaje de error si no continuar normalmente
    $stmtBuscarFolioGen->execute();
    $resultadoBuscar = $stmtBuscarFolioGen->get_result();
  
    if($resultadoBuscar->num_rows == 0){ 
      $stmtInsertarMovimiento = $enlace->prepare("INSERT INTO movimiento_almacen ( fecha ,  tipo ,  id_empleado ,  motivo ,  folio_generador ) VALUES ( ? , ? , ? , ?, ? )");
      $stmtInsertarMovimiento->bind_param("ssssi", $fecha, $tipo, $rfc, $motivo, $folio_gen);
      $stmtInsertarMovimiento->execute();
    
      unset($_SESSION["movimiento"]);
  
      $stmtInsertarDetalle = $enlace->prepare("INSERT INTO  detalle_movimiento ( folio_movimiento ,  clave_producto ,  cantidad ) VALUES (? , ? , ?)");
      $stmtInsertarDetalle->bind_param("iii", $folioActual, $id, $cantidad);
  
      foreach($_SESSION["orden"] as $id=>$info){
        $cantidad = $_SESSION["orden"][$id]["cantidad"];
      
        if($motivo == "compra_proveedor"){
          $stmtAumentarCantidadStock->execute();
        }
        else{
          $stmtDisminuirCantidadStock->execute();
        }
      
        $stmtInsertarDetalle->execute();
      }
  
      unset($_SESSION["orden"]);
    
      if($enlace->affected_rows == 0){
        echo "Hubo un problema al intentar crear el registro, intente luego";
      }
      else{
        echo "Registro creado con éxito!";
      } 
    }
    else{
      echo "Ya existe un movimiento relacionado a ese folio generador";
    }
  }
?>

<?php
if(isset($_POST["consultar"])){
  $_SESSION["movimiento"]["motivo"] =  $_POST["motivo"];
  $_SESSION["movimiento"]["fecha"] =  $_POST["fecha"];
?>

<script type="text/javascript">
selectElement('motivo', '<?=$_POST["motivo"]?>')

function selectElement(id, valueToSelect){
  let element = document.getElementById(id);
  element.value = valueToSelect;
}
</script>

<?php
  if($_POST["motivo"] == "compra_cliente" || $_POST["motivo"] == "devolucion_proveedor"){
    $_SESSION["movimiento"]["tipo"] = "salida";
  }
  else{
    $_SESSION["movimiento"]["tipo"] = "entrada";
  }
  
  if($_POST["motivo"] == "compra_cliente" || $_POST["motivo"] == "devolucion_cliente"){
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
  else{
    unset($_SESSION["orden"]);
?>

  <form class="pure-form" method="post" id="miFormulario">
      <fieldset>

          <input type="text" placeholder="ID del producto" name="idProducto" id="idProducto" onchange="MostrarDescripcion('#idProducto', '#descProducto');" required>
          <input type="text" placeholder="Cantidad Deseada" name="cantidadProducto" id="cantidadProducto" required>

          <button type="button" class="pure-button pure-button-primary" onclick="MostrarCarrito();">Agregar a Carrito</button>
      
          <div id="descProducto">
          </div>
      </fieldset>
  </form>
    
<div id="resultados">
    
</div>

<br>
<form method="post" class="pure-form">
    <input type="number" name="folio" placeholder="Folio" step="1" required>
    <input type="submit" name="confirmar" value="Confirmar">
</form>

<?php
    
  }
}
?>

<?php
  if(isset($_POST["registrar"])){ //este es para operaciones de cliente
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