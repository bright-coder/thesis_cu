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

function setModalHtml(type, input = null) {
    if(type == 'add') {
        modalAddChangeInput.find('.modal-title').html('Add new input');
        modalAddChangeInput.find('.modal-body').load('/vendor/html-layout/change-request/modal-body.html');
        modalAddChangeInput.find('.modal-footer').load('/vendor/html-layout/change-request/modal-footer.html #addEdit');
        modalAddChangeInput.find('.selectpicker').selectpicker();
    }
    else if(type == 'edit') {
        modalAddChangeInput.find('.modal-title').html('Edit '+ input.name + 'input');
        modalAddChangeInput.find('.modal-body').load('/vendor/html-layout/change-request/modal-body.html');
        modalAddChangeInput.find('.modal-footer').load('/vendor/html-layout/change-request/modal-footer.html #addEdit');
        modalAddChangeInput.find('.selectpicker').selectpicker();
        hideShowDetailbyDataType();
    }
    else {
        modalAddChangeInput.find('.modal-title').html('Delete '+ input.name + 'input');
        modalAddChangeInput.find('.modal-footer').load('/vendor/html-layout/change-request/modal-footer.html #delete');
    }
}

function setLengthHtml(length = '') {
    $('div#dataTypeDetail').html(
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Length</label>' +
        '<div class="col-sm-6">' +
        '<input id="inputLength" type="number" name="length" placeholder="input Length" value="' + length + '" class="form-control form-control-success" required>' +
        '</div>' +
        '</div>');
}

function setPrecisionHtml(precision = '', scale = '') {
    $('div#dataTypeDetail').html(
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Precision</label>' +
        '<div class="col-sm-6">' +
        '<input id="inputPrecision" type="number" value="' + precision + '" name="precision" placeholder="precision value." class="form-control form-control-success" required>' +
        '</div>' +
        '</div>' +
        scale +
        '</div>');
}

function setScaleHtml(scale = '') {
    return '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Scale</label>' +
        '<div class="col-sm-6">' +
        '<input id="inputScale" type="number" name="scale" value="' + scale + '" placeholder="scale value." class="form-control form-control-success" required>' +
        '</div>'
}

function setMinmaxHtml(min = '', max = '') {
    $('div#minMax').html('<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Min</label>' +
        '<div class="col-sm-6">' +
        '<input id="inputMin" type="text" value="' + min + '" name="min" placeholder="min value." step="any" class="form-control form-control-success">' +
        '</div>' +
        '</div>' +
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Max</label>' +
        '<div class="col-sm-6">' +
        '<input id="inputMax" type="text" value="' + max + '" name="max" placeholder="max value." step="any" class="form-control form-control-success">' +
        '</div>' +
        '</div>');
}

function setHtmlModal(data) {
    $('#modalHeader').html(data.header);
    $('div.modal-body').html(data.body);
    $('div.modal-body').find('.selectpicker').selectpicker();
    $('div.modal-footer').html(data.footer);
}

function hideShowDetailbyDataType(dataType, detail = { length: '', precision: '', scale: '', min: '', max: '' }) {
    switch (dataType) {
        case 'char':
        case 'varchar':
        case 'nvarchar':
        case 'nchar':
            setLengthHtml(detail.length);
            $('div#minMax').html('');
            break;
        case 'float':
            setPrecisionHtml();
            $('div#minMax').html(setMinmaxHtml(detail.min, detail.max));
            break;
        case 'decimal':
            setPrecisionHtml(detail.precision, setScaleHtml(detail.scale));
            $('div#minMax').html(setMinmaxHtml(detail.min, detail.max));
            break;
        case 'int':
            $('div#dataTypeDetail').html('');
            $('div#minMax').html(setMinmaxHtml(detail.min, detail.max));
            break;
        case 'date':
        case 'datetime':
            $('div#dataTypeDetail').html('');
            $('div#minMax').html('');
            break;
        default:
            break;
    }

}

function preAddChangeList(dataArray,type){
    var result = dataArray.reduce(function(obj, item) {
        obj[item.name] = item.value;
        return obj;
    }, {});
    if(!('nullable' in result)) {
        result.nullable = 'N';
    }
    if(!('unique' in result)) {
        result.unique = 'N';
    }
    result.changeType = type;
    return result;
}

function htmlBadge(type) {
    if (type === 'add')
        return '<span class="badge badge-success">Add</span>';
    else if (type === 'edit')
        return '<span class="badge badge-warning">Edit</span>';

    return '<span class="badge badge-danger">Delete</span>';
}

function IsChange(oldData,newData){
    return true;
}
