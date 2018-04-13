function getProject(id) {
    $('#showMessage').empty();
    $.ajax({
        type: "GET",
        url: "/api/v1/projects/" + id,
        headers: {
            "Authorization": "Bearer " + $('input[name=accessToken]').val(),
        },
        contentType: 'application/json; charset=utf-8',
        dataType: "json",
        success: function (response) {
            $('#preloadLayout').remove();
            $('#menu').show();
            $('#header').text(response.name);
            $('#headerBread').text(response.name);
            $('input[name=projectName]').val(response.name);
            $('input[name=dbName]').val(response.dbName);
            $('input[name=dbServer]').val(response.dbServer);
            $('input[name=dbPort]').val(response.dbPort);
            $('input[name=dbUsername]').val(response.dbUsername);
            $('input[name=dbPassword]').val(response.dbPassword);
            if (response.dbType == "sqlsrv") {
                $('#dbTypeSqlSrv').attr('checked', "");

            }
            else if (response.dbType == "mysql") {
                $('#dbTypeMySql').attr('checked', "");
            }
        },
        error: function (response) {
            $('#preloadLayout').remove();
        }
    });
}

function getDatabase(projectId) {
    var refreshBtn = Ladda.create(document.querySelector('#refreshDb'));
    refreshBtn.start();

    $.ajax({
        type: "GET",
        url: "/api/v1/projects/" + projectId + "/databases",
        headers: {
            "Authorization": "Bearer " + $('input[name=accessToken]').val(),
        },
        contentType: 'application/json; charset=utf-8',
        dataType: "json",
        success: function (response) {
            refreshBtn.stop();
            $('#pills-db').append('<section class="tables"><div class="container-fluid" id="table"></div></section>');
            $.each(response, function (tableName, tableObj) {
                $('#pills-db > section.tables > div#table').append(strCard(tableName));
                $('#pills-db').find('#' + tableName + '.card > div.card-body').append(strTable(tableName, tableObj.columns));
                $('#pills-db').find('#' + tableName + '.card > div.card-body').append(strConstraint(tableName, tableObj.constraints));
                $('#pills-db').find('#' + tableName + '.card > div.card-body').append(strInstance(tableName, tableObj.instance));
                showColumn(tableName);
                $('#pills-db').find('table#' + tableName + '_instance').DataTable();
            });
        },
        error: function (response) {
            refreshBtn.stop();
            alert("Cannot get this database information please refresh this page.");
        }
    });
}

function strCard(tableName) {
    return '<div class="col-lg-12">' +
        '<div class="card" id="' + tableName + '">' +
        '<div class="card-close">' +
        '<div class="dropdown">' +
        '<button type="button" id="' + tableName + '" class="dropdown-toggle" name="visible"><i class="fa fa-eye-slash"></i></button>' +
        '<button type="button" id="closeCard3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-ellipsis-v"></i></button>' +
        '<div aria-labelledby="closeCard3" class="dropdown-menu dropdown-menu-right has-shadow">' +
        '<a href="#showColumn" class="dropdown-item" id="' + tableName + '"> <i class="fa fa-columns"></i>Columns</a>' +
        '<a href="#showConstraint" class="dropdown-item" id="' + tableName + '"> <i class="fa fa-cogs"></i>Constriants</a>' +
        '<a href="#showInstance" class="dropdown-item" id="' + tableName + '"> <i class="fa fa-bars"></i>Instance</a>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="card-header d-flex align-items-center">' +
        '<h3 class="h4">' + tableName + '</h3>' +
        '</div>' +
        '<div class="card-body" id="' + tableName + '">' +
        '</div>' +
        '</div>' +
        '</div>';
}

function strTable(tableName, columns) {
    return '<div class="table-responsive" id="' + tableName + '_column">' +
        '<table class="table table-striped">' +
        '<thead>' +
        '<tr>' +
        '<th>Column Name</th>' +
        '<th>Data Type</th>' +
        '<th>Length</th>' +
        '<th>Precision</th>' +
        '<th>Scale</th>' +
        '<th>Default</th>' +
        '<th>Nullable</th>' +
        '<th>Unique</th>' +
        '</tr>' +
        '</thead>' +
        '<tbody>' +
        strColumn(columns) +
        '</tbody>' +
        '</table>' +
        '</div>';
}

