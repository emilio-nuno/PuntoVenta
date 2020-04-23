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
/*TODO
*Agregar funcionalidad con dinero en caja real
*Cambiar la manera en que se registra la cantidad de productos para que una devolucion que tome lugar en diferentes fechas del mismo producto no se registren en dos ocasiones. Por ejemplo de devuelve un producto 1 un dia y el otro dia se devuelve un producto 1 entonces en el registro debe tomar esa devolucion de 1 y cambiar la cantidad a 2
*/
$dineroCaja = true;
if($dineroCaja){
  $folio_venta = $_SESSION["folio_venta"];
  $folio_devolucion = $_SESSION["folio_actual"];
  $rfc = $_SESSION["empleado"];
  $fecha = date("Y-m-d");
  
  $stmtRegistroDevolucion = $enlace->prepare("INSERT INTO devolucion(folio_venta, fecha, rfc_empleado) values(?, ?, ?)");
  $stmtRegistroDevolucion->bind_param("iss", $folio_venta, $fecha, $rfc);
  
  $stmtRegistroDetalle = $enlace->prepare("INSERT INTO detalle_devolucion(folio_devolucion, clave_producto, cantidad, motivo) VALUES(?, ?, ?, ?)");
  $stmtRegistroDetalle->bind_param("iiis", $folio_devolucion, $clave, $cantidadDevolver, $motivo);
  
  $stmtActualizarStock = $enlace->prepare("UPDATE producto SET cantidad = ? WHERE clave_producto = ?");
  $stmtActualizarStock->bind_param("ii", $cantidadGenerada, $clave);
  
  $stmtVerificarCantidad = $enlace->prepare("SELECT cantidad FROM producto where clave_producto = ?");
  $stmtVerificarCantidad->bind_param("i", $clave);
  
  $stmtRegistroDevolucion->execute();
  
  foreach($_SESSION["devolucion"] as $id=>$info){
    $existeDevolucion = false;
    $clave = $id;
    $cantidadDevolver = $_SESSION["devolucion"][$id]["cantidad"];
    $motivo = $_SESSION["devolucion"][$id]["motivo"];
    
    $stmtVerificarCantidad->execute();
    $resultado = $stmtVerificarCantidad->get_result();
    $row = $resultado->fetch_assoc();
    $cantidadGenerada = $row["cantidad"];
    $cantidadGenerada += $cantidadDevolver;
    
    $stmtActualizarStock->execute();
    $stmtRegistroDetalle->execute();
  }
  
  $stmtRegistroDevolucion->close();
  $stmtActualizarStock->close();
  $stmtVerificarCantidad->close();
  $stmtRegistroDetalle->close();
  
  echo "Se ha registrado la devolución de manera exitosa!";
}
else{
  echo "No hay suficiente dinero en caja para realizar la devolución. Intente luego!";
}
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
    <title>Hola mundo!</title>
  </head>
  <body>
    <form method="post">
      <input type="submit" name="terminar" value="Salir de la Devolución" class="pure-button pure-button-primary">
    </form>
  </body>
  </html>
  
<?php
if($dineroCaja){
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
?>
  
<?php
if(isset($_POST["terminar"])){
  header("Location: Inicio_Devolucion.php");
  exit();
}  
?>