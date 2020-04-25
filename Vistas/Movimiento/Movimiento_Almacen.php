<?php
$servidor="localhost";
$usuario="root";
$clave="";
$baseDeDatos="formulario";

$enlace = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if(mysqli_connect_errno()){
  echo "Conexión a la base de datos fallida";
}
?>

<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
  <title>Registro de Movimientos</title>
</head>

<body>
  <form method="post" class="pure-form pure-form-stacked">
    <fieldset>
      <label for="fecha">Fecha</label>
      <input type="date" id="fecha" value=<?=date("Y-m-d")?> required name="fecha" required>
      <label for="motivo">Motivo</label>
      
      <select id="motivo" name="motivo" required>
        <option value="compra_cliente">Compra de Cliente</option>
        <option value="devolucion_proveedor">Devolución a Proovedor</option>
        <option value="compra_proveedor">Compra a Proveedor</option>
        <option value="devolucion_cliente">Devolución de Cliente</option>
      </select>
      
      <label for="tipo">Tipo de Movimiento</label>
      <select name="tipo" id="tipo" required>
        <option value="entrada">Entrada</option>
        <option value="salida">Salida</option>
      </select>
      
      <label for="rfc">RFC de Empleado</label>
      <input id="rfc" name="rfc" type="text" minlength="13" maxlength="13" required>
      
      <label for="folio">Folio Generador</label>
      <input id="folio" name="folio" type="number" min="1" required>
      
      <button type="submit" class="pure-button pure-button-primary" name="registrar">Registrar</button>
    </fieldset>
  </form>
</body>

</html> 

<?php
  if(isset($_POST["registrar"])){
    $motivo = $_POST["motivo"];
    $fecha = $_POST["fecha"];
    $tipo = $_POST["tipo"];
    $rfc = $_POST["rfc"];
    $folio_gen = $_POST["folio"];
    
    $stmtInsertarMovimiento = $enlace->prepare("INSERT INTO movimiento_almacen ( fecha ,  tipo ,  id_empleado ,  motivo ,  folio_generador ) VALUES ( ? , ? , ? , ?, ? )");
    $stmtInsertarMovimiento->bind_param("ssssi", $fecha, $tipo, $rfc, $motivo, $folio_gen);
    $stmtInsertarMovimiento->execute();
    
    if($enlace->affected_rows == 0){
      echo "Hubo un problema al intentar crear el registro, intente luego";
    }
    else{
      echo "Registro creado con éxito!";
    }
    
  }
?>