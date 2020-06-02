<?php
 session_start();
/*TODO:
*Agregar iva de la venta al monto de la devolucion en el total
*/

//Inicializar variables para conexión a BD
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

$fechaVenta = strtotime($_SESSION["fecha"]); //agregar fecha de $_SESSION
$actual = strtotime(date("Y-m-d"));

$dif = abs($actual - $fechaVenta);
$anos = floor($dif / ((365*60*60*24)));
$meses = floor(($dif - $anos * 365*60*60*24) / (30*60*60*24)); 

if($meses > 0){
    header("Location: Inicio_Devolucion.php");
    exit();
}

$stmtFolioActualPrincipio = $enlace->prepare("SELECT folio_devolucion FROM devolucion ORDER BY folio_devolucion DESC LIMIT 1"); //hacer lo mismo en venta
$stmtFolioActualPrincipio->execute();
$result = $stmtFolioActualPrincipio->get_result();
if($result->num_rows == 0){
  $folio = 1;
}
else{
  $row = $result->fetch_assoc();
  $folio = $row["folio_devolucion"] + 1;
}

$stmtInfoEmpleado = $enlace->prepare("SELECT nombre_empleado FROM empleado WHERE rfc_empleado = ?");
$stmtInfoEmpleado->bind_param("s" ,$_SESSION["empleado"]);
$stmtInfoEmpleado->execute();

$infoEmpleado = $stmtInfoEmpleado->get_result();
    
$tuplaInfoEmpleado= $infoEmpleado->fetch_assoc();
$nomEmpleado = $tuplaInfoEmpleado["nombre_empleado"];
$rfc_empleado = $_SESSION["empleado"];

$stmtInfoCliente = $enlace->prepare("SELECT nombre, rfc FROM cliente WHERE rfc IN (SELECT id_cliente FROM venta WHERE folio_venta = ?)");
$stmtInfoCliente->bind_param("s" , $_SESSION["folio_venta"]);
$stmtInfoCliente->execute();

$infoCliente = $stmtInfoCliente->get_result();
    
$tuplaInfoCliente = $infoCliente->fetch_assoc();
$nomCliente = $tuplaInfoCliente["nombre"];
$rfc_cliente = $tuplaInfoCliente["rfc"];

$stmtProductosVenta = $enlace->prepare("SELECT clave_producto, cantidad, valor_unitario FROM detalle_venta WHERE folio_venta = ?");
$stmtProductosVenta->bind_param("i", $_SESSION["folio_venta"]);
$stmtProductosVenta->execute();
$resultadoProductosVenta = $stmtProductosVenta->get_result();

$stmtInfoProducto = $enlace->prepare("SELECT nombre, descripcion, precio FROM producto WHERE clave_producto = ?");
$stmtInfoProducto->bind_param("i", $clave_producto);

$stmtInfoPagoVenta = $enlace->prepare("CREATE TEMPORARY TABLE desglose_dia SELECT folio_venta, clave_producto, cantidad, valor_unitario, valor_unitario * cantidad as total FROM detalle_venta");
$stmtInfoPagoVenta->execute();

$stmtInfoVentaIndividual = $enlace->prepare("SELECT folio_venta, SUM(total) as monto FROM desglose_dia WHERE folio_venta = ?");
$stmtInfoVentaIndividual->bind_param("i", $_SESSION["folio_venta"]);
$stmtInfoVentaIndividual->execute();
$montoVenta = $stmtInfoVentaIndividual->get_result()->fetch_assoc()["monto"];

$stmtIVAVenta = $enlace->prepare("SELECT iva FROM venta WHERE folio_venta = ?");
$stmtIVAVenta->bind_param("i", $_SESSION["folio_venta"]);
$stmtIVAVenta->execute();
$iva = $stmtIVAVenta->get_result()->fetch_assoc()["iva"];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Página de Devolución</title>
  <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  <script src="http://code.jquery.com/jquery-latest.js"></script>
  <script src="../../Herramientas/MostrarDescripcion/mostrarDescripcion.js"></script>