function strColumn(columnObj) {
    var strColumn = '';
    var count = 1;
    $.each(columnObj, function (key, value) {
        strColumn += '<tr id="' + key + '">' +
            '<th scope="row">' + key + '</th>' +
            '<td>' + value.type + '</td>' +
            '<td>' + value.length + '</td>' +
            '<td>' + value.precision + '</td>' +
            '<td>' + value.scale + '</td>' +
            '<td>' + value.default + '</td>' +
            '<td>' + nullableCss(value.nullable) + '</td>' +
            '<td id="unique">' + uniqueCss(false) + '</td>' +
            '</tr>';
        ++count;
    });
    return strColumn;
}

function nullableCss(isNullable) {
    return isNullable ? '<span style="color:green">Yes</span>' : '<span style="color:red">No</span>';
}

function uniqueCss(isUnque) {
    return isUnque ? '<span style="color:green">Yes</span>' : '<span style="color:red">No</span>';
}

function strInstance(tableName, instances) {
    if (instances.length < 1 || instances == undefined) {
        return '<p>Not found Instance of this table.</p>';
    }
    var header = "";
    $.each(Object.keys(instances[0]), function (index, columnName) {
        header += '<th>' + columnName + '</th>';
    });
    var records = '';
    $.each(instances, function (index, instance) {
        records += '<tr>';
        $.each(instance, function (key, value) {
            records += '<td>' + value + '</td>';
        });
        records += '</tr>';
    });
    return '<div class="table-responsive" id="' + tableName + '_instance">' +
        '<table class="table table-striped" id="' + tableName + '_instance">' +
        '<thead>' +
        '<tr>' +
        header +
        '</tr>' +
        '</thead>' +
        '<tbody>' +
        records +
        '</tbody>' +
        '</table>' +
        '</div>';
}


function strConstraint(tableName, constraintObj) {
    var pk = 'PK' in constraintObj ? strPK(tableName, constraintObj.PK) : '';
    var fks = 'FKs' in constraintObj ? strFKs(tableName, constraintObj.FKs) : '';
    var uniques = 'uniques' in constraintObj ? strUniques(tableName, constraintObj.uniques) : '';
    var chks = 'checks' in constraintObj ? strChecks(tableName, constraintObj.checks) : '';
    return '<div class="row" id="' + tableName + '_constraint">' + pk + fks + uniques + chks + '</div>';
}

function strPK(tableName, pk) {
    $.each(pk.columns, function (pkIndex, columnName) {
        $('div#' + tableName + '_column > table > tbody > tr#' + columnName + ' > th').append(' <span style="color:red">(PK)</span>');
    });
    return '<div class="col-md-12"><h1>Primary Key</h1><br>' +
        '<strong>Columns : </strong>' + pk.columns + '<hr></div>';
}

function strFKs(tableName, fks) {
    var strFks = '';
    $.each(fks, function (fkIndex, fk) {
        $.each(fk.links, function (linkIndex, link) {
            var header = (linkIndex == 0) ? '<div class="badge badge-rounded bg-blue">' + (fkIndex + 1) + '</div>' : '';
            strFks +=
                '<div class="row">' +
                '<div class="col-md-1">' +
                header +
                '</div>' +
                '<div class="col-md-2">' + link.from.columnName + '</div>' +
                '<div class="col-md-1"></div>' +
                '<div class="col-md-2">' + link.to.tableName + '</div>' +
                '<div class="col-md-2">' + link.to.columnName + '</div>' +
                '</div>';
            $('div#' + tableName + '_column > table > tbody > tr#' + link.from.columnName + ' > th').append(' <span style="color:blue">(FK)</span>');
        });

    });
    return '<div class="col-md-12"><h1>Foreign Keys <div class="badge badge-rounded bg-blue">' + fks.length + '</div></h1><br>' +
        '<div class="row">' +
        '<div class="col-md-1"><strong>No</strong></div>' +
        '<div class="col-md-2"><strong>Column Name</strong></div>' +
        '<div class="col-md-1">To <i class="fa fa-arrow-circle-right"></i></div>' +
        '<div class="col-md-2"><strong>Table Name</strong></div>' +
        '<div class="col-md-2"><strong>Column Name</strong></div>' +
        '</div>' +
        strFks +
        '<hr></div>';
}

