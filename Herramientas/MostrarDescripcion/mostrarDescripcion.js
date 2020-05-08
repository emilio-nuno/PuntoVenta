function MostrarDescripcion(elementoClave, contenedorResultado) {
    var idProducto = $(elementoClave).val();
    $.post("../../Herramientas/MostrarDescripcion/muestraInformacion.php", { clave: idProducto },
    function(data) {
    $(contenedorResultado).html(data);
});
}