</head>

<body>
  
  <p>Le atiende: <strong><?=$nomEmpleado?></strong></p>
  <p>RFC Empleado: <?=$rfc_empleado?></p>
  
  <p>Folio de la devolución actual: <?=$folio?></p>
  <p>Fecha actual: <?=date("Y-m-d")?></p>
  <p>Venta asociada a esta Devolución: <?=$_SESSION["folio_venta"]?></p>
  
  <p>La venta fue realizada por <?=$nomCliente?></p>
  <p>RFC Cliente: <?=$rfc_cliente?></p>
  
  <!--Aquí va la tabla de productos-->
  <table class="pure-table">
        <thead>
          <legend>Productos vendidos en la Venta asociada</legend>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Cantidad</th>
              <th>Precio</th>
              <th>Importe</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <?php while($tuplaProductoVenta = $resultadoProductosVenta->fetch_assoc()){ 
            $clave_producto = $tuplaProductoVenta["clave_producto"];
            $stmtInfoProducto->execute();
            $tuplaInfoProducto = $stmtInfoProducto->get_result()->fetch_assoc();
            $precio = $tuplaInfoProducto["precio"]; //esto solo saca el precio de la bd pero idealmente se sacaria de la tabla venta, arreglar
            $nombre = $tuplaInfoProducto["nombre"];
            $desc = $tuplaInfoProducto["descripcion"];
            ?>
              <td><?=$tuplaProductoVenta["clave_producto"]?></td>
              <td><?=$nombre?></td>
              <td><?=$desc?></td>
              <td><?=$tuplaProductoVenta["cantidad"]?></td>
              <td><?=$precio?></td>
              <td><?=$tuplaProductoVenta["cantidad"] * $precio?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table> 
  
  <p>Subtotal de la Venta: <?=$montoVenta?></p>
  <p>IVA de la Venta: <?=$montoVenta * $iva?></p>
  <p>Total de la Venta: <?=$montoVenta + ($montoVenta * $iva)?></p>
  
  <p>Vendedor, por favor <span style="color:red;">verifique</span> los productos entregados por el cliente antes de llenar el formulario para cada producto</p>
  <form class="pure-form" method="post">
    <fieldset>

        <input type="text" placeholder="Clave del Producto" name="clave" id="clave" onchange="MostrarDescripcion('#clave', '#descProducto');" required>
        <input type="number" placeholder="Cantidad" min="1" step="1" name="cantidad" required>
        <input type="text" placeholder="Motivo" name="motivo" required>

        <button type="submit" class="pure-button pure-button-primary" name="devolver">Agregar a Devolución</button>
        <div id="descProducto">
        </div>
    </fieldset>
</form>
  <form method="post">
        <input type="submit" class="pure-button" name="confirmar" value="Confirmar Devolución">
</form>
</body>
  
</html>

<?php
if(isset($_POST["devolver"])){
  $clave = trim($_POST["clave"]);
  $stmt = $enlace->prepare("SELECT cantidad FROM detalle_venta WHERE folio_venta = ? AND clave_producto = ?");
  $stmt->bind_param("ii", $_SESSION["folio_venta"], $clave);
  $stmt->execute();
  $result = $stmt->get_result();
  if($result->num_rows === 0){
    echo "El producto no se encuentra asociado a el folio dado";
  }
  else{
    $row = $result->fetch_assoc();
    $cantidadComprada = $row["cantidad"];
    
    $stmtFolioActual = $enlace->prepare("SELECT folio_devolucion FROM devolucion ORDER BY folio_devolucion DESC LIMIT 1"); //hacer lo mismo en venta
    $stmtFolioActual->execute();
    $result = $stmtFolioActual->get_result();
    if($result->num_rows == 0){
      $folio = 1;
    }
    else{
      $row = $result->fetch_assoc();
      $folio = $row["folio_devolucion"] + 1;
    }
    
    $_SESSION["folio_actual"] = $folio;
    $cantidadDevuelta = 0;
    
    $folio_venta = $_SESSION["folio_venta"];

    $stmtValidar = $enlace->prepare("SELECT clave_producto, cantidad FROM detalle_devolucion WHERE folio_devolucion IN (SELECT folio_devolucion FROM devolucion WHERE folio_venta = ?)");
    $stmtValidar->bind_param("i", $folio_venta);
    $stmtValidar->execute();
    $result = $stmtValidar->get_result();
    if($result->num_rows != 0){
      while($row = $result->fetch_assoc()){
        if($row["clave_producto"] == $clave){
          $cantidadDevuelta += $row["cantidad"];
        }
      }
    }
    
    $cantidadActualCarrito = isset($_SESSION["devolucion"][$clave]["cantidad"]) ? $_SESSION["devolucion"][$clave]["cantidad"] : 0;
    
    if($cantidadComprada < $_POST["cantidad"] + $cantidadActualCarrito or $cantidadActualCarrito + $_POST["cantidad"] > ($cantidadComprada - $cantidadDevuelta)){
      echo "Estás intentando regresar más productos de los que compraste";
    }
    else{
      $consultarDatos = "SELECT * FROM producto where clave_producto = '$clave'";
      $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
      $row = mysqli_fetch_array($ejecutarConsultar);
      
      $_SESSION["devolucion"][$clave]["cantidad"] = isset( $_SESSION["devolucion"][$clave]["cantidad"]) ? $_SESSION["devolucion"][$clave]["cantidad"] + $_POST["cantidad"]: $_POST["cantidad"];
      $_SESSION["devolucion"][$clave]["motivo"] = $_POST["motivo"];
      $_SESSION["devolucion"][$clave]["descripcion"] = $row["descripcion"];
      $_SESSION["devolucion"][$clave]["nombre"] = $row["nombre"];
      ?>
      <table class="pure-table" id="productos">
        <thead>
          <legend>Productos Aceptados para Devolución</legend>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Cantidad</th>
              <th>Motivo</th>
          </tr>
        </thead>
        <tbody>
          <tr>
          <?php
          foreach($_SESSION["devolucion"] as $id=>$info){
          ?>
          <tr>
            <td><?=$id?></td>
            <td><?=$_SESSION["devolucion"][$id]["nombre"]?></td>
            <td><?=$_SESSION["devolucion"][$id]["descripcion"]?></td>
            <td><?=$_SESSION["devolucion"][$id]["cantidad"]?></td>
            <td><?=$_SESSION["devolucion"][$id]["motivo"]?></td>
          </tr>
        <?php
        }
        ?>
        </tbody>
      </table> 
<?php
    }
  }
  $stmt->close();
}
?>

<?php
if(isset($_POST["confirmar"])){ //aqui verificamos si se puede proceder con la devolucion
  $montoDevolucion = 0; //con esto verificaremos si se puede realizar la devolución
  
  $stmtVerificarValor = $enlace->prepare("SELECT precio FROM producto WHERE clave_producto = ?");
  $stmtVerificarValor->bind_param("i", $id);
  
  foreach($_SESSION["devolucion"] as $id=>$info){
    $stmtVerificarValor->execute();
    $resultadoValor = $stmtVerificarValor->get_result();
    $tuplaResultadoValor = $resultadoValor->fetch_assoc();
    $montoDevolucion += ($_SESSION["devolucion"][$id]["cantidad"] * $tuplaResultadoValor["precio"]);
  }
  
  if($montoDevolucion > $_SESSION["dinero_caja"]){
    header("Location: ../../Pantallas/Vendedor.php");
    exit();
  }
  else{
    header("Location: Gerente_Autorizar.php");
    exit();
  }
}
?>
