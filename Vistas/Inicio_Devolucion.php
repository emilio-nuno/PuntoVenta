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

// Define variables and initialize with empty values
$folio = "";
$folio_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["folio"]))){
        $folio_err = "Por favor inserte un folio.";
    }
    else{
        // Prepare a select statement
        $sql = "SELECT folio_venta, fecha_venta FROM venta WHERE folio_venta = ?";
        
        if($stmt = mysqli_prepare($enlace, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_folio);
            
            // Set parameters
            $param_folio = trim($_POST["folio"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                $resultado = $stmt->get_result();
                $row = $resultado->fetch_assoc();
                
                if($resultado->num_rows == 1){
                    $folio = trim($_POST["folio"]); //hacer que este sea el éxito
                    $_SESSION["folio"]= $folio;
                    $_SESSION["fecha"] = $row["fecha_venta"];
                    header("Location: Devolucion.php");
                    exit;
                } else{
                    echo "El folio de venta no existe en la base de datos.";
                }
            // Close statement
            mysqli_stmt_close($stmt);
            }
        }
    // Close connection
    mysqli_close($enlace);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log-In de Cliente</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Log-In</h2>
        <p>Por favor digite el folio de venta cara comenzar la devolución.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($folio_err)) ? 'has-error' : ''; ?>">
                <label>Folio</label>
                <input type="text" name="folio" class="form-control" value="<?php echo $folio; ?>">
                <span class="help-block"><?php echo $folio_err; ?></span>
            </div>    
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-default" value="Reset">
            </div>
        </form>
    </div>    
</body>
</html>