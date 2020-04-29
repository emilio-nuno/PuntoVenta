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
              <a href="../Vistas/Corte/Generar_Corte.php">Generar Corte de Caja</a>
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
        <li>Producto</li>
        <li>Empleado</li>
        <li>Cliente</li>
      </ul>
    </h1>
</head>