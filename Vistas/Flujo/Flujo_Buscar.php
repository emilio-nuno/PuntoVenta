<?php
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

$stmtFolioAjusteMax = $enlace->prepare("SELECT folio_flujo FROM flujo_efectivo ORDER BY folio_flujo DESC LIMIT 1"); //Verificamos el valor máximo de un folio
$stmtFolioAjusteMax->execute();
$resultado = $stmtFolioAjusteMax->get_result();
if($resultado->num_rows == 0){
  echo "No puede consultar porque no hay ningún flujo registrado... Intente después";
  //header("Location: Inicio_Devolucion.php"); //Probablemente iremos a algún menú que tendremos luego
  exit();
}
else{
  $tupla = $resultado->fetch_assoc();
  $max = $tupla["folio_flujo"];
}
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  </head>
  <body>
    <h1>Consulta de Flujos de Efectivo</h1>
    <p>En este sitio podrás consultar los flujos de efectivo registrados</p>
    <form method="post" class="pure-form">
      <input type="number" step="1" min="1" max=<?=$max?> placeholder="Folio" name="folio" required>
      <input type="submit" value="Buscar" name="buscar">
    </form>
  </body>
</html>

<?php
if(isset($_POST["buscar"])){
  $stmtBuscar = $enlace->prepare("SELECT * FROM flujo_efectivo WHERE folio_flujo = ?");
  $stmtBuscar->bind_param("i", $_POST["folio"]);
  $stmtBuscar->execute();
  $tuplaFlujo = $stmtBuscar->get_result()->fetch_assoc();
  
  $stmtNombreEmpleado = $enlace->prepare("SELECT nombre_empleado FROM empleado WHERE rfc_empleado = ?");
  $stmtNombreEmpleado->bind_param("s", $tuplaFlujo["id_empleado"]);
  $stmtNombreEmpleado->execute();
  $tuplaNombreEmpleado = $stmtNombreEmpleado->get_result()->fetch_assoc();
  ?>
  <table class="pure-table">
    <thead>
      <legend>Tabla de Información de Flujo de Efectivo</legend><br>
      <tr>
        <th>Folio de Flujo</th>
        <th>Fecha</th>
        <th>Hora</th>
        <th>RFC Empleado</th>
        <th>Nombre Empleado</th>
        <th>Monto</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?=$_POST["folio"]?></td>
        <td><?=$tuplaFlujo["fecha"]?></td>
        <td><?=$tuplaFlujo["hora"]?></td>
        <td><?=$tuplaFlujo["id_empleado"]?></td>
        <td><?=$tuplaNombreEmpleado["nombre_empleado"]?></td>
        <td><?=$tuplaFlujo["monto"]?></td>
      </tr>
    </tbody>
  </table>

<?php
}
?>