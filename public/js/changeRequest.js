$(function (){
    $.ajax({
        type: "GET",
        url: "/api/v1/projects",
        headers: {
            "Authorization": "Bearer " + $('input[name=accessToken]').val(),
        },
        contentType: 'application/json; charset=utf-8',
        dataType: "json",
        success: function (response) {
            $('#preloadLayout').remove();
            $('#menu').show();
            if('msg' in response) {
                //$('#content').append('<small>Let\'s create your first project.</small>');
            }
            else  {
                response.forEach(project => {
                    $('#selectProject').append('<option value="'+ project.id +'">'+project.name+"</option>");
                });
                $('#selectProject').selectpicker('refresh');
            }
        },
        error: function (response) {
            //var response = response.responseJSON;
            $('#preloadLayout').remove();
            alert("Cannot get project list please refresh this page.");
        }
    });

    $('#selectProject').on('changed.bs.select', function (e) {
        //alert($(this).val());
      });
});