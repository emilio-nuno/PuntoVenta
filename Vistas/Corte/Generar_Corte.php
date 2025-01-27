<?php
session_start();
require("PDF/tables.php");

function agregarTitulo($titulo, $pdf){
  $header = [$titulo];
  $data = [];
  
  $pdf->BasicTable($header,$data);
  $pdf->Ln();
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',14);

$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}

$fecha = date("Y-m-d");
$rfc_emp = "101"; //rfc del empleado

$stmtConseguirInfoVentas = $enlace->prepare("CREATE TEMPORARY TABLE desglose_dia SELECT folio_venta, clave_producto, cantidad, valor_unitario, valor_unitario * cantidad as total FROM detalle_venta WHERE folio_venta IN (SELECT folio_venta FROM venta WHERE (DATEDIFF(fecha_venta, ?) = 0 AND rfc_empleado = ?))");
$stmtConseguirInfoVentas->bind_param("ss", $fecha, $rfc_emp);
$stmtConseguirInfoVentas->execute();

$stmtConsultarTotalVenta = $enlace->prepare("SELECT folio_venta, SUM(total) as monto_venta FROM desglose_dia WHERE folio_venta = ?");
$stmtConsultarTotalVenta->bind_param("i", $folioVenta);

$stmtConseguirFoliosDia = $enlace->prepare("SELECT folio_venta, metodo_pago, iva FROM venta WHERE (DATEDIFF(fecha_venta, ?) = 0 AND rfc_empleado = ?)"); //generamos los folios del día, para iterar sobre ellos
$stmtConseguirFoliosDia->bind_param("ss", $fecha, $rfc_emp);
$stmtConseguirFoliosDia->execute();

$resultadoFolios = $stmtConseguirFoliosDia->get_result();

$stmtConseguirInfoVentas->close();
$stmtConseguirFoliosDia->close();

$totalVentasEfectivo = 2000;
$totalVentasCredito = 0;
$totalDevoluciones = 0;
$totalFlujos = 0;
?>
  
