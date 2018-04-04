var id = $(location).attr('pathname').split("/")[2];
var frFromFile = [];
var data = [[1],2];
$("#frFile").filestyle({
    htmlIcon: '<i class="fas fa-file-excel"></i> ',
    text: "Upload",
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
    // if (target === '#pills-db') {
    //     getDatabase();
    // }
});
getProject();
getDatabase();

$('input[name=projectName]').on("change paste keyup", function () {
    $('#header').text($(this).val());
    $('#headerBread').text($(this).val());
});


$("#saveProject").submit(function (event) {
    event.preventDefault();
    var l = Ladda.create(document.querySelector('#saveProjectBtn'));
    var id = $(location).attr('pathname').split("/")[2];
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
            setTimeout(function (){
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
    getDatabase();
});

$(window).on('hashchange', function (e) {
    history.replaceState("", document.title, e.originalEvent.oldURL);
});

$('input[type=file]').change(readExcel);