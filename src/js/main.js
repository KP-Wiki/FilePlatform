// Retrieve current hostname, allow both http and https protocols
var urlBase = $(location).attr('protocol') + '//' + $(location).attr('hostname');

window.fillTable = function(ajaxTarget, tableID) {
    switch (ajaxTarget) {
        case 'Home':
            $.ajax({ // Get the list of files via API, loads the page quickly and retrieves data asynchronously, helpful with big data lists.
                url: urlBase + '/api/v1/getFiles',
                error: function(xhr, status, error) {
                    alert('Unable to handle the request due to an AJAX fault! \r\nStatus : ' + status + ' <|> Error : ' + error);
                },
                dataType: 'json',
                success: function(result, status, xhr) {
                    $(tableID).bootstrapTable("load", result);
                },
                type: 'GET'
            });
            break;
        default:
            alert('Unable to handle request with < ajaxTarget = ' + ajaxTarget + ', tableID = ' + tableID + ' >');
    }
}

// Format the name to an a link
window.detailUrlFormatter = function(value, row, index) {
    return '<a href="/filedetails?file=' + row.file_pk + '">' + value + '</a>';
}
