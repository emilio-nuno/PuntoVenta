<?php
    session_start();

    if(empty($_SESSION["orden"])){
        $_SESSION["orden"] = [];
    }

    $servidor="localhost";
    $usuario="root";
    $clave="";
    $baseDeDatos="formulario";
    $numColumnas = 9;

    $enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

    if(!$enlace){
        echo "Error en la conexion del servidor";
    }

    $criterioBuscar = $_POST['idProducto'];
    $consultarDatos = "SELECT * FROM producto where clave_producto = '$criterioBuscar'";
    $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
    $row = mysqli_fetch_array($ejecutarConsultar);

    $idProducto = $_POST['idProducto'];
    $cantidadProducto = $_POST['cantidadProducto'];

    if(empty($row)){
        echo "No se ha encontrado el producto solicitado";
        exit();
    }

    if($cantidadProducto > $row["cantidad"]){
        echo "No hay existencias suficientes para cubrir esa orden";
        exit();
    }

    if(array_key_exists($idProducto, $_SESSION["orden"])){
        if($cantidadProducto == 0){
            unset($_SESSION["orden"][$idProducto]);
        }
        else{
            $_SESSION["orden"][$idProducto]["cantidad"] = $cantidadProducto; 
            $_SESSION["orden"][$idProducto]["precio"] = $row["precio"];
            $_SESSION["orden"][$idProducto]["nombre"] = $row["nombre"];
            $_SESSION["orden"][$idProducto]["descripcion"] = $row["descripcion"];
        }
    }
    else{
        if($cantidadProducto != 0){
            $_SESSION["orden"][$idProducto]["cantidad"] = $cantidadProducto;
            $_SESSION["orden"][$idProducto]["precio"] = $row["precio"];
            $_SESSION["orden"][$idProducto]["nombre"] = $row["nombre"];
            $_SESSION["orden"][$idProducto]["descripcion"] = $row["descripcion"];
        }
        else{
            echo "No se pueden agregar 0 productos del id " . $idProducto . " al carrito"; 
        }
    }
?>

<table class="pure-table" id="productos">
    <thead>
        <legend>Carrito de Compras</legend>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
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
            <td><?=$_SESSION["orden"][$id]["nombre"]?></td>
            <td><?=$_SESSION["orden"][$id]["descripcion"]?></td>
            <td><?=$_SESSION["orden"][$id]["cantidad"]?></td>
            <td><?=$_SESSION["orden"][$id]["precio"]?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>