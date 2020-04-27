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
?>

<!DOCTYPE html>
<html>
<head>
  <title>Página de Devolución</title>
  <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
</head>

<body>
  <p>Por favor digite la información solicitada para cada uno de los productos pertinentes</p>
  <form class="pure-form" method="post">
    <fieldset>

        <input type="text" placeholder="Clave del Producto" name="clave" required>
        <input type="number" placeholder="Cantidad" min="1" step="1" name="cantidad" required>
        <input type="text" placeholder="Motivo" name="motivo" required>

        <button type="submit" class="pure-button pure-button-primary" name="ajustar">Agregar a Ajuste</button>
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
  
  $stmtVerificarProducto = $enlace->prepare("SELECT * FROM producto WHERE clave_producto = ?");
  $stmtVerificarProducto->bind_param("i", $clave);
  $stmtVerificarProducto->execute();
  $result = $stmtVerificarProducto->get_result();
  if($result->num_rows === 0){
    echo "El producto no existe en la base de datos";
  }
  else{
    $_SESSION["ajuste"][$clave]["cantidad"] = $cantidad;
    $_SESSION["ajuste"][$clave]["motivo"] = $motivo;?>
  
    <table class="pure-table">
        <thead>
          <br><legend>Productos listos para ajuste</legend><br>
          <tr>
            <th>Clave de Producto</th>
            <th>Cantidad</th>
            <th>Motivo</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($_SESSION["ajuste"] as $id=>$valor){ ?>
            <tr>
              <td><?=$id?></td>
              <td><?=$_SESSION["ajuste"][$id]["cantidad"]?></td>
              <td><?=$_SESSION["ajuste"][$id]["motivo"]?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
<?php
  }
  $stmtVerificarProducto->close();
}
?>

<?php
if(isset($_POST["confirmar"])){  
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
  $stmtInsertarAjuste = $enlace->prepare("INSERT INTO ajuste_inventario ( fecha ) VALUES (?)");
  $stmtInsertarAjuste->bind_param("s", $fecha);
  
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