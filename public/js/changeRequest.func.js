function cleanContent(data) {
    return !data ? '' : data;
}

function redGreenHtml(data) {
    if (typeof data === 'string') {
        if (data.toUpperCase() === 'Y') {
            return '<span style="color:green">Y</span>';
        }
        else {
            return '<span style="color:red">' + data + '</span>';
        }
    }
    return data;
}


function htmlModal(header = '') {
    var html = {};
    html.header = header;
    html.body =
        '<form id="addChangeInput" class="form-horizontal" name="">' +
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Name</label>' +
        '<div class="col-sm-9">' +
        '<input id="inputName" value="" type="text" name="name" placeholder="input name." class="form-control form-control-success" required>' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-9 offset-sm-3"><hr></div>'+
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">DataType</label>' +
        '<div class="col-sm-9">' +
        '<select id="inputDataType" name="dataType" class="selectpicker form-control" data-style="btn-primary" title="Choose Data Type." required>' +
        '<option value="char">char</option>' +
        '<option value="varchar">varchar</option>' +
        '<option value="nchar">nchar</option>' +
        '<option value="nvarchar">nvarchar</option>' +
        '<option value="int">int</option>' +
        '<option value="float">float</option>' +
        '<option value="decimal">decimal</option>' +
        '<option value="date">date</option>' +
        '<option value="datetime">datetime</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div id="dataTypeDetail">' +
        '</div>' +
        '<div class="col-sm-9 offset-sm-3"><hr></div>'+
        '<div id="constraintDetail">' +
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Default</label>' +
        '<div class="col-sm-9">' +
        '<input id="inputDefault" type="text" name="default" placeholder="Default value." class="form-control form-control-success" required>' +
        '</div>' +
        '</div>' +
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label"></label>' +
        '<div class="col-sm-4">' +
        '<label class="checkbox-inline">' +
        '<input id="inputNullable" name="nullable" type="checkbox" value="Y"> Nullable' +
        '</label>' +
        '</div>' +
        '<div class="col-sm-4">' +
        '<label class="checkbox-inline">' +
        '<input id="inputUnique" name="unique" type="checkbox" value="Y"> Unique' +
        '</label>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div id="minMax" style="">' +
        '</div>'+
        '<div class="col-sm-9 offset-sm-3"><hr></div>'+
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Column Name</label>' +
        '<div class="col-sm-9">' +
        '<input id="inputColumnName" value="" type="text" name="columnName" placeholder="column name." class="form-control form-control-success" required>' +
        '</div>' +
        '</div>' +
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Table Name</label>' +
        '<div class="col-sm-9">' +
        '<input id="inputTableName" value="" type="text" name="tableName" placeholder="table name." class="form-control form-control-success" required>' +
        '</div>' +
        '</div>' +
        '</form>';
    html.footer =
        '<div class="row">' +
        '<div class="col-sm-9">' +
        '<input type="submit" value="Done" class="btn btn-primary">' +
        '</div>' +
        '</div>';
    return html;
}

function setLengthHtml(length = ''){
    $('div.modal-body > form > div#dataTypeDetail').html(
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Length</label>' +
        '<div class="col-sm-6">' +
        '<input id="inputLength" type="number" name="length" placeholder="input Length" value="'+length+'" class="form-control form-control-success" required>' +
        '</div>' +
        '</div>');
}

function setPrecisionHtml(precision = '' , scale = ''){
    $('div.modal-body > form > div#dataTypeDetail').html(
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Precision</label>' +
        '<div class="col-sm-6">' +
        '<input id="inputPrecision" type="number" value="'+precision+'" name="precision" placeholder="precision value." class="form-control form-control-success" required>' +
        '</div>' +
        '</div>' +
        scale +
        '</div>');
}

function setScaleHtml(scale = ''){
    return '<div class="form-group row">' +
    '<label class="col-sm-3 form-control-label">Scale</label>' +
    '<div class="col-sm-6">' +
    '<input id="inputScale" type="number" name="scale" value="'+scale+'" placeholder="scale value." class="form-control form-control-success" required>' +
    '</div>'
}

function setMinmaxHtml(min = '', max = ''){
    $('div.modal-body > form > div#minMax').html('<div class="form-group row">' +
    '<label class="col-sm-3 form-control-label">Min</label>' +
    '<div class="col-sm-6">' +
    '<input id="inputMin" type="text" value="'+min+'" name="min" placeholder="min value." step="any" class="form-control form-control-success" required>' +
    '</div>' +
    '</div>' +
    '<div class="form-group row">' +
    '<label class="col-sm-3 form-control-label">Max</label>' +
    '<div class="col-sm-6">' +
    '<input id="inputMax" type="text" value="'+max+'" name="max" placeholder="max value." step="any" class="form-control form-control-success" required>' +
    '</div>' +
    '</div>');
}

function setHtmlModal(data) {
    $('#modalHeader').html(data.header);
    $('div.modal-body').html(data.body);
    $('div.modal-body').find('.selectpicker').selectpicker();
    $('div.modal-footer').html(data.footer);
}

function hideShowDetailbyDataType(dataType, detail = {length: '', precision: '', scale: '', min: '', max: ''}){
    switch (dataType) {
        case 'char':
        case 'varchar':
        case 'nvarchar':
        case 'nchar':
            setLengthHtml(detail.length);
            $('div.modal-body > form > div#minMax').html('');
            break;
        case 'float':
            setPrecisionHtml();
            $('div.modal-body > form > div#minMax').html(setMinmaxHtml(detail.min,detail.max));
            break;
        case 'decimal':
            setPrecisionHtml(detail.precision,setScaleHtml(detail.scale));
            $('div.modal-body > form > div#minMax').html(setMinmaxHtml(detail.min,detail.max));
            break;
        case 'int':
            $('div.modal-body > form > div#dataTypeDetail').html('');
            $('div.modal-body > form > div#minMax').html(setMinmaxHtml(detail.min,detail.max));
            break;
        case 'date':
        case 'datetime':
            $('div.modal-body > form > div#dataTypeDetail').html('');
            $('div.modal-body > form > div#minMax').html('');
            break;
        default:
            break;
    }
}
