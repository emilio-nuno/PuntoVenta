<?php
session_start();
echo $_SESSION["dinero_caja"];
?>

<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Menú para Punto de Venta">
    <meta name="author" content="Emilio Nuño Paz">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../Estilos/dropdown.css">
    <title>Vista de Vendedor</title>
    
    <div class="navbar">
        <div class="dropdown">
            <button class="dropbtn">Cliente 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
              <a href="../Entidades/Cliente_Insertar.php">Registrar Cliente</a>
            </div>
        </div>
        <div class="dropdown">
            <button class="dropbtn">Venta 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
              <a href="../Vistas/Venta/Verificar_Cliente.php">Comenzar Venta</a>
              <a href="../Vistas/Venta/Consultar_Venta.php">Consultar Ventas</a>
            </div>
        </div>
      <div class="dropdown">
            <button class="dropbtn">Devolución 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
              <a href="../Vistas/Devolucion/Inicio_Devolucion.php">Comenzar Devolución</a>
              <a href="../Vistas/Devolucion/Consultar_Devolucion.php">Consultar Devolución</a>
            </div>
        </div> 
    </div>
    <h1>
      <ul>
        <li>Cliente
          <ul>
            <li>Registrar Cliente</li>
          </ul>
        </li>
        <li>Venta
          <ul>
            <li>Realizar Venta</li>
            <li>Consulta de Ventas</li>
          </ul>
        </li>
        <li>Devolución
          <ul>
            <li>Realizar Devolución</li>
            <li>Consulta de Devoluciones</li>
          </ul>
        </li>
      </ul>
    </h1>
</head>