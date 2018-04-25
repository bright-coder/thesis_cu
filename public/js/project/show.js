var frFromFile,tcFromFile,rtmFromFile;
var frTable = $('table#frTable').DataTable({
    "order": [[ 0, "asc" ]],
});
var tcTable = $('table#tcTable').DataTable({
    "order": [[0, "asc"]]
});
var rtmTable = $('table#rtmTable').DataTable({
    "order": [[0, "asc"]],
    autoWidth: false,
    columnDefs: [
        { className: 'blue-low', width: "30%", targets: 0 },
        { width: "70%", targets: 1 }
      ]
});
var id = $(location).attr('pathname').split("/")[2];

$(function () {

    getProject(id);
    getDatabase(id);
    $("#frFile").filestyle({
        htmlIcon: '<i class="fas fa-file-excel"></i> ',
        text: "Choose File",
        btnClass: "btn-primary",
        buttonBefore: true,
        placeholder: "Functional Requirements (.xlsx)"
    });
    $("#tcFile").filestyle({
        htmlIcon: '<i class="fas fa-file-excel"></i> ',
        text: "Upload",
        btnClass: "btn-primary",
        buttonBefore: true,
        placeholder: "Test cases (.xlsx)"
    });
    $("#rtmFile").filestyle({
        htmlIcon: '<i class="fas fa-file-excel"></i> ',
        text: "Upload",
        btnClass: "btn-primary",
        buttonBefore: true,
        placeholder: "Requirement Traceability Matrix (.xlsx)"
    });
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href") // activated tab
        // if (target === '#pills-project') {
        //     getProject();

        // }
    });

    $('input[name=projectName]').on("change paste keyup", function () {
        $('#header').text($(this).val());
        $('#headerBread').text($(this).val());
    });


    $("#saveProject").submit(function (event) {
        event.preventDefault();
        var l = Ladda.create(document.querySelector('#saveProjectBtn'));
        //var id = $(location).attr('pathname').split("/")[2];
        l.start();
        $.ajax({
            type: "PATCH",
            url: "/api/v1/projects/" + id,
            headers: {
                "Authorization": "Bearer " + $('input[name=accessToken]').val(),
            },
            contentType: 'application/json; charset=utf-8',
            data: JSON.stringify($(this).serializeJSON()),
            dataType: 'json',
            success: function (response) {
                l.stop();
                $('input').removeClass('is-invalid');
                $('#showMessage').html('<div class="alert alert-success">' + response.msg + '</div>');
                $('.alert-success').fadeOut(5000);
                setTimeout(function () {
                    $('.alert-success').remove();
                }, 2000);
            },
            error: function (response) {
                var response = response.responseJSON;
                var msg = response.msg;
                l.stop();
                $('input').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                $('.alert.alert-danger').remove();
                if (jQuery.type(msg) === "string") {
                    $('#showMessage').html('<div class="alert alert-danger">' + msg + '</div>');
                }
                else if ('fields' in msg) {
                    $.each(msg.fields, function (index, value) {
                        $('input[name=' + index + ']').addClass('is-invalid');
                        $('input[name=' + index + ']').after('<span class="invalid-feedback"><strong>' + value[0] + '</strong></span>');
                    });
                }
            }

        });
    });

    $(document).on('click', 'button#saveFr', function (){
        var l = Ladda.create(document.querySelector('button#saveFr'));
        l.start();
        $.ajax({
            type: "POST",
            url: "/api/v1/projects/"+id+"/functionalRequirements",
            data: JSON.stringify(cleanObject(frFromFile)),
            headers: {
                "Authorization": "Bearer " + $('input[name=accessToken]').val(),
            },
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                $('#pills-fr').find('#showMessage').html('<div class="alert alert-success">Save Successfully.</div>');
                l.stop();
                
            },
            error: function(response) {
                $('#pills-fr').find('#showMessage').html('<div class="alert alert-danger">Error please try again.</div>');
                l.stop();
                
            }
        });

    });

    $(document).on('click', 'button#saveTc', function (){
        var l = Ladda.create(document.querySelector('button#saveTc'));
        l.start();
        $.ajax({
            type: "POST",
            url: "/api/v1/projects/"+id+"/testCases",
            data: JSON.stringify(cleanObject(tcFromFile)),
            headers: {
                "Authorization": "Bearer " + $('input[name=accessToken]').val(),
            },
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                l.stop();
                $('#pills-tc').find('#showMessage').html('<div class="alert alert-success">Save Successfully.</div>');
            },
            error: function(response) {
                l.stop();
                $('#pills-tc').find('#showMessage').html('<div class="alert alert-danger">Error please try again.</div>');
            }
        });

    });

    $(document).on('click', 'button#saveRtm', function (){
        var l = Ladda.create(document.querySelector('button#saveRtm'));
        l.start();
        $.ajax({
            type: "POST",
            url: "/api/v1/projects/"+id+"/RTM",
            data: JSON.stringify(rtmFromFile),
            headers: {
                "Authorization": "Bearer " + $('input[name=accessToken]').val(),
            },
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                l.stop();
                $('#pills-rtm').find('#showMessage').html('<div class="alert alert-success">Save Successfully.</div>');
            },
            error: function(response) {
                l.stop();
                $('#pills-rtm').find('#showMessage').html('<div class="alert alert-danger">Error please try again.</div>');
            }
        });

    });

    $(document).on('click', 'button[name=visible]', function () {
        visible($(this).attr('id'));
    });


    $(document).on('click', 'a.dropdown-item', function (e) {
        e.preventDefault();
        if (!$('#' + $(this).attr('id') + ".card-body").is(":visible")) {
            visible($(this).attr('id'));
        }
        if ($(this).attr('href') == '#showColumn') {
            showColumn($(this).attr('id'));
        } else if ($(this).attr('href') == '#showInstance') {
            showInstance($(this).attr('id'));
        }
        else if ($(this).attr('href') == '#showConstraint') {
            showConstraint($(this).attr('id'));
        }
    });

    $(document).on('click', '#refreshDb', function () {
        $('#pills-db > section.tables').remove();
        getDatabase(id);
    });

    $(window).on('hashchange', function (e) {
        history.replaceState("", document.title, e.originalEvent.oldURL);
    });

    //$(document).on('change', 'input[type=file]', readExcel);
    $('input[type=file]').change(function (){
        var files = $(this).prop('files');
        if(files.length > 0) {
            readExcel(files[0], $(this).attr('id'));
        }
    });

    $(document).on('click', 'button[name=fr],button[name=tc]', function () {
        setHtmlModal($(this).attr('id'),$(this).attr('name'));
        $('#myModal').modal('show');
    });

    $(document).on('click', 'button#saveFr', function(){
        
    });
});

