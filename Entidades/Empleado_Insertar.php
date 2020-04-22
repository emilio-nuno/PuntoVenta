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
        <title>Formulario de Registro de Empleados</title>
        <link rel="stylesheet" type="text/css" href="../Estilos/estilos.css">
    </head>
    <body>
        <a href="../Menu.html">Regresar</a>
        <div class="contenedor">
            <form class="formulario" id = "formulario" name = "formulario" method="post">
                <div class = "datos-entrada">
                    <input type="text" name = "nombre_empleado" placeholder="Nombre">
                    <input type="text" name = "rfc_empleado" placeholder="RFC">
                    <input type="text" name = "salario" placeholder="Salario">
                    <input type="text" name = "telefono_empleado" placeholder="Telefono">
                    <input type="text" name = "direccion_empleado" placeholder="Direccion">
                    <input type="text" name = "ciudad_empleado" placeholder="Ciudad">
                    <input type="text" name = "email" placeholder="E-Mail">
                    <input type="text" name = "cargo" placeholder="Cargo">
                </div>
                <input type="submit" class="btn" name="registrarse" value="Registrar">
            </form>
        </div>
    </body>
</html>

<?php
if(isset($_POST['registrarse'])){
    $nombre_empleado = $_POST["nombre_empleado"];
    $rfc_empleado = $_POST["rfc_empleado"];
    $salario = $_POST["salario"];
    $telefono_empleado = $_POST["telefono_empleado"];
    $direccion_empleado = $_POST["direccion_empleado"];
    $ciudad_empleado = $_POST["ciudad_empleado"];
    $email = $_POST["email"];
    $cargo = $_POST["cargo"];
    
    $insertarDatos = "INSERT INTO empleado VALUES('$nombre_empleado', '$rfc_empleado', '$salario', '$telefono_empleado', '$direccion_empleado', '$ciudad_empleado', '$email', '$cargo', default)";
    
    $ejecutarInsertar = mysqli_query($enlace, $insertarDatos);
    
    if(!$ejecutarInsertar){
        echo "Error en la linea de sql";
    }
}
?>