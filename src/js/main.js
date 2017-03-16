window.fillTable = function(ajaxTarget, tableID) {
    // Retrieve current hostname, allow both http and https protocols
    var urlBase = $(location).attr('protocol') + '//' + $(location).attr('hostname');

    switch (ajaxTarget) {
        case 'Home':
            $.ajax({ // Get the list of maps via API, loads the page quickly and retrieves data asynchronously, helpful with big data lists.
                url: urlBase + '/api/v1/getMaps',
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
    return '<a href="/mapdetails?map=' + row.map_pk + '">' + value + '</a>';
}

$(document).ready(function() {
    $("#btnDownloadMap").click(function(){
        // Retrieve current hostname, allow both http and https protocols
        var urlBase = $(location).attr('protocol') + '//' + $(location).attr('hostname');
        var mapID   = $('#btnDownloadMap').attr('kp-map-id');

        $.fileDownload(urlBase + '/api/v1/downloadMap/' + mapID, {
            successCallback: function (url) {
                alert('Great success!');
            },
            failCallback: function (html, url) {
                alert('Unable to handle the request due to an AJAX fault! \r\nHtml : ' + html);
            }
        });
    });
});
