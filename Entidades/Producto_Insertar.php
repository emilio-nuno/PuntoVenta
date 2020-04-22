<?php
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
        <meta charset="utf-8">
        <title>Formulario de Registro de Productos</title>
        <link rel="stylesheet" type="text/css" href="../Estilos/estilos.css">
    </head>
    <body>
        <a href="../Menu.html">Regresar</a>
        <div class="contenedor">
            <form class="formulario" id = "formulario" name = "formulario" method="post">
                <div class = "datos-entrada">
                    <input type="text" name = "nombre" placeholder="Nombre">
                    <input type="text" name = "descripcion" placeholder="Descripcion">
                    <input type="text" name = "cantidad" placeholder="Cantidad">
                    <input type="text" name = "departamento" placeholder="Departamento">
                    <input type="text" name = "stock_max" placeholder="Stock Maximo">
                    <input type="text" name = "stock_min" placeholder="Stock Minimo">
                    <input type="text" name = "precio" placeholder="Precio">
                </div>
                <input type="submit" class="btn" name="registrarse" value="Registrar">
            </form>
        </div>
    </body>
</html>

<?php
if(isset($_POST['registrarse'])){
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $cantidad = $_POST["cantidad"];
    $departamento = $_POST["departamento"];
    $stock_max = $_POST["stock_max"];
    $stock_min = $_POST["stock_min"];
    $precio = $_POST["precio"];
    
    $insertarDatos = "INSERT INTO producto(nombre, descripcion, cantidad, departamento, stock_max, stock_min, status, precio) VALUES('$nombre', '$descripcion', '$cantidad', '$departamento', '$stock_max', '$stock_min', default, '$precio')";
    
    $ejecutarInsertar = mysqli_query($enlace, $insertarDatos);
    
    if(!$ejecutarInsertar){
        echo "Error en la linea de sql";
    }
}
?>