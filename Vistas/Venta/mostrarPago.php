<?php
    session_start();
    //Inicializar variables para conexión a BD
    $servidor="localhost";
    $usuario="root";
    $clave="";
    $baseDeDatos="formulario";

    $enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

    if(!$enlace){
        echo "Error en la conexion del servidor";
    }
        
    $fecha = date("Y-m-d");

    $consultarDatos = "SELECT porcentaje FROM iva ORDER BY ABS(DATEDIFF(fecha, '$fecha')) LIMIT 1";
    $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
    $row = mysqli_fetch_array($ejecutarConsultar);

    $porcentaje =  $row["porcentaje"];
    $monto = 0;
    $iva = 0;
    $total = 0;

    foreach($_SESSION["orden"] as $id=>$info){
        $monto += ($_SESSION["orden"][$id]["precio"] * $_SESSION["orden"][$id]["cantidad"]);
    }
    
    $iva = $monto * $porcentaje;
    $total = $iva + $monto;
?>

<p>Monto: <?=$monto?></p>
<p>IVA: <?=$iva?></p>
<p>Total: <?=$total?></p>