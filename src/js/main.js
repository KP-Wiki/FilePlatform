// Format the name to an a link
window.mapNameFormatter = function(value, row, index) {
    return '<a href="/mapdetails/' + row.map_pk + '"><span class="glyphicon glyphicon-link"></span>&nbsp;&nbsp;' + value + '</a>';
}

window.mapAuthorFormatter = function(value, row, index) {
    return '<a href="/profile/' + row.user_pk + '"><span class="glyphicon glyphicon-link"></span>&nbsp;&nbsp;' + value + '</a>';
}

window.toggleForgot = function() {
    $(".logreg-forgot").slideToggle('slow');
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
        max: 5,
        rating: $('#ratingStarrr').attr('kp-map-rating'),
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

                        $.ajax({
                            url: urlBase + '/api/v1/rating/' + mapID,
                            error: function(xhr, status, error) {
                            },
                            data: {'score': value},
                            dataType: 'json',
                            success: function(result, status, xhr) {
                                $('#ratingStarrr').attr('kp-map-rating', result.data.avg_rating);
                                $('#ratingAvg').html(result.data.avg_rating + '<small> / 5</small>');
                                $('#ratingFive').text(result.data.rating_five);
                                $('#ratingFour').text(result.data.rating_four);
                                $('#ratingThree').text(result.data.rating_three);
                                $('#ratingTwo').text(result.data.rating_two);
                                $('#ratingOne').text(result.data.rating_one);
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
        var $target = $($(this).attr('href')),
            $item   = $(this);

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

        for(var i=0; i<curInputs.length; i++){
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
