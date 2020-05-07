<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Menú para Punto de Venta">
    <meta name="author" content="Emilio Nuño Paz">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../Estilos/dropdown.css">
    <title>Vista de Almacenista</title>
    
    <div class="navbar">
        <div class="dropdown">
            <button class="dropbtn">Movimiento de Almacén 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
              <a href="../Vistas/Movimiento/Movimiento_Almacen.php">Registrar un Movimiento</a>
              <a href="../Vistas/Movimiento/Movimiento_Buscar.php">Consultar Movimientos</a>
            </div>
        </div>
    </div>
    <h1>
      <ul>
        <li>Movimiento de Almacén
          <ul>
            <li>Realizar un Movimiento</li>
            <li>Consultar Movimientos</li>
          </ul>
        </li>
      </ul>
    </h1>
</head>