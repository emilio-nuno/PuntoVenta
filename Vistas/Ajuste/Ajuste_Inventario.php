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
  
$stmtFolioAjuste = $enlace->prepare("SELECT folio_ajuste FROM ajuste_inventario ORDER BY folio_ajuste DESC LIMIT 1"); //Verificamos el valor máximo de un folio
$stmtFolioAjuste->execute();
$resultado = $stmtFolioAjuste->get_result();
if($resultado->num_rows == 0){
  $folioAjuste = 1;
}
else{
  $tupla = $resultado->fetch_assoc();
  $folioAjuste = $tupla["folio_ajuste"];
  $folioAjuste++;
}

$fecha = date("Y-m-d");

$stmtNombreEmpleado = $enlace->prepare("SELECT nombre_empleado FROM empleado WHERE rfc_empleado = ?");
$stmtNombreEmpleado->bind_param("s", $_SESSION["empleado"]);
$stmtNombreEmpleado->execute();
$tuplaNombreEmpleado = $stmtNombreEmpleado->get_result()->fetch_assoc();
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
  <p>Folio de Ajuste actual: <?=$folioAjuste?></p>
  <strong>Nombre de Gerente: <?=$tuplaNombreEmpleado["nombre_empleado"]?></strong>
  <p>Fecha actual: <?=$fecha?></p>
  <p>Por favor digite la información solicitada para cada uno de los productos pertinentes</p>
  <form class="pure-form" method="post">
    <fieldset>

        <input type="text" placeholder="Clave del Producto" name="clave" id="clave" onchange="MostrarDescripcion('#clave', '#descProducto');" required>
        <input type="number" placeholder="Cantidad" min="1" step="1" name="cantidad" required>
        <input type="text" placeholder="Motivo" name="motivo" required>

        <button type="submit" class="pure-button pure-button-primary" name="ajustar">Agregar a Ajuste</button>
      
        <div id="descProducto">
        </div>
    </fieldset>
</form>
  <form method="post">
        <input type="submit" class="pure-button" name="confirmar" value="Confirmar Ajuste">
</form>
</body>
  
</html>

<?php
if(isset($_POST["ajustar"])){
  $clave = $_POST["clave"];
  $cantidad = $_POST["cantidad"];
  $motivo = $_POST["motivo"];
  
  $stmtVerificarProducto = $enlace->prepare("SELECT * FROM producto WHERE clave_producto = ?"); //tenemos dos consultas redundantes
  $stmtVerificarProducto->bind_param("i", $clave);
  $stmtVerificarProducto->execute();
  $result = $stmtVerificarProducto->get_result();
  
  if($result->num_rows === 0){
    echo "El producto no existe en la base de datos";
  }
  else{
    
    $cantidadDisponible = $result->fetch_assoc()["cantidad"];
    $cantidadActualCarrito = isset($_SESSION["ajuste"][$clave]["cantidad"]) ? $_SESSION["ajuste"][$clave]["cantidad"] : 0;  
    
    if($cantidadDisponible >= $cantidadActualCarrito + $cantidad){
      
      $consultarDatos = "SELECT * FROM producto where clave_producto = '$clave'";
      $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
      $row = mysqli_fetch_array($ejecutarConsultar);
      

      $_SESSION["ajuste"][$clave]["descripcion"] = $row["descripcion"];
      $_SESSION["ajuste"][$clave]["nombre"] = $row["nombre"];
      $_SESSION["ajuste"][$clave]["cantidad"] = isset($_SESSION["ajuste"][$clave]["cantidad"]) ? $_SESSION["ajuste"][$clave]["cantidad"] + $cantidad: $cantidad;
      $_SESSION["ajuste"][$clave]["motivo"] = $motivo;?>
  
      <table class="pure-table">
          <thead>
            <br><legend>Productos listos para ajuste</legend><br>
            <tr>
              <th>Clave de Producto</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Cantidad</th>
              <th>Motivo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($_SESSION["ajuste"] as $id=>$valor){ ?>
              <tr>
                <td><?=$id?></td>
                <td><?=$_SESSION["ajuste"][$id]["nombre"]?></td>
                <td><?=$_SESSION["ajuste"][$id]["descripcion"]?></td>
                <td><?=$_SESSION["ajuste"][$id]["cantidad"]?></td>
                <td><?=$_SESSION["ajuste"][$id]["motivo"]?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
<?php
      }
      else{
        echo "No se puede agregar esa cantidad porque excede el stock disponible";
      }
  }
  $stmtVerificarProducto->close();
}
?>

<?php
if(isset($_POST["confirmar"])){  
  $stmtInsertarAjuste = $enlace->prepare("INSERT INTO ajuste_inventario ( fecha , rfc_empleado) VALUES ( ? , ? )");
  $stmtInsertarAjuste->bind_param("ss", $fecha, $_SESSION["empleado"]);
  
  $stmtInsertarDetalle = $enlace->prepare("INSERT INTO detalle_ajuste ( folio_ajuste ,  clave_producto ,  cantidad ,  motivo ) VALUES ( ? , ? , ? , ?)");
  $stmtInsertarDetalle->bind_param("iiis", $folioAjuste, $claveProducto, $cantidadProducto, $motivoProducto);
  
  $stmtVerificarCantidadProducto = $enlace->prepare("SELECT cantidad FROM producto WHERE clave_producto = ?");
  $stmtVerificarCantidadProducto->bind_param("i", $claveProducto);
  
  $stmtActualizarCantidadProducto = $enlace->prepare("UPDATE producto SET cantidad = ? WHERE clave_producto = ?");
  $stmtActualizarCantidadProducto->bind_param("ii", $cantidadGenerada, $claveProducto);
  
  $stmtInsertarAjuste->execute();

  foreach($_SESSION["ajuste"] as $id=>$valor){
    $claveProducto = $id;
    $cantidadProducto = $_SESSION["ajuste"][$id]["cantidad"];
    $motivoProducto = $_SESSION["ajuste"][$id]["motivo"];
    
    $stmtVerificarCantidadProducto->execute();
    $resultado = $stmtVerificarCantidadProducto->get_result();
    $tuplaCantidad = $resultado->fetch_assoc();
    $cantidadGenerada = $tuplaCantidad["cantidad"] - $cantidadProducto;
    
    $stmtActualizarCantidadProducto->execute();
    
    $stmtInsertarDetalle->execute();
  }
  $stmtInsertarAjuste->close();
  $stmtInsertarDetalle->close();
  $stmtVerificarCantidadProducto->close();
  $stmtActualizarCantidadProducto->close();
  $stmtFolioAjuste->close();
  
  echo "SE HA REGISTRADO EL AJUSTE DE MANERA EXITOSA";
  exit();
}
?>
