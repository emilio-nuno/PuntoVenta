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
                <input type="submit" class="btn" name="consultar" value="Mostrar Todos">
                <select name="columna">
                    <option value="nombre">Nombre</option>
                    <option value="departamento">Departamento</option>
                </select>
                <div class = "datos-entrada">
                    <input type="text" name = "busqueda" placeholder="Valor para busqueda">
                </div>
                <input type="submit" class="btn" name="buscar" value="Buscar">
            </form>
        </div>
    </body>
</html>

<?php
function crearCadena($row){
    return "ID: " . $row['clave_producto'] . " Nombre: " . $row['nombre'] . " Descripcion: " . $row['descripcion'] . " Cantidad: " . $row['cantidad'] . " Departamento: " . $row['departamento'] . " Stock max: " . $row['stock_max'] . " Stock min: " . $row['stock_min'] . " Status: " . $row['status'] . " Precio: " . $row['precio'];
}
?>

<?php
if(isset($_POST['consultar'])){
    $consultarDatos = "SELECT * FROM producto";
    $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
?>
    <select name="listbox.php" size="<?=$numColumnas?>">
        <?php
        while($row = mysqli_fetch_array($ejecutarConsultar)) {
        ?>
        <option value="<?=crearCadena($row)?>"><?=crearCadena($row)?></option>
        <?php
        }
        ?>
    </select>
<?php
}
?>

<?php
if(isset($_POST['buscar'])){
    $columnaBuscar = $_POST['columna'];
    $criterioBuscar = $_POST['busqueda'];
    
    $consultarDatos = "SELECT * FROM producto where $columnaBuscar = '$criterioBuscar'";
    $ejecutarConsultar = mysqli_query($enlace, $consultarDatos);
?>
    <select name="listbox.php" size="<?=$numColumnas?>">
        <?php
        while($row = mysqli_fetch_array($ejecutarConsultar)) {
        ?>
        <option value="<?=crearCadena($row)?>"><?=crearCadena($row)?></option>
        <?php
        }
        ?>
    </select>
<?php
}
?>

    
