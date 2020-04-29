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

$fecha = date("Y-m-d");
//$fecha = "2020-04-23"; //solo para probar, reemplazar por fecha actual

$stmtConseguirInfoVentas = $enlace->prepare("CREATE TEMPORARY TABLE desglose_dia SELECT folio_venta, clave_producto, cantidad, valor_unitario, valor_unitario * cantidad as total FROM detalle_venta WHERE folio_venta IN (SELECT folio_venta FROM venta WHERE DATEDIFF(fecha_venta, ?) = 0)");
$stmtConseguirInfoVentas->bind_param("s", $fecha);
$stmtConseguirInfoVentas->execute();

$stmtConsultarTotalVenta = $enlace->prepare("SELECT folio_venta, SUM(total) as monto_venta FROM desglose_dia WHERE folio_venta = ?");
$stmtConsultarTotalVenta->bind_param("i", $folioVenta);

$stmtConseguirFoliosDia = $enlace->prepare("SELECT folio_venta FROM venta WHERE DATEDIFF(fecha_venta, ?) = 0"); //generamos los folios del día, para iterar sobre ellos
$stmtConseguirFoliosDia->bind_param("s", $fecha);
$stmtConseguirFoliosDia->execute();

$resultadoFolios = $stmtConseguirFoliosDia->get_result();

$stmtConseguirInfoVentas->close();
$stmtConseguirFoliosDia->close();

$totalVentas = 0;
$totalDevoluciones = 0;
$totalFlujos = 0;
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
              $folioVenta = $tuplaFolio["folio_venta"];
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

$stmtFoliosDevolucion = $enlace->prepare("CREATE TEMPORARY TABLE devoluciones_dia SELECT folio_devolucion, clave_producto as clave, cantidad, cantidad * (SELECT precio FROM producto WHERE clave_producto = clave) as monto_total FROM detalle_devolucion WHERE folio_devolucion IN(SELECT folio_devolucion FROM devolucion WHERE DATEDIFF(fecha, ?) = 0 )");
$stmtFoliosDevolucion->bind_param("s", $fecha);
$stmtFoliosDevolucion->execute();
$stmtFoliosDevolucion->close(); //creamos la tabla
  
$stmtConseguirFolios = $enlace->prepare("SELECT folio_devolucion FROM devolucion WHERE DATEDIFF(fecha, ?) = 0");
$stmtConseguirFolios->bind_param("s", $fecha);
$stmtConseguirFolios->execute();
$foliosDevolucion = $stmtConseguirFolios->get_result();
$stmtConseguirFolios->close();

$stmtTotalFolio = $enlace->prepare("SELECT folio_devolucion, SUM(monto_total) as monto_devolucion FROM devoluciones_dia WHERE folio_devolucion = ?");
$stmtTotalFolio->bind_param("i", $folioDevolucion);
?>
  
<table class="pure-table">
    <thead>
      <br><legend>Devoluciones</legend><br>
        <tr>
            <th>Folio</th>
            <th>Monto Devolución</th>
        </tr>
    </thead>

    <tbody>
      <?php while($tuplaFolio = $foliosDevolucion->fetch_assoc()){ 
              $folioDevolucion = $tuplaFolio["folio_devolucion"];
              $stmtTotalFolio->execute();
              $resultadoTotal = $stmtTotalFolio->get_result();
              $tuplaDevolucion = $resultadoTotal->fetch_assoc();
              $totalDevoluciones += $tuplaDevolucion["monto_devolucion"];
      ?>
      <tr>
        <td><?=$tuplaDevolucion["folio_devolucion"]?></td>
        <td><?=$tuplaDevolucion["monto_devolucion"]?></td>
      </tr>
      <?php } ?>
    </tbody>
  
    <tfoot>
      <th>Total</th>
      <td><?=$totalDevoluciones?></td>
    </tfoot>
</table>
  
<?php
$stmtTotalFolio->close();

$stmtConseguirFlujos = $enlace->prepare("SELECT folio_flujo, monto, hora FROM flujo_efectivo WHERE DATEDIFF(fecha, ?) = 0 ");
$stmtConseguirFlujos->bind_param("s", $fecha);
$stmtConseguirFlujos->execute();
    
$foliosFlujo = $stmtConseguirFlujos->get_result(); //para flujos es una simple consulta
        
$stmtConseguirFlujos->close();
?>
  
<table class="pure-table">
    <thead>
      <br><legend>Flujo Efectivo</legend><br>
        <tr>
            <th>Folio</th>
            <th>Monto</th>
            <th>Hora</th>
        </tr>
    </thead>

    <tbody>
      <?php while($tuplaFolio = $foliosFlujo->fetch_assoc()){ 
              $totalFlujos += $tuplaFolio["monto"];
      ?>
      <tr>
        <td><?=$tuplaFolio["folio_flujo"]?></td>
        <td><?=$tuplaFolio["monto"]?></td>
        <td><?=$tuplaFolio["hora"]?></td>
      </tr>
      <?php } ?>
    </tbody>
  
    <tfoot>
      <th>Total</th>
      <td><?=$totalFlujos?></td>
    </tfoot>
</table>

<p><strong>TOTAL VOUCHERS: <?=$totalVentas - $totalDevoluciones?></strong></p>
<p><strong>TOTAL EFECTIVO: <?=$totalVentas - $totalDevoluciones - $totalFlujos?></strong></p>
  
<?php
  $rfc_emp = "1234567890121"; //cambiar estos a valores dados por $_SESSION[]
  $rfc_geren = $_SESSION["empleado"]; //aqui guardamos el valor del rfc actual y solo pueden entrar a esta vista los gerentes
        
  $rfcs = [$rfc_emp, $rfc_geren];
  $nombres = [];
        
  $stmtConseguirNombreEmpleado = $enlace->prepare("SELECT nombre_empleado FROM empleado WHERE rfc_empleado = ?");
  $stmtConseguirNombreEmpleado->bind_param("s", $rfc);
        
  foreach($rfcs as $valor){
    $rfc = $valor;
    $stmtConseguirNombreEmpleado->execute();
    $resultadoNombre = $stmtConseguirNombreEmpleado->get_result();
    $tuplaNombre = $resultadoNombre->fetch_assoc();
    array_push($nombres, $tuplaNombre["nombre_empleado"]);
  }

  $stmtConseguirNombreEmpleado->close();
?>
  
<p style="display: inline-block;
    vertical-align: top; width: 10%"><?=$nombres[0]?><br>Nombre Empleado<br>Entrega</p>
<p style="display: inline-block;
    vertical-align: top;"><?=$nombres[1]?><br>Nombre Gerente<br>Recibe</p>