function strChecks(tableName, chks) {
    var strChks = '';
    $.each(chks, function (ckIndex, ck) {
        $.each(ck.columns, function (columnIndex, columnName) {
            var header = (columnIndex == 0) ? '<div class="badge badge-rounded bg-blue">' + (ckIndex + 1) + '</div>' : '';
            strChks +=
                '<div class="row">' +
                '<div class="col-md-1">' +
                header +
                '</div>' +
                '<div class="col-md-2">' + columnName + '</div>' +
                '<div class="col-md-2">' + (columnName in ck.mins ? ck.mins[columnName].value : '-') + '</div>' +
                '<div class="col-md-2">' + (columnName in ck.maxs ? ck.maxs[columnName].value : '-') + '</div>' +
                '</div>';
        });

    });
    return '<div class="col-md-12"><h1>Check Constraints <div class="badge badge-rounded bg-blue">' + chks.length + '</div></h1><br>' +
        '<div class="row">' +
        '<div class="col-md-1"><strong>No</strong></div>' +
        '<div class="col-md-2"><strong>Column Name</strong></div>' +
        '<div class="col-md-2"><strong>Min</strong></div>' +
        '<div class="col-md-2"><strong>Max</strong></div>' +
        '</div>' +
        strChks +
        '<hr></div>';

}

function strUniques(tableName, uniques) {
    var strUniques = '';
    $.each(uniques, function (uniqueIndex, unique) {
        $.each(unique.columns, function (columnIndex, columnName) {
            var header = (columnIndex == 0) ? '<div class="badge badge-rounded bg-blue">' + (uniqueIndex + 1) + '</div>' : '';
            strUniques +=
                '<div class="row">' +
                '<div class="col-md-1">' +
                header +
                '</div>' +
                '<div class="col-md-11">' + columnName + '</div>' +
                '</div>';
            $('div#' + tableName + '_column > table > tbody > tr#' + columnName + ' > td#unique').html(uniqueCss(true));
        });

    });
    return '<div class="col-md-12"><h1>Unique Constraints <div class="badge badge-rounded bg-blue">' + uniques.length + '</div></h1><br>' +
        '<div class="row">' +
        '<div class="col-md-1"><strong>No</strong></div>' +
        '<div class="col-md-11"><strong>Columns</strong></div>' +
        '</div>' +
        strUniques +
        '<hr></div>';
}

function showColumn(tableId) {
    $('#' + tableId + '_constraint').hide();
    $('#' + tableId + '_instance').hide();
    $('#' + tableId + '_column').show('slow');
}

function showConstraint(tableId) {
    $('#' + tableId + '_column').hide();
    $('#' + tableId + '_instance').hide();
    $('#' + tableId + '_constraint').show('slow');

}

function showInstance(tableId) {
    $('#' + tableId + '_constraint').hide()
    $('#' + tableId + '_column').hide();
    $('#' + tableId + '_instance').show('slow');
}

function visible(tableId) {
    $('#' + tableId + ".card-body").toggle('slow');
    $('button#' + tableId + '[name=visible]').children('i').toggleClass('fa-eye-slash fa-eye');
}

function readExcel(excelFile, contentType) {
    var reader = new FileReader();
    reader.onload = function (e) {
        var binary = "";
        var bytes = new Uint8Array(e.target.result);
        var length = bytes.byteLength;
        for (var i = 0; i < length; i++) {
            binary += String.fromCharCode(bytes[i]);
        }

        var workbook = XLSX.read(binary, { type: 'binary' });
        var listOfSheet = [];
        $.each(workbook.Sheets, function (index, sheet) {
            var arraySheet = sheetToArray(sheet);
            if (arraySheet.length > 0) {
                listOfSheet.push(arraySheet);
            }
        });
        if (listOfSheet.length > 0) {
            switch (contentType) {
                case 'frFile':
                    readFrFromExcel(listOfSheet);
                    $('#pills-fr > section.tables').show();
                    $('#pills-fr').find('#showMessage').html('<div class="alert alert-success"> Found ' + frFromFile.length + ' functional requirements. </div>');
                    showFr();
                    $('div#saveFr').show();
                    break;
                case 'tcFile':
                    readTcFromExcel(listOfSheet);
                    $('#pills-tc > section.tables').show();
                    $('#pills-tc').find('#showMessage').html('<div class="alert alert-success"> Found ' + tcFromFile.length + ' functional requirements. </div>');
                    showTc();
                    $('div#saveTc').show();
                case 'rtmFile':
                    readRtmFromExcel(listOfSheet[0]);
                default:
                    break;
            }
        }

    }
    reader.readAsArrayBuffer(excelFile);

}

