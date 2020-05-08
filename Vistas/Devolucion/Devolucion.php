<?php
 session_start();
/*TODO:
*Meter validación de producto sin descuento
*Meter validacion de no poder generar la devolucion si no existe suficiente dinero en caja
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
  
  <p>Folio de la devolución actual: <?=$folio?></p>
  <p>Fecha actual: <?=date("Y-m-d")?></p>
  <p>Venta asociada a esta Devolución: <?=$_SESSION["folio_venta"]?></p>
  
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
    
    if($cantidadComprada < $_POST["cantidad"] or $_POST["cantidad"] > ($cantidadComprada - $cantidadDevuelta)){
      echo "Estás intentando regresar más productos de los que compraste";
    }
    else{
      $consultarDatos = "SELECT * FROM producto where clave_producto = '$clave'";
      $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
      $row = mysqli_fetch_array($ejecutarConsultar);
      
      $_SESSION["devolucion"][$clave]["cantidad"] = $_POST["cantidad"];
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
