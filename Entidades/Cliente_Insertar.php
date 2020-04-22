<?php
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";
$numColumnas = 6;

$enlace = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

if(!$enlace){
    echo "Error en la conexion del servidor";
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Formulario de Registro de Clientes</title>
        <link rel="stylesheet" type="text/css" href="../Estilos/estilos.css">
    </head>
    <body>
        <a href="../Menu.html">Regresar</a>
        <div class="contenedor">
            <form class="formulario" id = "formulario" name = "formulario" method="post">
                <div class = "datos-entrada">
                    <input type="text" name = "rfc" placeholder="RFC">
                    <input type="text" name = "nombre" placeholder="Nombre">
                    <input type="text" name = "correo" placeholder="Correo">
                    <select name="genero">
                    <option value="" disabled selected>Sexo</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    </select>    
                    <input type="text" name = "domicilio" placeholder="Domicilio">
                    <input type="text" name = "telefono" placeholder="Telefono">
                </div>
                <input type="submit" class="btn" name="registrarse" value="Registrar">
            </form>
        </div>
    </body>
</html>

<?php
if(isset($_POST['registrarse'])){
    $rfc_cliente = $_POST["rfc"];
    $nombre_cliente = $_POST["nombre"];
    $correo_cliente = $_POST["correo"];
    $sexo_cliente = $_POST["genero"];
    $direccion_registrada = $_POST["domicilio"];
    $telefono_cliente = $_POST["telefono"];
    
    $insertarDatos = "INSERT INTO cliente VALUES('$rfc_cliente', '$nombre_cliente', '$correo_cliente', '$sexo_cliente', '$direccion_registrada', '$telefono_cliente')";
    
    $ejecutarInsertar = mysqli_query($enlace, $insertarDatos);
    
    if(!$ejecutarInsertar){
        echo "Error en la linea de sql";
    }
}
?>
