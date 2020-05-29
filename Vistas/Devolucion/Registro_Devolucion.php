<?php
session_start();

if(isset($_POST["terminar"])){
  header("Location: ../../Pantallas/Vendedor.php");
  exit();
}

$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);

if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

$montoDevolucion = 0;

$folio_venta = $_SESSION["folio_venta"];

$folio_devolucion = $_SESSION["folio_actual"];
$rfc = $_SESSION["empleado"];
$fecha = date("Y-m-d");

$stmtIvaVenta = $enlace->prepare("SELECT iva FROM venta WHERE folio_venta = ?");
$stmtIvaVenta->bind_param("i", $folio_venta);

$stmtIvaVenta->execute();
$iva = $stmtIvaVenta->get_result()->fetch_assoc()["iva"];

$stmtRegistroDevolucion = $enlace->prepare("INSERT INTO devolucion(folio_venta, fecha, rfc_empleado) values(?, ?, ?)");
$stmtRegistroDevolucion->bind_param("iss", $folio_venta, $fecha, $rfc);
  
$stmtRegistroDetalle = $enlace->prepare("INSERT INTO detalle_devolucion(folio_devolucion, clave_producto, cantidad, motivo) VALUES(?, ?, ?, ?)");
$stmtRegistroDetalle->bind_param("iiis", $folio_devolucion, $clave, $cantidadDevolver, $motivo);
  
$stmtRegistroDevolucion->execute();

$stmtVerificarValor = $enlace->prepare("SELECT precio FROM producto WHERE clave_producto = ?");
$stmtVerificarValor->bind_param("i", $id);
  
foreach($_SESSION["devolucion"] as $id=>$info){
$stmtVerificarValor->execute();
$resultadoValor = $stmtVerificarValor->get_result();
$tuplaResultadoValor = $resultadoValor->fetch_assoc();
$montoDevolucion += ($_SESSION["devolucion"][$id]["cantidad"] * $tuplaResultadoValor["precio"]);
  
$clave = $id;
$cantidadDevolver = $_SESSION["devolucion"][$id]["cantidad"];
$motivo = $_SESSION["devolucion"][$id]["motivo"];
    
$stmtRegistroDetalle->execute();
}

$stmtRegistroDevolucion->close();
$stmtRegistroDetalle->close();

$_SESSION["dinero_caja"] -= ($montoDevolucion + ($montoDevolucion * $iva));
  
echo "Se ha registrado la devolución de manera exitosa!";

?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
    <title>Registro de la Devolución</title>
  </head>
  <body>
    <form method="post">
      <input type="submit" name="terminar" value="Salir de la Devolución" class="pure-button pure-button-primary">
    </form>
  </body>
  </html>
  
<?php

  $f_venta = $_SESSION["folio_venta"];
  $f_dev = $_SESSION["folio_actual"];
  $rfc_emp = $_SESSION["empleado"];
  
  
  echo "El RFC del empleado que le atendió es: " . $rfc_emp . "<br>";
  echo "El folio de la devolución actual es: " . $f_dev . "<br>";
  echo "El folio de la venta asociada a esta devolución es: " . $f_venta . "<br>";
  echo "La fecha actual es: " . date("d-m-Y") . "<br><br>";
  
?>
<table class="pure-table" id="productos">
  <thead>
    <legend>Productos Aceptados para Devolución</legend><br>
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Descripción</th>
      <th>Cantidad</th>
      <th>Motivo</th>
    </tr>
  </thead>
  <tbody>
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