window.rateMap = function(mapID, score) {
    // Retrieve current hostname, allow both http and https protocols
    var urlBase = $(location).attr('protocol') + '//' + $(location).attr('hostname');

    $.ajax({
        url: urlBase + '/api/v1/rating/' + mapID,
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
    return '<a href="/mapdetails?map=' + row.map_pk + '"><span class="glyphicon glyphicon-link"></span>&nbsp;&nbsp;' + value + '</a>';
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

    $('#userRegisterFrm').formValidation({
        framework: 'bootstrap',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        exclude: ':disabled',
        addOns: {
            reCaptcha2: {
                element: 'captchaContainer',
                language: 'en',
                theme: 'light',
                siteKey: '6LcTcBkUAAAAADJcfF5RhZL_3I8ALCacwmHztWeu',
                timeout: 120,
                message: 'The captcha is not valid'
            }
        },
        fields: {
            username: {
                validators: {
                    stringLength: {
                        min: 2,
                        message: 'Username should be longer than two characters'
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9_\.]+$/,
                        message: 'Username can only consist of alphanumber, dot and underscore'
                    },
                    notEmpty: {
                        message: 'Please supply your username'
                    }
                }
            },
            emailAddress: {
                validators: {
                    emailAddress: {
                        message: 'Email address must be valid'
                    },
                    notEmpty: {
                        message: 'Please supply your email address'
                    }
                }
            },
            password: {
                validators: {
                    stringLength: {
                        min: 6,
                        message: 'Password requires a minimum of 6 characters'
                    },
                    notEmpty: {
                        message: 'Please supply your password'
                    }
                }
            },
            confirmPassword: {
                validators: {
                    identical: {
                        field: 'password',
                        message: 'Passwords must match'
                    }
                }
            }
        }
    });

    $('#userRegResetBtn').on('click', function() {
        // Reset the recaptcha
        FormValidation.AddOn.reCaptcha2.reset('captchaContainer');

        // Reset form
        $('#userRegisterFrm').formValidation('resetForm', true);
    });

    $('#ratingStarrr').starrr({
        change: function(e, value){
            if (value) {
                // Retrieve current hostname, allow both http and https protocols
                var urlBase = $(location).attr('protocol') + '//' + $(location).attr('hostname');
                var mapID   = $('#ratingStarrr').attr('kp-map-id');

                $.ajax({
                    url: urlBase + '/api/v1/rating/' + mapID,
                    error: function(xhr, status, error) {
                        
                        $('#ratingResultError > .message').text(xhr.responseJSON.message);
                        $('#ratingResultError').show();
                    },
                    data: {'score': value},
                    dataType: 'json',
                    success: function(result, status, xhr) {
                        $('#ratingResultSuccess > .message').text(result.data);
                        $('#ratingResultSuccess').show();
                    },
                    type: 'POST'
                });
            }
        }
    });
});

window.toggleForgot = function() {
    $(".logreg-forgot").slideToggle('slow');
}
