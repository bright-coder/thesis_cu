function cleanContent(data) {
    return !data ? '' : data; 
}

function htmlModalAddInput(){
    var html = {};
    html.header = 'Add new input';
    html.body = '';
    html.footer = '';
    return html;
}

function setHtmlModal(data) {
    $('#modalHeader').html(data.header);
    $('div.modal-body').html(data.body);
    $('div.modal-footer').html(data.footer);
}