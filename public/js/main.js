window.urlBase = $(location).attr('protocol') + '//' + $(location).attr('hostname');

// Format the name to a link
window.mapNameFormatter = function(value, row, index) {
    return '<a href="/map/' + row.map_pk + '"><span class="glyphicon glyphicon-link"></span>&nbsp;&nbsp;' + value + '</a>';
}

window.newMapRevFormatter = function(value, row, index) {
    return '<center><a href="/map/' + row.map_pk + '/updatefiles"><span class="glyphicon glyphicon-share-alt"></span>&nbsp;&nbsp;Update</a></center>';
}

window.mapAuthorFormatter = function(value, row, index) {
    return '<a href="/profile/' + row.user_pk + '"><span class="glyphicon glyphicon-link"></span>&nbsp;&nbsp;' + value + '</a>';
}

window.toggleForgot = function() {
    $(".logreg-forgot").slideToggle('slow');
}

window.submitForm = function(dataArray, path, reqType, hasFiles, isJSON, redirectTo) {
    reqType    = typeof reqType    !== 'undefined' ? reqType    : 'POST';
    hasFiles   = typeof hasFiles   !== 'undefined' ? hasFiles   : false;
    isJSON     = typeof isJSON     !== 'undefined' ? isJSON     : true;
    redirectTo = typeof redirectTo !== 'undefined' ? redirectTo : null;

    if (hasFiles) {
        $.ajax({
            url: urlBase + path,
            type: reqType,
            data: dataArray,
            cache: false,
            contentType: false,
            processData: false,
            error: function(xhr, status, error) {
                var jsonResponse = JSON.parse(xhr.responseText);
                alert('Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n' + jsonResponse.message);
            },
            success: function(result, status, xhr) {
                if (result.result != 'Success')
                    alert('Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n' + result.message);

                if (redirectTo != null)
                    window.location.href = urlBase + redirectTo;
            }
        });
    } else if(isJSON) {
        $.ajax({
            url: urlBase + path,
            type: reqType,
            data: dataArray,
            dataType: 'json',
            cache: false,
            error: function(xhr, status, error) {
                var jsonResponse = JSON.parse(xhr.responseText);
                alert('Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n' + jsonResponse.message);
            },
            success: function(result, status, xhr) {
                if (result.result != 'Success')
                    alert('Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n' + result.message);

                if (redirectTo != null)
                    window.location.href = urlBase + redirectTo;
            }
        });
    } else {
        $.ajax({
            url: urlBase + path,
            type: reqType,
            data: dataArray,
            cache: false,
            contentType: false,
            processData: false,
            error: function(xhr, status, error) {
                var jsonResponse = JSON.parse(xhr.responseText);
                alert('Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n' + jsonResponse.message);
            },
            success: function(result, status, xhr) {
                if (result.result != 'Success')
                    alert('Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n' + result.message);

                if (redirectTo != null)
                    window.location.href = urlBase + redirectTo;
            }
        });
    }
}

$(document).ready(function() {
    $('#btnDownloadMap').click(function() {
        var mapId = $(this).attr('kp-map-id');

        $.fileDownload(urlBase + '/api/v1/maps/download/' + mapId, {
            successCallback: function (url) {
                alert('Great success!');
            },
            failCallback: function (html, url) {
                alert('Unable to handle the request due to an AJAX fault! \r\nHtml : ' + html);
            }
        });
    });

    $('#btnFlagMap').click(function() {
        var mapId = $(this).attr('kp-map-id');

        $.ajax({
            url: urlBase + '/api/v1/maps/flag/' + mapId,
            type: '',
            cache: false,
            error: function(xhr, status, error) {
                var jsonResponse = JSON.parse(xhr.responseText);
                alert('Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n' + jsonResponse.message);
            },
            success: function(result, status, xhr) {
                if (result.result != 'Success')
                    alert('Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n' + result.message);
                else
                    alert('Thank you for submitting this map for review.');
            }
        });
    });

    $('#uploadMapFrm').submit(function(event) {
        event.preventDefault();
        submitForm(new FormData(this), '/api/v1/maps', 'POST', true, false, '/dashboard');
    });

    $('#editMapInfoFrm').submit(function(event) {
        event.preventDefault();
        var mapId = $(this).attr('kp-map-id');
        submitForm(new FormData(this), '/api/v1/maps/' + mapId + '/updateinfo', 'POST', false, false, '/map/' + mapId);
    });

    $('#editMapFilesFrm').submit(function(event) {
        event.preventDefault();
        var mapId = $(this).attr('kp-map-id');
        submitForm(new FormData(this), '/api/v1/maps/' + mapId + '/updatefiles', 'POST', true, false, '/map/' + mapId);
    });

    $('#userRegisterFrm').formValidation({
        framework: 'bootstrap',
        icon: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        excluded: [':disabled', ':hidden', ':not(:visible)'],
        live: 'enabled',
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
        FormValidation.AddOn.reCaptcha2.reset('captchaContainer');
        $('#userRegisterFrm').formValidation('resetForm', true);
    });

    $('#ratingStarrr').starrr({
        max: 5,
        rating: $('#ratingStarrr').attr('kp-map-rating'),
        change: function(e, value){
            if (value) {
                var mapId = $('#ratingStarrr').attr('kp-map-id');

                $.ajax({
                    url: urlBase + '/api/v1/rating/' + mapId,
                    error: function(xhr, status, error) {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        $('#ratingResultError > .message').text(jsonResponse.message);
                        $('#ratingResultError').show();
                    },
                    data: {'score': value},
                    dataType: 'json',
                    success: function(result, status, xhr) {
                        $('#ratingResultSuccess > .message').text(result.data);
                        $('#ratingResultSuccess').show();

                        $.ajax({
                            url: urlBase + '/api/v1/rating/' + mapId,
                            error: function(xhr, status, error) {
                            },
                            data: {'score': value},
                            dataType: 'json',
                            success: function(result, status, xhr) {
                                $('#ratingStarrr').attr('kp-map-rating', result.data.avg_rating);
                                $('#ratingAvg').html(result.data.avg_rating + '<small> / 5</small>');
                                $('#ratingFive').text(result.data.rating_five + ' Vote(s)');
                                $('#ratingFour').text(result.data.rating_four + ' Vote(s)');
                                $('#ratingThree').text(result.data.rating_three + ' Vote(s)');
                                $('#ratingTwo').text(result.data.rating_two + ' Vote(s)');
                                $('#ratingOne').text(result.data.rating_one + ' Vote(s)');
                            },
                            type: 'GET'
                        });
                    },
                    type: 'POST'
                });
            }
        }
    });

    var navListItems = $('div.setup-panel > div > a'),
        allWells     = $('.setup-content'),
        allNextBtn   = $('.nextBtn');

    allWells.hide();

    navListItems.click(function (e) {
        e.preventDefault();
        var $item   = $(this),
            $target = $($item.attr('href'));

        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-primary').addClass('btn-default');
            $item.addClass('btn-primary');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }
    });

    allNextBtn.click(function(){
        var curStep        = $(this).closest(".setup-content"),
            curStepBtn     = curStep.attr("id"),
            nextStepWizard = $('div.setup-panel > div > a[href="#' + curStepBtn + '"]').parent().next().children("a"),
            curInputs      = curStep.find("input[type='text'],input[type='url'],input[type='file'],select,textarea"),
            isValid        = true;
        $(".form-group").removeClass("has-error");

        for(var i = 0; i < curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        if (isValid)
            nextStepWizard.removeAttr('disabled').removeClass('disabled').trigger('click');
    });

    $('div.setup-panel > div > a.btn-primary').trigger('click');
});
