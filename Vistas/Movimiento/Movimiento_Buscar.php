<?php
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

$stmtFolioMovimientoMax = $enlace->prepare("SELECT folio_movimiento FROM movimiento_almacen ORDER BY folio_movimiento DESC LIMIT 1"); //Verificamos el valor máximo de un folio
$stmtFolioMovimientoMax->execute();
$resultado = $stmtFolioMovimientoMax->get_result();
if($resultado->num_rows == 0){
  echo "No puede consultar porque no hay ninguna devolución registrada... Intente después";
  //header("Location: Inicio_Devolucion.php"); //Probablemente iremos a algún menú que tendremos luego
  exit();
}
else{
  $tupla = $resultado->fetch_assoc();
  $max = $tupla["folio_movimiento"];
}
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  </head>
  <body>
    <h1>Consulta de Movimientos</h1>
    <p>En este sitio podrás consultar los movimientos de almacén registrados</p>
    <form method="post" class="pure-form">
      <input type="number" step="1" min="1" max=<?=$max?> placeholder="Folio" name="folio" required>
      <input type="submit" value="Buscar" name="buscar">
    </form>
  </body>
</html>

<?php
if(isset($_POST["buscar"])){
  $stmtBuscar = $enlace->prepare("SELECT * FROM movimiento_almacen WHERE folio_movimiento = ?");
  $stmtBuscar->bind_param("i", $_POST["folio"]);
  $stmtBuscar->execute();
  $tuplaDevolucion = $stmtBuscar->get_result()->fetch_assoc();
  ?>
  <table class="pure-table">
    <thead>
      <legend>Tabla de Información de Movimiento</legend><br>
      <tr>
        <th>Folio de Movimiento</th>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>RFC Empleado</th>
        <th>Motivo</th>
        <th>Folio Generado</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?=$_POST["folio"]?></td>
        <td><?=$tuplaDevolucion["fecha"]?></td>
        <td><?=$tuplaDevolucion["tipo"]?></td>
        <td><?=$tuplaDevolucion["id_empleado"]?></td>
        <td><?=$tuplaDevolucion["motivo"]?></td>
        <td><?=$tuplaDevolucion["folio_generador"]?></td>
      </tr>
    </tbody>
  </table>

<?php
}
?>