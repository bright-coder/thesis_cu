var frList;
var changeRequest = { inputList: [] };
var frTableBody = $('table#inputFrTable');
var changeTableBody = $('table#changeListTable');
var formAddChangeInput = $('form#addChangeInput');
var modalAddChangeInput = $('#myModal');
$(function () {
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
            if ('msg' in response) {
                //
            }
            else {
                response.forEach(project => {
                    $('#selectProject').append('<option value="' + project.id + '">' + project.name + '</option>');
                });
                $('#selectProject').selectpicker('refresh');
            }
        },
        error: function (response) {
            $('#preloadLayout').remove();
            alert("Cannot get project list please refresh this page.");
        }
    });

    $('#selectProject').on('changed.bs.select', function (e) {
        $('#selectProjectMenu').show();
        changeRequest.functionalRequirementId = null;
        changeRequest.inputList = [];
        changeRequest.projectId = $(this).val();
        var tbody = $('table#changeListTable > tbody');
        tbody.html('');
        $('#changeList').hide();
        $.ajax({
            type: "GET",
            url: "/api/v1/projects/" + $(this).val() + "/functionalRequirements",
            headers: {
                "Authorization": "Bearer " + $('input[name=accessToken]').val(),
            },
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (response) {
                //console.log(response);
                if (response.length > 0) {
                    frList = response;
                    $.each(response, function (index, fr) {
                        $('#selectFr').append('<option value="' + index + '">' + fr.no + '</option>');
                    });
                    $('#selectFr').selectpicker('refresh');
                }
            },
            error: function (response) {

            }
        });
    });

    $('#selectFr').on('changed.bs.select', function (e) {
        var tbody = frTableBody.find('tbody');
        $('#descText').html('<small>' + frList[$(this).val()].description + '</small>');
        tbody.html('');
        changeRequest.inputList = [];
        changeRequest.functionalRequirementId = $(this).val();
        changeTableBody.find('tbody').html('');
        $('#changeList').hide();
        $.each(frList[$(this).val()].inputs, function (index, input) {
            var row = '<tr>';
            row += '<td>' + cleanContent(input.name) + '</td>' +
                '<td>' + cleanContent(input.dataType) + '</td>' +
                '<td>' + cleanContent(input.length) + '</td>' +
                '<td>' + cleanContent(input.precision) + '</td>' +
                '<td>' + cleanContent(input.scale) + '</td>' +
                '<td>' + cleanContent(input.default) + '</td>' +
                '<td>' + redGreenHtml(cleanContent(input.nullable)) + '</td>' +
                '<td>' + redGreenHtml(cleanContent(input.unique)) + '</td>' +
                '<td>' + cleanContent(input.min) + '</td>' +
                '<td>' + cleanContent(input.max) + '</td>' +
                '<td>' + cleanContent(input.columnName) + '</td>' +
                '<td>' + cleanContent(input.tableName) + '</td>' +
                '<td><button class="btn btn-warning" id="' + index + '" name="editInput">Edit</button><button class="btn btn-danger" id="' + index + '" name="deleteInput">Delete</button></td>' +
                '</tr>';
            tbody.append(row);
        });
        $('#content.card-body').show();
        $('#inputChangeMenu').show();
    });


    $(document).on('changed.bs.select', '#inputDataType', function (e) {
        hideShowDetailbyDataType($(this).val());
    });

    $(document).on('click', 'button[name=addInput]', function () {
        setModalHtml('add');
        modalAddChangeInput.modal('show');
    });

    $(document).on('click', 'button[name=editInput]', function () {
        var input = frList[$('#selectFr').val()].inputs[$(this).attr('id')];
        setModalHtml('edit',input, $(this).attr('id'));
        modalAddChangeInput.modal('show');
    });

    $(document).on('click', 'button[name=deleteInput]', function () {
        var input = frList[$('#selectFr').val()].inputs[$(this).attr('id')];
        setModalHtml('delete',input, $(this).attr('id'));
        modalAddChangeInput.modal('show');
    });

    $(document).on('click', '#noBtn', function (e) {
        modalAddChangeInput.modal('hide');
    });

    $('#addChangeInput').submit(function (event) {
        event.preventDefault();

        switch ($(this).attr('name')) {
            case 'add':
                var data = preAddChangeList($(this).serializeArray(), $(this).attr('name'));
                changeRequest.inputList.push(data);
                break;
            case 'edit':
                var data = preAddChangeList($(this).serializeArray(), $(this).attr('name'));
                var input = frList[changeRequest.functionalRequirementId].inputs[$('#submitChangeInput').attr('name')];
                if (IsChange(data, input)) {
                    data.name = input.name;
                    data.columnName = input.columnName;
                    data.tableName = input.tableName;
                    changeRequest.inputList.push(data);
                    $('button#'+$('#submitChangeInput').attr('name')+'[name=editInput]').remove();
                    $('button#'+$('#submitChangeInput').attr('name')+'[name=deleteInput]').remove();
                }
                break;
            case 'delete':
                var data = Object.assign(
                    {changeType : $(this).attr('name') }, 
                    frList[changeRequest.functionalRequirementId].inputs[$('#submitChangeInput').attr('name')]
                );
                changeRequest.inputList.push(data);
                $('button#'+$('#submitChangeInput').attr('name')+'[name=editInput]').remove();
                $('button#'+$('#submitChangeInput').attr('name')+'[name=deleteInput]').remove();
                break;
            default:
                break;
        }

        var last = changeRequest.inputList.length - 1;
        if (last > -1) {
            var input = changeRequest.inputList[last];
            var tbody = changeTableBody.find('tbody');
            //var tbody = $('table#changeListTable > tbody');
            tbody.append('<tr>' +
                '<td>' + cleanContent(input.name) + '</td>' +
                '<td>' + cleanContent(input.dataType) + '</td>' +
                '<td>' + cleanContent(input.length) + '</td>' +
                '<td>' + cleanContent(input.precision) + '</td>' +
                '<td>' + cleanContent(input.scale) + '</td>' +
                '<td>' + cleanContent(input.default) + '</td>' +
                '<td>' + redGreenHtml(cleanContent(input.nullable)) + '</td>' +
                '<td>' + redGreenHtml(cleanContent(input.unique)) + '</td>' +
                '<td>' + cleanContent(input.min) + '</td>' +
                '<td>' + cleanContent(input.max) + '</td>' +
                '<td>' + cleanContent(input.columnName) + '</td>' +
                '<td>' + cleanContent(input.tableName) + '</td>' +
                '<td>' + htmlBadge(input.changeType) + '</td>' + +
                '</tr>');
            modalAddChangeInput.modal('hide');
            $('#changeList').show();
            console.log(changeRequest.inputList);
        }


    });



});