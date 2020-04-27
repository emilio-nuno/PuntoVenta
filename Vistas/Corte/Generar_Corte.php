<?php
session_start();
/*TODO:
*Agregar tabla de metodo de pago a venta
*/

$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

//$fecha = date("Y-m-d");
$fecha = "2020-04-23"; //solo para probar, reemplazar por fecha actual
$folio = 12; //solo para hacer testing

$stmtConseguirInfoVentas = $enlace->prepare("CREATE TEMPORARY TABLE desglose_dia SELECT folio_venta, clave_producto, cantidad, valor_unitario, valor_unitario * cantidad as total FROM detalle_venta WHERE folio_venta IN (SELECT folio_venta FROM venta WHERE DATEDIFF(fecha_venta, ?) = 0)");
$stmtConseguirInfoVentas->bind_param("s", $fecha);
$stmtConseguirInfoVentas->execute();

$stmtConsultarTotalVenta = $enlace->prepare("SELECT folio_venta, SUM(total) as monto_venta FROM desglose_dia WHERE folio_venta = ?");
$stmtConsultarTotalVenta->bind_param("i", $folio);

$stmtConseguirFoliosDia = $enlace->prepare("SELECT folio_venta FROM venta WHERE DATEDIFF(fecha_venta, ?) = 0"); //generamos los folios del día, para iterar sobre ellos
$stmtConseguirFoliosDia->bind_param("s", $fecha);
$stmtConseguirFoliosDia->execute();

$resultadoFolios = $stmtConseguirFoliosDia->get_result();

$stmtConseguirInfoVentas->close();
$stmtConseguirFoliosDia->close();

$totalVentas = 0;
$totalDevoluciones = 0;
?>

<DOCTYPE !html>
<html>
  <head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
    <title>Corte de Caja</title>
  </head>
  <body>
    <h1>Corte de Caja</h1>
    <h2>Bienvenido al corte de caja! Puede consultar la información del día a continuación:</h2>
  </body>
</html>
  
<table class="pure-table">
    <thead>
      <legend>Ventas</legend><br>
        <tr>
            <th>Folio</th>
            <th>Monto de Venta</th>
            <th>Método de Pago</th>
        </tr>
    </thead>

    <tbody>
      <?php while($tuplaFolio = $resultadoFolios->fetch_assoc()){ 
              $folio = $tuplaFolio["folio_venta"];
              $stmtConsultarTotalVenta->execute();
              $resultadoTotal = $stmtConsultarTotalVenta->get_result();
              $tuplaVenta = $resultadoTotal->fetch_assoc();
              $totalVentas += $tuplaVenta["monto_venta"];
      ?>
      <tr>
        <td><?=$tuplaVenta["folio_venta"]?></td>
        <td><?=$tuplaVenta["monto_venta"]?></td>
        <td>Efectivo</td>
      </tr>
      <?php } ?>
    </tbody>
  
    <tfoot>
      <th>Total</th>
      <td><?=$totalVentas?></td>
    </tfoot>
</table>

<?php
$stmtConsultarTotalVenta->close();

//vamos a reutilizar $resultadoFolios
echo "Aqui van las devoluciones..";
?>