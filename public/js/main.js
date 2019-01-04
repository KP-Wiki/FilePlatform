window.urlBase = `${$(location).attr("protocol")}//${$(location).attr("hostname")}:${$(location).attr("port")}`;

// Format the name to a link
window.mapNameFormatter = (value, row, index) => {
    return `<a href="/map/${row.map_pk}"><span class="glyphicon glyphicon-link"></span>&nbsp;&nbsp;${value}</a>`;
}

window.mapAuthorFormatter = (value, row, index) => {
    return `<a href="/profile/${row.user_pk}"><span class="glyphicon glyphicon-link"></span>&nbsp;&nbsp;${value}</a>`;
}

window.newMapRevFormatter = (value, row, index) => {
    return `<center><a href="/map/${row.map_pk}/updatefiles"><span class="glyphicon glyphicon-share-alt"></span>&nbsp;&nbsp;Update</a></center>`;
}

window.adminFlagPickupFormatter = (value, row, index) => {
    return `<center><button onclick="adminFlagBtnClick('pickup', ${row.flag_pk})" class="btn btn-primary" role="button">Pickup</button></center>`;
}

window.adminFlagCloseFormatter = (value, row, index) => {
    return `<center><button onclick="adminFlagBtnClick('close', ${row.flag_pk})" class="btn btn-primary" role="button">close</button></center>`;
}

window.adminFlagResolveFormatter = (value, row, index) => {
    return `<center><button onclick="adminFlagBtnClick('resolve', ${row.flag_pk})" class="btn btn-primary" role="button">Resolve (disable)</button></center>`;
}

window.adminFlagFileNameFormatter = (value, row, index) => {
    return `<a href="${urlBase}/api/v1/maps/download/${row.rev_fk}">${value}</a>`;
}

window.adminMapAcceptFormatter = (value, row, index) => {
    return `<center><button onclick="adminQueueBtnClick('accept', ${row.rev_pk})" class="btn btn-success" role="button">Accept</button></center>`;
}

window.adminMapRejectFormatter = (value, row, index) => {
    return `<center><button onclick="adminQueueBtnClick('reject', ${row.rev_pk})" class="btn btn-danger" role="button">Reject</button></center>`;
}

window.toggleForgot = () => {
    $(".logreg-forgot").slideToggle("slow");
}

window.adminFlagBtnClick = (action, flagId) => {
    const refreshFunc = (data) => {
        $("#flagQueueTable").bootstrapTable("refresh");
        $("#flagTable").bootstrapTable("refresh");
    };
    switch(action) {
        case "pickup": {
            $.post(`${urlBase}/api/v1/flags/${flagId}/pickup`, refreshFunc);
            break;
        }
        case "close": {
            $.post(`${urlBase}/api/v1/flags/${flagId}/close`, refreshFunc);
            break;
        }
        case "resolve": {
            $.post(`${urlBase}/api/v1/flags/${flagId}/resolve`, refreshFunc);
            break;
        }
        default: alert("Invalid use of this function.");
    }
}

window.adminQueueBtnClick = (action, revId) => {
    const refreshFunc = (data) => {
        $("#mapQueueTable").bootstrapTable("refresh");
    };
    switch(action) {
        case "accept": {
            $.post(`${urlBase}/api/v1/maps/${revId}/accept`, refreshFunc);
            break;
        }
        case "reject": {
            $.post(`${urlBase}/api/v1/maps/${revId}/reject`, refreshFunc);
            break;
        }
        default: alert("Invalid use of this function.");
    }
}

