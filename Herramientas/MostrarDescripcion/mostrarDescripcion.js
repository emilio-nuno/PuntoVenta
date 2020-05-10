function MostrarDescripcion(elementoClave, contenedorResultado) {
    var idProducto = $(elementoClave).val();
    $.post("../../Herramientas/MostrarDescripcion/muestraInformacion.php", { clave: idProducto },
    function(data) {
    $(contenedorResultado).html(data);
});
}

function MostrarInfoFolio(motivo, elementoClave, contenedorResultado){
  $(contenedorResultado).html("");
  
  var folio = $(elementoClave).val();
  
  console.log(`El motivo es ${motivo} y es de tipo ${typeof motivo}`);

  $.post("mostrarInfoFolio.php", { folio: folio , motivo: motivo },
    function(data) {
    $(contenedorResultado).html(data);
});
}

function MostrarCarrito() {
    var idProducto = $("#idProducto").val();
    var cantidadProducto = $("#cantidadProducto").val();
    $.post("../../Herramientas/MostrarDescripcion/agregarCarrito.php", { idProducto: idProducto, cantidadProducto: cantidadProducto },
    function(data) {
    $('#resultados').html(data);
    $('#miFormulario')[0].reset();
    });
}