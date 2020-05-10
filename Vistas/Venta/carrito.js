function DesplegarCarrito() {
    var idProducto = $("#idProducto").val();
    var cantidadProducto = $("#cantidadProducto").val();
    $.post("../../Herramientas/MostrarDescripcion/agregarCarrito.php", { idProducto: idProducto, cantidadProducto: cantidadProducto },
    function(data) {
    $('#resultados').html(data);
    $('#miFormulario')[0].reset();
    MostrarInfoPago();
    });
}

function MostrarInfoPago() {
    var idProducto = $("#idProducto").val();
    var cantidadProducto = $("#cantidadProducto").val();
    $.post("mostrarPago.php", { idProducto: idProducto, cantidadProducto: cantidadProducto },
    function(data) {
    $('#infoPago').html(data);
    $('#miFormulario')[0].reset();
    $("#descProducto").html("");
    });
}

function GenerarCarrito(){
  DesplegarCarrito();
}