window.submitForm = (dataArray, path, reqType, hasFiles, isJSON, redirectTo) => {
    reqType = typeof reqType !== "undefined" ? reqType : "POST";
    hasFiles = typeof hasFiles !== "undefined" ? hasFiles : false;
    isJSON = typeof isJSON !== "undefined" ? isJSON : true;
    redirectTo = typeof redirectTo !== "undefined" ? redirectTo : null;

    if (hasFiles) {
        $.ajax({
            url: urlBase + path,
            type: reqType,
            data: dataArray,
            cache: false,
            contentType: false,
            processData: false,
            error: (xhr, status, error) => {
                const jsonResponse = JSON.parse(xhr.responseText);
                alert(`Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n${jsonResponse.message}`);
            },
            success: (result, status, xhr) => {
                if (result.result != "Success")
                    alert(`Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n${result.message}`);

                if (redirectTo != null)
                    window.location.href = urlBase + redirectTo;
            }
        });
    } else if(isJSON) {
        $.ajax({
            url: urlBase + path,
            type: reqType,
            data: dataArray,
            dataType: "json",
            cache: false,
            error: (xhr, status, error) => {
                const jsonResponse = JSON.parse(xhr.responseText);
                alert(`Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n${jsonResponse.message}`);
            },
            success: (result, status, xhr) => {
                if (result.result != "Success")
                    alert(`Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n${result.message}`);

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
            error: (xhr, status, error) => {
                const jsonResponse = JSON.parse(xhr.responseText);
                alert(`Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n${jsonResponse.message}`);
            },
            success: (result, status, xhr) => {
                if (result.result != "Success")
                    alert(`Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n${result.message}`);

                if (redirectTo != null)
                    window.location.href = urlBase + redirectTo;
            }
        });
    }
}

$(document).ready(() => {
    $("#btnDownloadMap").click((e) => {
        const revId = $(e.currentTarget).attr("kp-rev-id");
        $.fileDownload(`${urlBase}/api/v1/maps/download/${revId}`, {
            successCallback: (url) => {
                alert("Great success!");
            },
            failCallback: (html, url) => {
                alert(`Unable to handle the request due to an AJAX fault! \r\nHtml : ${html}`);
            }
        });
    });

    $("#btnFlagMap").click((e) => {
        const revId = $(e.currentTarget).attr("kp-rev-id");
        $.ajax({
            url: `${urlBase}/api/v1/flags/map/${revId}`,
            type: "POST",
            cache: false,
            error: (xhr, status, error) => {
                const jsonResponse = JSON.parse(xhr.responseText);
                alert(`Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n${jsonResponse.message}`);
            },
            success: (result, status, xhr) => {
                if (result.result != "Success") {
                    alert(`Unable to handle the request due to an AJAX fault!\r\n\r\nMessage:\r\n${result.message}`);
                } else {
                    alert("Thank you for submitting this map for review.");
                }
            }
        });
    });

    $("#uploadMapFrm").submit((e) => {
        e.preventDefault();
        submitForm(new FormData(e.currentTarget), "/api/v1/maps", "POST", true, false, "/dashboard");
    });

    $("#editMapInfoFrm").submit((e) => {
        e.preventDefault();
        const mapId = $(e.currentTarget).attr("kp-map-id");
        submitForm(new FormData(e.currentTarget), `/api/v1/maps/${mapId}/updateinfo`, "POST", false, false, `/map/${mapId}`);
    });

    $("#editMapFilesFrm").submit((e) => {
        e.preventDefault();
        const mapId = $(e.currentTarget).attr("kp-map-id");
        submitForm(new FormData(e.currentTarget), `/api/v1/maps/${mapId}/updatefiles`, "POST", true, false, `/map/${mapId}`);
    });

    $("#userRegisterFrm").formValidation({
        framework: "bootstrap",
        icon: {
            valid: "glyphicon glyphicon-ok",
            invalid: "glyphicon glyphicon-remove",
            validating: "glyphicon glyphicon-refresh"
        },
        excluded: [":disabled", ":hidden", ":not(:visible)"],
        live: "enabled",
        addOns: {
            reCaptcha2: {
                element: "captchaContainer",
                language: "en",
                theme: "light",
                siteKey: "6LcTcBkUAAAAADJcfF5RhZL_3I8ALCacwmHztWeu",
                timeout: 120,
                message: "The captcha is not valid"
            }
        },
        fields: {
            username: {
                validators: {
                    stringLength: {
                        min: 2,
                        message: "Username should be longer than two characters"
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9_\.]+$/,
                        message: "Username can only consist of alphanumber, dot and underscore"
                    },
                    notEmpty: {
                        message: "Please supply your username"
                    }
                }
            },
            emailAddress: {
                validators: {
                    emailAddress: {
                        message: "Email address must be valid"
                    },
                    notEmpty: {
                        message: "Please supply your email address"
                    }
                }
            },
            password: {
                validators: {
                    stringLength: {
                        min: 6,
                        message: "Password requires a minimum of 6 characters"
                    },
                    notEmpty: {
                        message: "Please supply your password"
                    }
                }
            },
            confirmPassword: {
                validators: {
                    identical: {
                        field: "password",
                        message: "Passwords must match"
                    }
                }
            }
        }
    });

    $("#userRegResetBtn").on("click", () => {
        FormValidation.AddOn.reCaptcha2.reset("captchaContainer");
        $("#userRegisterFrm").formValidation("resetForm", true);
    });

    $("#ratingStarrr").starrr({
        max: 5,
        rating: $("#ratingStarrr").attr("kp-map-rating"),
        change: (e, value) => {
            if (value) {
                const mapId = $("#ratingStarrr").attr("kp-map-id");

                $.ajax({
                    url: `${urlBase}/api/v1/rating/${mapId}`,
                    error: (xhr, status, error) => {
                        const jsonResponse = JSON.parse(xhr.responseText);
                        $("#ratingResultError > .message").text(jsonResponse.message);
                        $("#ratingResultError").show();
                    },
                    data: {"score": value},
                    dataType: "json",
                    success: (result, status, xhr) => {
                        $("#ratingResultSuccess > .message").text(result.data);
                        $("#ratingResultSuccess").show();

                        $.ajax({
                            url: `${urlBase}/api/v1/rating/${mapId}`,
                            error: (xhr, status, error) => {
                            },
                            data: {"score": value},
                            dataType: "json",
                            success: (result, status, xhr) => {
                                $("#ratingStarrr").attr("kp-map-rating", result.data.avg_rating);
                                $("#ratingAvg").html(result.data.avg_rating + "<small> / 5</small>");
                                $("#ratingFive").text(result.data.rating_five + " Vote(s)");
                                $("#ratingFour").text(result.data.rating_four + " Vote(s)");
                                $("#ratingThree").text(result.data.rating_three + " Vote(s)");
                                $("#ratingTwo").text(result.data.rating_two + " Vote(s)");
                                $("#ratingOne").text(result.data.rating_one + " Vote(s)");
                            },
                            type: "GET"
                        });
                    },
                    type: "POST"
                });
            }
        }
    });

    const navListItems = $("div.setup-panel > div > a");
    const allWells = $(".setup-content");
    const allNextBtn = $(".nextBtn");
    allWells.hide();

    navListItems.click((e) => {
        e.preventDefault();
        const $item = $(e.currentTarget);
        const $target = $($item.attr("href"));

        if (!$item.hasClass("disabled")) {
            navListItems.removeClass("btn-primary").addClass("btn-default");
            $item.addClass("btn-primary");
            allWells.hide();
            $target.show();
            $target.find("input:eq(0)").focus();
        }
    });

    allNextBtn.click((e) => {
        const curStep = $(e.currentTarget).closest(".setup-content");
        const curStepBtn = curStep.attr("id");
        const nextStepWizard = $(`div.setup-panel > div > a[href="#${curStepBtn}"]`).parent().next().children("a");
        const curInputs = curStep.find("input[type=\"text\"],input[type=\"url\"],input[type=\"file\"],select,textarea");
        const isValid = true;
        $(".form-group").removeClass("has-error");

        for(let i = 0; i < curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        if (isValid)
            nextStepWizard.removeAttr("disabled").removeClass("disabled").trigger("click");
    });

    $("div.setup-panel > div > a.btn-primary").trigger("click");
});
