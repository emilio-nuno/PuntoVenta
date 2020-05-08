function MostrarDescripcion() {
    var idProducto = $("#idProducto").val();
    $.post("muestraInformacion.php", { clave: idProducto },
    function(data) {
    $('#descProducto').html(data);
});
}