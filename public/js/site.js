function loadModal(url, title) {
    $('#modal').find('.modal-body').html('');
    $('#modal').find('.modal-title').html(title);
    $('#modal').find('.modal-body').load(url, function( response, status, xhr ) {
        if ( status == "error" ) {
            var msg = "Error: ";
            $( "#modal" ).find('.modal-body').html( msg + xhr.status + " " + xhr.statusText );
        }
        else {
            $('#modal [data-toggle=tooltip]').tooltip();
            $('#modal .cp').colorpicker();
        }
    });
    $('#modal').modal('show');
}