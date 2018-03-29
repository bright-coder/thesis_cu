function getProject() {
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
            $('#header').text(response.projectName);
            $('#headerBread').text(response.projectName);
            $('input[name=projectName]').val(response.projectName);
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
            //$('#pills-project').show();
        },
        error: function (response) {
            $('#preloadLayout').remove();
        }
    });
}

function getDatabase() {
    //Ladda.bind( 'button#refreshDb' );
    var v = Ladda.create(document.querySelector('#refreshDb'));

    // Start loading
    v.start();

    $.ajax({
        type: "GET",
        url: "/api/v1/projects/" + id + "/databases",
        headers: {
            "Authorization": "Bearer " + $('input[name=accessToken]').val(),
        },
        contentType: 'application/json; charset=utf-8',
        dataType: "json",
        success: function (response) {
            v.stop();
            console.log(response);
            $('#pills-db').append('<section class="tables"><div class="container-fluid" id="table"></div></section>');
            $.each(response, function (tableName, tableObj) {
                $('section.tables > div#table').append(strCard(tableName));
                $('div#'+tableName+'.card > div.card-body').append(strTable(tableName, tableObj.columns));
                $('div#'+tableName+'.card > div.card-body').append(strConstraint(tableName, tableObj.constraints));
                $('div#'+tableName+'.card > div.card-body').append(strInstance(tableName, tableObj.instance));
                showColumn(tableName);
                $('table#' + tableName + '_instance').DataTable();
            });
        },
        error: function (response) {
            lrefreshDb.stop();
            //var response = response.responseJSON;
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
        strColumn += '<tr id="'+key+'">' +
            '<th scope="row">' + key + '</th>' +
            '<td>' + value.type + '</td>' +
            '<td>' + value.length + '</td>' +
            '<td>' + value.precision + '</td>' +
            '<td>' + value.scale + '</td>' +
            '<td>' + value.default + '</td>' +
            '<td>' + nullableCss(value.nullable) + '</td>' +
            '<td id="unique">'+uniqueCss(false)+'</td>' +
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
    var pk = 'PK' in constraintObj ? strPK(tableName,constraintObj.PK) : '';
    var fks = 'FKs' in constraintObj ? strFKs(tableName,constraintObj.FKs) : '';
    var uniques = 'uniques' in constraintObj ? strUniques(tableName,constraintObj.uniques) : '';
    var chks = 'checks' in constraintObj ? strChecks(tableName,constraintObj.checks) : '';
    return '<div class="row" id="' + tableName + '_constraint">' + pk + fks + uniques + chks + '</div>';
}

function strPK(tableName,pk) {
    $.each(pk.columns, function (pkIndex, columnName){
        $('div#'+tableName+'_column > table > tbody > tr#'+columnName+' > th').append(' <span style="color:red">(PK)</span>');
    });
    return '<div class="col-md-12"><h1>Primary Key</h1><br>' +
        '<strong>Columns : </strong>' + pk.columns + '<hr></div>';
}

function strFKs(tableName,fks) {
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
                $('div#'+tableName+'_column > table > tbody > tr#'+link.from.columnName+' > th').append(' <span style="color:blue">(FK)</span>');
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

function strChecks(tableName,chks) {
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

function strUniques(tableName,uniques) {
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
                $('div#'+tableName+'_column > table > tbody > tr#'+columnName+' > td#unique').html(uniqueCss(true));
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


