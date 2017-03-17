window.rateMap = function(mapID, score) {
    // Retrieve current hostname, allow both http and https protocols
    var urlBase = $(location).attr('protocol') + '//' + $(location).attr('hostname');

    $.ajax({
        url: urlBase + '/api/v1/rating/' + mapID, // + '?score=' + score,
        error: function(xhr, status, error) {
            alert('Unable to handle the request due to an AJAX fault! \r\nStatus : ' + status + ' <|> Error : ' + error);
        },
        data: {'score': score},
        dataType: 'json',
        success: function(result, status, xhr) {
            alert(result);
            location.reload();
        },
        type: 'POST'
    });
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

        $.fileDownload(urlBase + '/api/v1/download/' + mapID, {
            successCallback: function (url) {
                alert('Great success!');
            },
            failCallback: function (html, url) {
                alert('Unable to handle the request due to an AJAX fault! \r\nHtml : ' + html);
            }
        });
    });
});
