<?php
/*TODO
*Agregar funcionalidad de dinero en caja para motivos de  devoluciones
*Agregar columna descuento para calcular el descuento
*/
    session_start();

    $servidor="localhost";
    $usuario="root";
    $clave="";
    $baseDeDatos="formulario";
    $numColumnas = 9;

    $enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

    if(!$enlace){
        echo "Error en la conexion del servidor";
    }
?>

 <!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="carrito.js"></script>
</head>
<body>

<div style="position:fixed; left:1000px;" id="infoPago" class="pure-form">
    <p>Aquí se muestra el precio</p>
    <p>Aquí se muestra el IVA</p>
    <p>Aquí se muestra el total + IVA</p>
</div>
    
<?php
    if(isset($_POST['confirmar'])){
        $folio = 0;
        //venta: folio que es autoincrementable, fecha, que conseguiremos en php, rfc de empleado y rfc de cliente, se registra solo una
        //detalle_Venta: folio, clave de producto, cantidad, valor unitario e iva crear uno para cada producto
        $consultarDatos = "SELECT folio_venta FROM venta ORDER BY folio_venta DESC LIMIT 1";
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
      
        $rows = mysqli_num_rows($ejecutarConsultar);
        if($rows == 0){
          $folio = 1;
        }
        else{
          $row = mysqli_fetch_array($ejecutarConsultar);
          $folio = $row["folio_venta"]; //se guarda como cadena, entonces lo convertimos a int
          $folio = (int)$folio;
          $folio += 1;
        }
        
        $_SESSION["folio_venta"] = $folio;
    
        $fecha = date("Y-m-d");
        $rfc_emp = $_SESSION["empleado"];
        $rfc_cli = $_SESSION["cliente"];
      
        $consultarDatos = "SELECT porcentaje FROM iva ORDER BY ABS(DATEDIFF(fecha, '$fecha')) LIMIT 1"; //conseguimos el porcentaje de IVA de la fecha más cercana a la actual
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
        $row = mysqli_fetch_array($ejecutarConsultar);
        $porcentaje =  $row["porcentaje"];
        
        $consultarDatos = "INSERT INTO venta(fecha_venta, rfc_empleado, id_cliente, iva) values ('$fecha', '$rfc_emp', '$rfc_cli', $porcentaje)"; //registramos venta
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
        
        foreach($_SESSION["orden"] as $id=>$info){
            $clave_producto = $id;
            $cantidad_producto = $_SESSION["orden"][$id]["cantidad"];
            $valor_unitario = $_SESSION["orden"][$id]["precio"];
            
            $consultarDatos = "INSERT INTO detalle_venta(folio_venta,  clave_producto, cantidad, valor_unitario) values ($folio, $id, $cantidad_producto, $valor_unitario)"; //registramos un detalle por cada producto en la canasta
            $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
            
            $consultarDatos = "SELECT cantidad FROM producto WHERE clave_producto = $id"; //consultamos el stock de cada producto 
            $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
            $row = mysqli_fetch_array($ejecutarConsultar);
            $cantidadNueva = $row["cantidad"];
            $cantidadNueva -= $cantidad_producto;
            
            $consultarDatos = "UPDATE producto SET cantidad = $cantidadNueva WHERE clave_producto = $id"; //actualizamos el stock de los productos para que reflejen la venta
            $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
            $row = mysqli_fetch_array($ejecutarConsultar);
        }
        
        header("Location: Registro_Venta.php");
        exit();
    }
?>
    
<form class="pure-form" method="post" id="miFormulario">
    <fieldset>

        <input type="text" placeholder="ID del producto" name="idProducto" id="idProducto">
        <input type="text" placeholder="Cantidad Deseada" name="cantidadProducto" id="cantidadProducto">

        <button type="button" class="pure-button pure-button-primary" onclick="GenerarCarrito();">Agregar a Carrito</button>
    </fieldset>
</form>
    
<div id="resultados">
    
</div>
    
<form method="post">
        <input type="submit" class="pure-button" name="confirmar" value="Confirmar">
</form>
    
</body>
</html> 