<?php
/*TODO: Agregar validacion para corte de caja SOLO desde las 7 PM
*/
$horaCorteDisponible = 19;
$horaInicioJornada = 10;

function enRango($inicio, $fin){
  $horaActual = date("H");
  //$horaActual = 19;
  if($inicio > $fin){
    if($horaActual >= $inicio || $horaActual < $fin){
      return true;
    }
  }
  
  else if($horaActual >= $inicio && $horaActual <= $fin){
      return true;
  }
  
  return false;
}

?>
<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Menú para Punto de Venta">
    <meta name="author" content="Emilio Nuño Paz">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../Estilos/dropdown.css">
    <title>Vista de Gerente</title>
    
    <div class="navbar">
        <div class="dropdown">
            <button class="dropbtn">Corte de Caja 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
              <?php if(enRango($horaCorteDisponible, $horaInicioJornada)){?>
              <a href="../Vistas/Corte/Generar_Corte.php">Generar Corte de Caja</a>
             <?php }else{ ?>
              <a>Generar Corte de Caja</a>
            <?php } ?>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropbtn">Ajuste de Inventario 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
              <a href="../Vistas/Ajuste/Ajuste_Inventario.php">Registrar un Ajuste</a>
              <a href="../Vistas/Ajuste/Ajuste_Buscar.php">Consultar Ajustes</a>
            </div>
        </div>
    </div>
    <h1>
      <ul>
        <li>Generar Corte de Caja</li>
        <li>
        Ajuste de Inventario
          <ul>
            <li>Registrar un ajuste</li>
            <li>Consultar ajustes</li>
          </ul>
        </li>
      </ul>
    </h1>
</head>