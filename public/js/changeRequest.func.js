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

function setModalHtml(type, input = null, indexFr) {
    formAddChangeInput.attr('name',type);
    if(type == 'add') {
        modalAddChangeInput.find('.modal-title').html('<span class="badge badge-success">Add</span> <span class="badge badge-light">new input</span>');
        modalAddChangeInput.find('.modal-body').load('/vendor/html-layout/change-request/modal-body.html', function(){
            modalAddChangeInput.find('.selectpicker').selectpicker();
        });
        modalAddChangeInput.find('.modal-footer').load('/vendor/html-layout/change-request/modal-footer.html #addEdit');
        modalAddChangeInput.find('.selectpicker').selectpicker();
    }
    else if(type == 'edit') {
        modalAddChangeInput.find('.modal-title').html('<span class="badge badge-warning">Edit</span> <span class="badge badge-light">'+input.name+'</span>');
        modalAddChangeInput.find('.modal-body').load('/vendor/html-layout/change-request/modal-body.html', function (){
            $('#inputName').replaceWith(input.name);
            modalAddChangeInput.find('.selectpicker').selectpicker();
            modalAddChangeInput.find('.selectpicker').selectpicker('val', input.dataType);
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
            var detail = { 
                length: cleanContent(input.length), 
                precision: cleanContent(input.precision), 
                scale: cleanContent(input.scale), 
                min: cleanContent(input.min), 
                max: cleanContent(input.max) 
            };
            hideShowDetailbyDataType(input.dataType, detail);
        });
        modalAddChangeInput.find('.modal-footer').load('/vendor/html-layout/change-request/modal-footer.html #addEdit', function(){
            $('#submitChangeInput').attr('name', indexFr);
        });
    }
    else {
        modalAddChangeInput.find('.modal-title').html('<span class="badge badge-danger">Delete</span> <span class="badge badge-light">'+input.name+'</span>');
        modalAddChangeInput.find('.modal-body').html('');
        modalAddChangeInput.find('.modal-footer').load('/vendor/html-layout/change-request/modal-footer.html #delete', function(){
            $('#submitChangeInput').attr('name', indexFr);
        });
    }
}

function setLengthHtml(length = '') {
    $('div#dataTypeDetail').html(
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Length <span style="color:red">*</span></label>' +
        '<div class="col-sm-6">' +
        '<input id="inputLength" type="number" name="length" placeholder="input Length" value="' + length + '" class="form-control form-control-success" required>' +
        '</div>' +
        '</div>');
}

function setPrecisionHtml(precision = '', scale = '') {
    $('div#dataTypeDetail').html(
        '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Precision <span style="color:red">*</span></label>' +
        '<div class="col-sm-6">' +
        '<input id="inputPrecision" type="number" value="' + precision + '" name="precision" placeholder="precision value." class="form-control form-control-success" required>' +
        '</div>' +
        '</div>' +
        scale +
        '</div>');
}

function setScaleHtml(scale = '') {
    return '<div class="form-group row">' +
        '<label class="col-sm-3 form-control-label">Scale <span style="color:red">*</span></label>' +
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

function preAddChangeList(dataArray,type , input = null){
    var result = dataArray.reduce(function(obj, item) {
        obj[item.name] = item.value;
        return obj;
    }, {});
    if(type == 'add') {
        if(!('nullable' in result)) {
            result.nullable = 'N';
        }
        if(!('unique' in result)) {
            result.unique = 'N';
        }
    }
    else if(type == 'edit') {
        if(result.dataType == input.dataType) {
            delete result.dataType;
        }
        if('length' in result){
            if(result.length == input.length) {
                delete result.length;
            }
        }
        if('precision' in result){
            if(result.precision == input.precision) {
                delete result.precision;
            }
        }
        if('scale' in result){
            if(result.scale == input.scale) {
                delete result.scale;
            }
        }
        if('default' in result){
            if(result.default == input.default || result.default == '') {
                delete result.default;
            }
        }
        if('min' in result){
            if(result.min == input.min) {
                delete result.min;
            }
        }
        if('max' in result){
            if(result.max == input.max) {
                delete result.max;
            }
        }
        if('nullable' in result){
            if(result.nullable == input.nullable) {
                delete result.nullable;
            }
        }
        if('unique' in result){
            if(result.unique == input.unique) {
                delete result.unique;
            }
        }
        result.frId = input.id;
    }
    else if(type == 'delete') {
        result = { frId: input.id };
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

function cleanObject(obj) {
    Object.keys(obj).forEach(function (key) {
        if (obj[key] instanceof Array || typeof obj[key] === "object") {
            obj[key] = cleanObject(obj[key]);
            if(obj[key] instanceof Array) {
                obj[key] = filter_array(obj[key]);
            }
        }
        else if (!obj[key]) {
            delete obj[key];
        }
    });
    return obj;
}