<?php
  //IMPPRIMIR TOTAL DE VENTAS
  agregarTitulo("Ventas", $pdf);

  $header = array("Folio", "Total de Venta", "Metodo de Pago");
  $data = [];
  
  while($tuplaFolio = $resultadoFolios->fetch_assoc()){ 
      $folioVenta = $tuplaFolio["folio_venta"];
      $stmtConsultarTotalVenta->execute();
      $resultadoTotal = $stmtConsultarTotalVenta->get_result();
      $tuplaVenta = $resultadoTotal->fetch_assoc();
    
      $iva = $tuplaFolio["iva"];
    
      if($tuplaFolio["metodo_pago"] == "efectivo"){
          $totalVentasEfectivo += ($tuplaVenta["monto_venta"] + ($tuplaVenta["monto_venta"] * $iva)); 
      }
      else{
          $totalVentasCredito += ($tuplaVenta["monto_venta"] + ($tuplaVenta["monto_venta"] * $iva)); 
      }
    
      $data[] = [$tuplaVenta["folio_venta"], $tuplaVenta["monto_venta"] + ($tuplaVenta["monto_venta"] * $iva), ucfirst($tuplaFolio["metodo_pago"])];
  }
  
  $data[] = ["Total Efectivo", $totalVentasEfectivo];
  $data[] = ["Total Credito", $totalVentasCredito];
  
  $pdf->BasicTable($header,$data);
  $pdf->Ln();

  unset($data);

  $stmtConsultarTotalVenta->close();

  $stmtFoliosDevolucion = $enlace->prepare("CREATE TEMPORARY TABLE devoluciones_dia SELECT folio_devolucion, clave_producto as clave, cantidad, cantidad * (SELECT precio FROM producto WHERE clave_producto = clave) as monto_total FROM detalle_devolucion WHERE folio_devolucion IN(SELECT folio_devolucion FROM devolucion WHERE (DATEDIFF(fecha, ?) = 0 AND rfc_empleado = ?))");
  $stmtFoliosDevolucion->bind_param("ss", $fecha, $rfc_emp);
  $stmtFoliosDevolucion->execute();
  $stmtFoliosDevolucion->close(); //creamos la tabla
  
  $stmtConseguirFolios = $enlace->prepare("SELECT folio_devolucion FROM devolucion WHERE (DATEDIFF(fecha, ?) = 0 AND rfc_empleado = ?)");
  $stmtConseguirFolios->bind_param("ss", $fecha, $rfc_emp);
  $stmtConseguirFolios->execute();
  $foliosDevolucion = $stmtConseguirFolios->get_result();
  $stmtConseguirFolios->close();

  $stmtTotalFolio = $enlace->prepare("SELECT folio_devolucion, SUM(monto_total) as monto_devolucion FROM devoluciones_dia WHERE folio_devolucion = ?");
  $stmtTotalFolio->bind_param("i", $folioDevolucion);

  $stmtIvaVentaAsociada = $enlace->prepare("SELECT iva FROM venta WHERE folio_venta IN (SELECT folio_venta FROM devolucion WHERE folio_devolucion = ?)");
  $stmtIvaVentaAsociada->bind_param("i", $folioDevolucion);

  //IMPRIMIR TOTAL DEVOLUCIONES
  agregarTitulo("Devoluciones", $pdf);

  $header = array("Folio", "Total Devolucion");
  $data = [];

  while($tuplaFolio = $foliosDevolucion->fetch_assoc()){
      $folioDevolucion = $tuplaFolio["folio_devolucion"];
      $stmtTotalFolio->execute();
      $resultadoTotal = $stmtTotalFolio->get_result();
      $tuplaDevolucion = $resultadoTotal->fetch_assoc();
      
      $stmtIvaVentaAsociada->execute();
      $iva = $stmtIvaVentaAsociada->get_result()->fetch_assoc()["iva"];
    
      $totalDevoluciones += ($tuplaDevolucion["monto_devolucion"] + ($tuplaDevolucion["monto_devolucion"] * $iva));
    
      $data[] = [$tuplaDevolucion["folio_devolucion"], $tuplaDevolucion["monto_devolucion"] + ($tuplaDevolucion["monto_devolucion"] * $iva)];
  }  

  $data[] = ["Total", $totalDevoluciones];

  $pdf->BasicTable($header,$data);
  $pdf->Ln();

  unset($data);

  $stmtTotalFolio->close();
  $stmtConseguirFlujos = $enlace->prepare("SELECT folio_flujo, monto, hora FROM flujo_efectivo WHERE (DATEDIFF(fecha, ?) = 0 AND id_empleado = ?)");
  $stmtConseguirFlujos->bind_param("ss", $fecha, $rfc_emp);
  $stmtConseguirFlujos->execute();
  $foliosFlujo = $stmtConseguirFlujos->get_result(); //para flujos es una simple consulta
  $stmtConseguirFlujos->close();

  //IMPRIMIR TOTAL FLUJOS
  agregarTitulo("Flujos de Efectivo", $pdf);
  
  $header = array("Folio", "Monto", "Hora");
  $data = [];

  while($tuplaFolio = $foliosFlujo->fetch_assoc()){ 
      $totalFlujos += $tuplaFolio["monto"];
      $data[] = [$tuplaFolio["folio_flujo"], $tuplaFolio["monto"], $tuplaFolio["hora"]];
  }

  $data[] = ["Total", $totalFlujos];

  $pdf->BasicTable($header,$data);
  $pdf->Ln();

  unset($data);

  $data = [];
  
  $header = array("Fecha del Corte", "Hora del Corte");
  $data[] = [$fecha, date("H:i")];

  $pdf->BasicTable($header,$data);
  $pdf->Ln();

  unset($data);

  $data = [];

  $header = array("Efectivo al Corte", "Vouchers al Corte");
  $data[] = [$totalVentasEfectivo - $totalDevoluciones - $totalFlujos, $totalVentasCredito];

  $pdf->BasicTable($header,$data);
  $pdf->Ln();

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

  unset($data);

  $data = [];

  $header = array("Entrega", "Recibe");
  $data[] = [$nombres[0], $nombres[1]];

  $pdf->BasicTable($header,$data);

  $pdf->Output('F', "corte.pdf");
?>