function showTc() {
    tcTable.clear().draw();
    $.each(tcFromFile, function (index, tc) {
        tcTable.row.add([
            strContent(tc.no, true),
            strContent(tc.type, true),
            (tc.inputs == undefined ? strContent(tc.inputs, true) :
                '<button type="button" id="' + index + '" name="tc" class="btn btn-link">' +
                tc.inputs.length +
                '</button>')
        ]).draw(false);
    });
}

function showFr() {
    frTable.clear().draw();
    $.each(frFromFile, function (index, fr) {
        frTable.row.add([
            strContent(fr.no, true),
            strContent(fr.desc),
            (fr.inputs == undefined ? strContent(fr.inputs, true) :
                '<button type="button" id="' + index + '" name="fr" class="btn btn-link">' +
                fr.inputs.length +
                '</button>')
        ]).draw(false);
    });

}



function strContent(data, required = false) {
    if (required && data == undefined) {
        return '<span style="color:red">undefined</span>';
    }
    else return data + '';
}

function setHtmlModal(id, contentType) {
    var htmlHeader, htmlInput, htmlTableHeader;
    var modal = $('#myModal');
    if (contentType == 'fr') {
        var fr = frFromFile[id];
        htmlHeader = fr.no;
        htmlTableHeader = '<th>Name <span style="color:red">*</span></th>' +
            '<th>Data Type <span style="color:red">*</span></th>' +
            '<th>Length</th>' +
            '<th>Precision</th>' +
            '<th>Scale</th>' +
            '<th>Default</th>' +
            '<th>Nullable <span style="color:red">*</span></th>' +
            '<th>Unique <span style="color:red">*</span></th>' +
            '<th>Min</th>' +
            '<th>Max</th>' +
            '<th>Column Name <span style="color:red">*</span></th>' +
            '<th>Table Name <span style="color:red">*</span></th>';
        htmlInput = '';
        $.each(fr.inputs, function (index, input) {
            htmlInput += '<tr>' +
                '<td>' + strContent(input.name, true) + '</td>' +
                '<td>' + strContent(input.dataType, true) + '</td>' +
                '<td>' + strContent(input.length, input.dataType.toString().search('char') != -1) + '</td>' +
                '<td>' + strContent(input.precision, input.dataType.toString().search('float') != -1 || input.dataType.toString().search('decimal') != -1) + '</td>' +
                '<td>' + input.scale + '</td>' +
                '<td>' + input.default + '</td>' +
                '<td>' + strContent(input.nullable, true) + '</td>' +
                '<td>' + strContent(input.unique, true) + '</td>' +
                '<td>' + input.min + '</td>' +
                '<td>' + input.max + '</td>' +
                '<td>' + strContent(input.column, true) + '</td>' +
                '<td>' + strContent(input.table, true) + '</td>' +
                '</tr>';
        });
    }
    else if (contentType == 'tc') {
        var tc = tcFromFile[id];
        htmlHeader = tc.no;
        htmlTableHeader = '<th>Name <span style="color:red">*</span></th>' +
            '<th>TestData <span style="color:red">*</span></th>';
        htmlInput = '';
        $.each(tc.inputs, function (index, input) {
            htmlInput += '<tr>' +
                '<td>' + strContent(input.name, true) + '</td>' +
                '<td>' + strContent(input.testData, true) + '</td>' +
                '</tr>';
        });
    }

    modal.find('#modalHeader').text(htmlHeader);
    modal.find('.modal-body').html(
        '<div class="table-responsive">' +
        '<table class="table table-striped">' +
        '<thead>' +
        '<tr>' +
        htmlTableHeader +
        '</tr>' +
        '</thead>' +
        '<tbody>' +
        htmlInput +
        '</tbody>' +
        '</table>' +
        '</div>'
    );
}

