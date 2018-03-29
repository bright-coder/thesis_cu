var l = Ladda.create( document.querySelector( '#createBtn' ) );

$("#create").submit(function (event) {
    event.preventDefault();
    //l.ladda('start');
    l.start();
    $.ajax({
        type: "POST",
        url: "/api/v1/projects",
        headers: {
            "Authorization": "Bearer " + $('input[name=accessToken]').val(),
        },
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify($(this).serializeJSON()),
        dataType: 'json',
        success: function (response) {
            window.location.replace("/project/");
        },
        error: function (response) {
            var response = response.responseJSON;
            var msg = response.msg;
            //l.ladda( 'stop' );
            l.stop();
            $('input').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('.alert.alert-danger').remove();
            if(jQuery.type( msg ) === "string") {
                $('#lastLine').before('<div class="alert alert-danger">'+msg+'</div>');
            }
            else if('fields' in msg) {
                $.each(msg.fields, function(index, value) {
                    $('input[name='+index+']').addClass('is-invalid');
                    $('input[name='+index+']').after('<span class="invalid-feedback"><strong>'+value[0]+'</strong></span>');
                });
            }
        }

      });
});