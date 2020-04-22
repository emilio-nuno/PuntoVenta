<?php
/*TODO
*Agregar funcionalidad de dinero en caja para motivos de  devoluciones
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

<form class="pure-form" method="post">
    <input type="text" class="pure-input-rounded" placeholder="Departamento" name="busqueda">
    <button type="submit" class="pure-button" name="buscar" value="buscando">Buscar</button>
</form>
    
<?php
    if(isset($_POST['confirmar'])){
        //venta: folio que es autoincrementable, fecha, que conseguiremos en php, rfc de empleado y rfc de cliente, se registra solo una
        //detalle_Venta: folio, clave de producto, cantidad, valor unitario e iva crear uno para cada producto
        $consultarDatos = "SELECT folio_venta FROM venta ORDER BY folio_venta DESC LIMIT 1";
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
        $row = mysqli_fetch_array($ejecutarConsultar);
        
        $folio = $row["folio_venta"]; //se guarda como cadena, entonces lo convertimos a int
        $folio = (int)$folio;
        $folio += 1;
        
        $fecha = date("Y-m-d");
        $rfc_emp = $_SESSION["empleado"];
        $rfc_cli = $_SESSION["cliente"];
        
        $consultarDatos = "INSERT INTO venta(fecha_venta, rfc_empleado, id_cliente) values ('$fecha', '$rfc_emp', '$rfc_cli')"; //registramos venta
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
        
        $consultarDatos = "SELECT porcentaje FROM iva ORDER BY ABS(DATEDIFF(fecha, '$fecha')) LIMIT 1"; //conseguimos el porcentaje de IVA de la fecha más cercana a la actual
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
        $row = mysqli_fetch_array($ejecutarConsultar);

        $porcentaje =  $row["porcentaje"];
        
        foreach($_SESSION["orden"] as $id=>$info){
            $clave_producto = $id;
            $cantidad_producto = $_SESSION["orden"][$id]["cantidad"];
            $valor_unitario = $_SESSION["orden"][$id]["precio"];
            
            $consultarDatos = "INSERT INTO detalle_venta(folio_venta,  clave_producto, cantidad, valor_unitario, iva) values ($folio, $id, $cantidad_producto, $valor_unitario, $porcentaje)"; //registramos un detalle por cada producto en la canasta
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
        
        header("Location: VerificarCliente.php");
        exit();
    }
?>
    
<?php
    if(isset($_POST['buscar'])){
        $criterioBuscar = $_POST['busqueda'];
        $consultarDatos = "SELECT * FROM producto where departamento = '$criterioBuscar'";
        $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
?>

<table class="pure-table" id="productos">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Stock</th>
            <th>Departamento</th>
            <th>Precio</th>
        </tr>
    </thead>
    <tbody>
        <tr>
        <?php
        while($row = mysqli_fetch_array($ejecutarConsultar)) {
        ?>
        <tr>
            <td><?=$row["clave_producto"]?></td>
            <td><?=$row["nombre"]?></td>
            <td><?=$row["descripcion"]?></td>
            <td><?=$row["cantidad"]?></td>
            <td><?=$row["departamento"]?></td>
            <td><?=$row["precio"]?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
<?php
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