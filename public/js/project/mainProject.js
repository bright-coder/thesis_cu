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
            $('#content').append('<small>Let\'s create your first project.</small>');
        }
        else  {
            response.forEach(project => {
                $('#content').append('<div class="col-md-6 col-lg-3">'+
                '<div class="card"><img src="https://d19m59y37dris4.cloudfront.net/admin-premium/1-4-0/img/mockup4.jpg" alt="Card image cap" class="card-img-top img-fluid">'+
                  '<div class="card-body">'+
                    '<h3 class="card-title"><center><a href="/project/'+project.projectId+'" class="card-link">'+project.projectName+'</a></center></h3>'+
                  '</div>'+
                  '<ul class="list-group list-group-flush">'+
                    '<li class="list-group-item"><i class="fa fa-server"></i>&emsp;'+project.dbServer+'</li>'+
                    '<li class="list-group-item"><i class="fa fa-database"></i>&emsp;'+project.dbName+'</li>'+
                    '<li class="list-group-item"><center><button class="btn btn-danger" onclick="deleteProject('+project.projectId+')"><i class="fa fa-trash"></i></button></center></li>'+
                  '</ul>'+
                '</div>'+
              '</div>');
            });
        }
    },
    error: function (response) {
        //var response = response.responseJSON;
        $('#preloadLayout').remove();
        alert("Cannot get project list please refresh this page.");
    }
});

function deleteProject(id) {
    if(confirm("Are you sure you want to delete this project?")){
        $.ajax({
            type: "DELETE",
            url: "/api/v1/projects/"+id,
            headers: {
                "Authorization": "Bearer " + $('input[name=accessToken]').val(),
            },
            contentType: 'application/json; charset=utf-8',
            dataType: "json",
            success: function (response) {
                location.reload();
            },
            error: function (response) {
                //var response = response.responseJSON;
                alert("Cannot delete this project.");
            }
        });
    }
    else{
        return false;
    }
}