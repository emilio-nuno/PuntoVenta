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
  
<?php
$folio_venta = $_SESSION["folio_venta"];
$rfc_emp = $_SESSION["empleado"];
$rfc_cli = $_SESSION["cliente"];
$fecha = date("Y-m-d");

$stmtIva = $enlace->prepare("SELECT porcentaje FROM iva ORDER BY ABS(DATEDIFF(fecha, ?)) LIMIT 1"); //conseguimos el porcentaje de IVA de la fecha más cercana a la actual
$stmtIva->bind_param("s", $fecha);
$stmtIva->execute();
$resultado = $stmtIva->get_result();
$row = $resultado->fetch_assoc();
$porcentaje =  $row["porcentaje"];
  
echo "El RFC del empleado que le atendió es: " . $rfc_emp . "<br>";
echo "El RFC del cliente es: " . $rfc_cli . "<br>";
echo "El folio de la venta actual es: " . $folio_venta . "<br>";
echo "El porcentaje de IVA actual es: " . $porcentaje . "<br>";
echo "La fecha actual es: " . date("d-m-Y") . "<br><br>";
?>
  
<table class="pure-table" id="productos">
    <thead>
        <legend>Productos Comprados</legend><br>
        <tr>
            <th>ID</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
        </tr>
    </thead>
    <tbody>
        <tr>
        <?php
        foreach($_SESSION["orden"] as $id=>$info){
        ?>
        <tr>
            <td><?=$id?></td>
            <td><?=$_SESSION["orden"][$id]["cantidad"]?></td>
            <td><?=$_SESSION["orden"][$id]["precio"]?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
    <title>Hola mundo!</title>
  </head>
  <body>
    <form method="post">
      <input type="submit" name="terminar" value="Salir de la Venta" class="pure-button pure-button-primary">
    </form>
  </body>
</html>
  
<?php
if(isset($_POST["terminar"])){
  header("Location: ../../Pantallas/Vendedor.php");
  exit();
}  
?>