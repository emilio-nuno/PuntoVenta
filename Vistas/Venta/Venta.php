<?php
/*TODO
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
    
    $stmtInfoEmpleado = $enlace->prepare("SELECT nombre_empleado FROM empleado WHERE rfc_empleado = ?");
    $stmtInfoEmpleado->bind_param("s" ,$_SESSION["empleado"]);
    $stmtInfoEmpleado->execute();

    $infoEmpleado = $stmtInfoEmpleado->get_result();
    
    $tuplaInfoEmpleado= $infoEmpleado->fetch_assoc();
    $nomEmpleado = $tuplaInfoEmpleado["nombre_empleado"];
    
    $stmtInfoCliente = $enlace->prepare("SELECT nombre, domicilio FROM cliente WHERE rfc = ?");
    $stmtInfoCliente->bind_param("s" ,$_SESSION["cliente"]);
    $stmtInfoCliente->execute();

    $infoCliente = $stmtInfoCliente->get_result();
    
    $tuplaInfoCliente = $infoCliente->fetch_assoc();
    $nomCliente = $tuplaInfoCliente["nombre"];
    $dirCliente = $tuplaInfoCliente["domicilio"];
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
  if(isset($_POST["cancelar"])){
    header("Location: ../../Pantallas/Vendedor.php");
    exit();
  }
?>

<?php
    if(isset($_POST['confirmar'])){ 
        $_SESSION["folio_venta"] = $folio;
    
        $fecha = date("Y-m-d");
        $rfc_emp = $_SESSION["empleado"];
        $rfc_cli = $_SESSION["cliente"];
      
        $metodo = $_POST["metodo"];
      
        $consultarDatos = "SELECT porcentaje FROM iva ORDER BY ABS(DATEDIFF(fecha, '$fecha')) LIMIT 1"; //conseguimos el porcentaje de IVA de la fecha más cercana a la actual
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
        $row = mysqli_fetch_array($ejecutarConsultar);
        $porcentaje =  $row["porcentaje"];
        
        $consultarDatos = "INSERT INTO venta(fecha_venta, rfc_empleado, id_cliente, iva, metodo_pago) values ('$fecha', '$rfc_emp', '$rfc_cli', $porcentaje, '$metodo')"; //registramos venta
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
      
        $dineroGenerado = 0; //con esto aumentaremos la cantidad de dinero total en caja
        
        foreach($_SESSION["orden"] as $id=>$info){
            $clave_producto = $id;
            $cantidad_producto = $_SESSION["orden"][$id]["cantidad"];
            $valor_unitario = $_SESSION["orden"][$id]["precio"];
          
            
            for($i = 0; $i < $cantidad_producto; $i++){
              $dineroGenerado += $valor_unitario;
            }

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
        
        if($metodo != "credito"){
          $_SESSION["dinero_caja"] += $dineroGenerado; //solo modificamos el valor de efectivo en caja cuando la venta se hace con efectivo 
        }
      
        header("Location: Registro_Venta.php");
        exit();
    }
?>
  
<p>Le atiende: <strong><?=$nomEmpleado?></strong></p>
  
<p>Folio de la venta actual: <?=$folio?></p>
<p>Fecha actual: <?=date("Y-m-d")?></p>
<p>RFC de Cliente: <?=$_SESSION["cliente"]?></p>
<p>Nombre del Cliente: <?=$nomCliente?></p>
<p>Domicilio del Cliente: <?=$dirCliente?></p>

<form class="pure-form" method="post" id="miFormulario">
    <fieldset>

        <input type="text" placeholder="ID del producto" name="idProducto" id="idProducto">
        <input type="text" placeholder="Cantidad Deseada" name="cantidadProducto" id="cantidadProducto">

        <button type="button" class="pure-button pure-button-primary" onclick="GenerarCarrito();">Agregar a Carrito</button>
    </fieldset>
</form>
    
<div id="resultados">
    
</div>
    
<form method="post"> <!--Tal vez pasar esta funcionalidad a otro lugar-->
    <label for="metodoEfectivo">Efectivo</label>
    <input id="metodoEfectivo" name="metodo" type="radio" value="efectivo">
    <label for="metodoCredito">Crédito</label>
    <input id="metodoCredito" name="metodo" type="radio" value="credito"><br>
    <input type="submit" class="pure-button" name="confirmar" value="Confirmar">
    <input type="submit" class="pure-button" name="cancelar" value="Cancelar">
</form>
    
</body>
</html> 