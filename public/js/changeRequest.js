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
        var tbody = $('table#inputFrTable > tbody');
        $('#descText').html('<small>' + frList[$(this).val()].description + '</small>');
        tbody.html('');
        changeRequest.inputList = [];
        changeRequest.functionalRequirementId = $(this).val();
        $('table#changeListTable > tbody').html('');
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
        setHtmlModal(htmlModal('Add new input', 'add'));
        $('#myModal').modal('show');
    });

    $(document).on('click', 'button[name=editInput]', function () {
        var input = frList[$('#selectFr').val()].inputs[$(this).attr('id')];
        setHtmlModal(htmlModal('Edit ' + input.name, 'edit'));
        $('#inputName').replaceWith(input.name);
        $('div.modal-body').find('.selectpicker').selectpicker('val', input.dataType);
        var detail = {
            length: cleanContent(input.length),
            precision: cleanContent(input.precision),
            scale: cleanContent(input.scale),
            min: cleanContent(input.min),
            max: cleanContent(input.max)
        }
        hideShowDetailbyDataType($('#inputDataType').val(), detail);
        $('#inputDefault').val(cleanContent(input.default));
        if (typeof input.nullable === 'string') {
            if (input.nullable.toUpperCase() === 'Y') {
                $('#inputNullable').attr('checked', 'checked');
            }
        }
        if (typeof input.unique === 'string') {
            if (input.unique.toUpperCase() === 'Y') {
                $('#inputUnique').attr('checked', 'checked');
            }
        }
        $('#inputColumnName').replaceWith(input.columnName);
        $('#inputTableName').replaceWith(input.tableName);
        $('#submitChangeInput').attr('name', $(this).attr('id'));
        $('#myModal').modal('show');
    });

    $(document).on('click', 'button[name=deleteInput]', function () {
        var input = frList[$('#selectFr').val()].inputs[$(this).attr('id')];
        var html = {};
        html.header = 'Delete Input ' + input.name;
        html.body = '';
        html.footer = '<div class="row">' +
            '<div class="col-sm-12">' +
            '<input type="submit" value="Yes" class="btn btn-primary" id="submitChangeInput">' +
            '<button class="btn btn-danger" id="noBtn">No</button>'
        '</div>' +
            '</div>';
        setHtmlModal(html);
        $('form#addChangeInput').attr('name', 'delete');
        $('#submitChangeInput').attr('name', $(this).attr('id'));
        $('#myModal').modal('show');
    });

    $(document).on('click', '#noBtn', function (e) {
        $('#myModal').modal('hide');
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
                }
                break;
            case 'delete':
                var data = Object.assign({changeType : $(this).attr('name') }, frList[changeRequest.functionalRequirementId].inputs[$('#submitChangeInput').attr('name')]);
                
                //console.log(frList[changeRequest.functionalRequirementId].inputs[$('#submitChangeInput').attr('name')]);
                changeRequest.inputList.push(data);
                break;
            default:
                break;
        }

        var last = changeRequest.inputList.length - 1;
        if (last > -1) {
            var input = changeRequest.inputList[last];
            var tbody = $('table#changeListTable > tbody');
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
            $('#myModal').modal('hide');
            $('#changeList').show();
        }


    });



});