function readTcFromExcel(tcList) {
    tcFromFile = [];
    $.each(tcList, function (index, tc) {
        var no = isKeyExist(tc, 0, 1) ? tc[0][1] : undefined;
        var type = isKeyExist(tc, 1, 1) ? tc[1][1] : undefined;
        var inputList = [];
        for (var i = 4; i < tc.length; ++i) {
            inputList.push({
                name: (0 in tc[i]) ? tc[i][0] : undefined,
                testData: (1 in tc[i]) ? tc[i][1] : undefined,
            });
        }
        tcFromFile.push({
            no: no,
            type: type,
            inputs: inputList.length > 0 ? inputList : undefined
        });
    });
}

function readFrFromExcel(frList) {
    frFromFile = [];
    $.each(frList, function (index, fr) {
        var no = isKeyExist(fr, 0, 1) ? fr[0][1] : undefined;
        var description = isKeyExist(fr, 1, 1) ? fr[1][1] : undefined;
        var inputList = [];
        for (var i = 4; i < fr.length; ++i) {
            inputList.push({
                name: (0 in fr[i]) ? fr[i][0] : undefined,
                dataType: (1 in fr[i]) ? fr[i][1] : undefined,
                length: (2 in fr[i]) ? fr[i][2] : undefined,
                precision: (3 in fr[i]) ? fr[i][3] : undefined,
                scale: (4 in fr[i]) ? fr[i][4] : undefined,
                default: (5 in fr[i]) ? fr[i][5] : undefined,
                nullable: (6 in fr[i]) ? fr[i][6] : undefined,
                unique: (7 in fr[i]) ? fr[i][7] : undefined,
                min: (8 in fr[i]) ? fr[i][8] : undefined,
                max: (9 in fr[i]) ? fr[i][9] : undefined,
                column: (10 in fr[i]) ? fr[i][10] : undefined,
                table: (11 in fr[i]) ? fr[i][11] : undefined,
            });
        }
        frFromFile.push({
            no: no,
            desc: description,
            inputs: inputList.length > 0 ? inputList : undefined
        });
    });

}

function readRtmFromExcel(rtm) {
    rtmFromFile = [];
    for (var i = 1; i < rtm.length; ++i) {
        var frNo = isKeyExist(rtm, i, 0) ? rtm[i].shift() : undefined;
        var testCaseNos = rtm[i];
        rtmFromFile.push({
            functionalRequirementNo: frNo,
            testCaseNos: testCaseNos
        });
    }
    console.log(cleanObject(rtmFromFile));
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

function filter_array(array) {
    var index = -1,
        arr_length = array ? array.length : 0,
        resIndex = -1,
        result = [];

    while (++index < arr_length) {
        var value = array[index];

        if (value) {
            result[++resIndex] = value;
        }
    }

    return result;
}

function isKeyExist(array, dimen1, dimen2 = undefined) {
    if (dimen1 in array) {
        if (dimen2 != undefined) {
            if (!Array.isArray(array[dimen1])) {
                return false;
            }
            if (dimen2 in array[dimen1]) {
                return true;
            } else return false;
        }
    } else return false;
}

function sheetToArray(sheet) {
    var result = [];
    var row;
    var rowNum;
    var colNum;
    if (sheet['!ref'] == undefined) {
        return result;
    }
    var range = XLSX.utils.decode_range(sheet['!ref']);
    for (rowNum = range.s.r; rowNum <= range.e.r; rowNum++) {
        row = [];
        for (colNum = range.s.c; colNum <= range.e.c; colNum++) {
            var nextCell = sheet[
                XLSX.utils.encode_cell({ r: rowNum, c: colNum })
            ];
            if (typeof nextCell === 'undefined') {
                row.push(void 0);
            } else row.push(nextCell.w);
        }
        result.push(row);
    }
    return result;
};



