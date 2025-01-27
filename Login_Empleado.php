<?php
 session_start();
/*TODO:
*Agregar campos descriptivos para devolucion
*/
$_SESSION["dinero_caja"] = 2000; //Inicializamos la caja con una valor de 2000

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
$rfc = "";
$rfc_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["rfc"]))){
        $rfc_err = "Por favor inserte un RFC.";
    }
    else{
        // Prepare a select statement
        $sql = "SELECT cargo FROM empleado WHERE rfc_empleado = ?";
        
        if($stmt = mysqli_prepare($enlace, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_rfc);
            
            // Set parameters
            $param_rfc = trim($_POST["rfc"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                $resultado = $stmt->get_result();
                $row = $resultado->fetch_assoc();
                
                if($resultado->num_rows == 1){
                    $rfc = trim($_POST["rfc"]);
                    $_SESSION["empleado"]= $rfc;
                  
                    $nivel = $row["cargo"];
                    if($nivel == "Empleado"){
                      header("Location: Pantallas/Vendedor.php");
                      exit();
                    }
                    else if($nivel == "Supervisor"){
                      header("Location: Pantallas/Gerente.php");
                      exit();
                    }
                    else if($nivel == "Almacenista"){
                      header("Location: Pantallas/Almacenista.php");
                      exit();
                    }
                } else{
                    echo "El RFC ingresado no se encuentra registrado.";
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
    <title>Log-In</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Log-In</h2>
        <p>Por favor inicie sesión, empleado.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($rfc_err)) ? 'has-error' : ''; ?>">
                <label>RFC</label>
                <input type="text" name="rfc" class="form-control" value="<?php echo $rfc; ?>">
                <span class="help-block"><?php echo $rfc_err; ?></span>
            </div>    
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-default" value="Reset">
            </div>
        </form>
    </div>    
</body>
</html>