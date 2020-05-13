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

$cantidadActual = $_SESSION["dinero_caja"]; //la cantidad actual en caja después de la última ventas
$cantidadRetiro = abs(2000 - $cantidadActual); //sacamos la cantidad que debemos retirar para dejar 2 mil pesos
$rfc_empleado = $_SESSION["empleado"];
$fecha = date("Y-m-d");
$hora = date("H:i");

$stmtFolioFlujo = $enlace->prepare("SELECT folio_flujo FROM flujo_efectivo ORDER BY folio_flujo DESC LIMIT 1"); //Verificamos el valor máximo de un folio
$stmtFolioFlujo->execute();
$resultado = $stmtFolioFlujo->get_result();
if($resultado->num_rows == 0){
  $folioActual = 1;
}
else{
  $tupla = $resultado->fetch_assoc();
  $folioActual = $tupla["folio_flujo"] += 1;
}
?>

<?php
if(isset($_POST["confirmar"])){
  $stmtInsertarFlujo = $enlace->prepare("INSERT INTO flujo_efectivo ( fecha ,  hora ,  id_empleado ,  monto ) VALUES ( ? , ? , ? , ? )");
  $stmtInsertarFlujo->bind_param("sssi", $fecha, $hora, $rfc_empleado, $_POST["cantidad"]);
  $stmtInsertarFlujo->execute();
  $stmtInsertarFlujo->close();
  $_SESSION["dinero_caja"] -= $_POST["cantidad"];
  header("Location: ../../Pantallas/Vendedor.php");
  exit();
}
?>

<DOCTYPE !html>
<html>
  <head>
    <title>Flujo de Efectivo</title>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  </head>
  <body>
    <h1>Página de Generación de Flujos de Efectivo</h1>
    <h2>A continuación podrá revisar los datos generados por el sistema, pero por razones de seguridad <span style="color:red;">solo</span> puede cambiar el valor del monto de retiro</h2>
    
    <p>Folio del flujo actual: <?=$folioActual?></p>
    <p>Fecha actual: <?=$fecha?></p>
    <p>Hora: <?=$hora?></p>
    <p>RFC del Empleado: <?=$rfc_empleado?></p>
    <p>Monto generado: <?=$cantidadRetiro?></p>
    
    <h3>El monto generado toma en cuenta que se deben dejar 2 mil pesos en la caja mínimo, pero si usted desea retirar una cantidad menor, puede editar el valor en la caja de entrada de monto</h3>
    
    <form method="post" class="pure-form">
      <input type="number" max=<?=$cantidadRetiro?> name="cantidad" value=<?=$cantidadRetiro?> step="1000" required>
      <input type="submit" name="confirmar" class="pure-button pure-button-primary" value="Confirmar el Flujo">
    </form>
  </body>
</html>