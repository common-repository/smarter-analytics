jQuery(function () {
    var $ = jQuery;

    var adminContainer = $(".smarter-analytics-admin");
    var formTableBody = adminContainer.find(".smarter-analytics-admin-table tbody");
    var exclusionsUserTableBody = adminContainer.find(".smarter-analytics-user-exclusions-table tbody");
    var exclusionsIPTableBody = adminContainer.find(".smarter-analytics-ip-exclusions-table tbody");

    addNewCode($, formTableBody, adminContainer);
    inputWatchers($, formTableBody);
    removeWatcher($, formTableBody);
    addCodeToDropdownWatcher($, adminContainer.find(".smarter-analytics-code"));

    trackingUserWatcher($, exclusionsUserTableBody);
    trackingIPWatcher($, exclusionsIPTableBody);


    resetAllSettings($, adminContainer);
});

var addCodeToDropdownWatcher = function ($, input) {
    var updateAdditionalsToDropdowns = function () {
        var defaultDropdowns = $(".default_dropdown");
        $.each(input, function (i, o) {
            var value = $(this).val();
            var optionSelector = "option[value='" + value + "']";
            var option = defaultDropdowns.eq(0).find(optionSelector);
            if (option.length == 0) {
                $.each(defaultDropdowns, function (i, o) {
                    $(this).append('<option value="' + value + '">' + value + '</option>');
                });
            }
        });
    }

    var updateSubtractionsFromDropdowns = function () {
        var defaultDropdowns = $(".default_dropdown");
        $.each(defaultDropdowns.find("option"), function (i, o) {
            var value = $(this).val();
            if (value != "" && value != null) {
                var found = false;
                $.each(input, function (i, o) {
                    if (value == $(this).val()) {
                        found = true;
                        return;
                    }
                });
                if (found == false) {
                    $(this).remove();
                }
            }

        });
    }

    
    $.each(input, function (i, o) {
        $(this).unbind("blur");
        $(this).on("blur", function () {
            updateAdditionalsToDropdowns();
            updateSubtractionsFromDropdowns();
        });
    });
}

var removeCodeFromDropdowns = function ($, value) {
    $.each($(".default_dropdown"), function (i, o) {
        var option = $(this).find("option[value='" + value + "']");
        if (option.text() == value) { option.remove(); }
    });
}

var addNewCode = function ($, formTableBody, adminContainer) {
    if (formTableBody.find("tr").length > 0) {
        formTableBody.closest("table").find("tfoot").hide();
    }

    adminContainer.find("#add-code-button").on("click", function () {
        var newId = $(".smarter-analytics-code").length + 1;
        var label = ("Analytics Code #" + newId);

        var row = '<tr id="smarter-analytics-code-' + newId + '" class="alternate"><td><label>' + label + '</label></td><td class="inline-edit-row"><span class="input-text-wrap"><input class="smarter-analytics-code" type="text" value="" placeholder="UA-XXXXX-Y" /></span></td><td><button class="button button-default remove-existing-code">Remove</button></td></tr>';
        formTableBody.append(row);

        if (formTableBody.find("tr").length > 0) {
            formTableBody.closest("table").find("tfoot").hide();
        }
        
        removeWatcher($, formTableBody);
        addCodeToDropdownWatcher($, adminContainer.find(".smarter-analytics-code"));
        inputWatchers($, formTableBody);
    });
}


var inputWatchers = function ($, formTableBody) {
    var codesInput = $("#codes");
    
    var inputWatcher = function () {
        var codes = "";
        $.each(formTableBody.find(".smarter-analytics-code"), function (i, o) {
            codes += $(this).val() + "|";
        });
        
        console.log(codes);
        $("#codes").val(codes);
    }

    $.each(formTableBody.find(".smarter-analytics-code"), function (i, o) {
        $(this).unbind("keyup");
        $(this).on("keyup paste blur", function () {
            inputWatcher();
        });
    });

    inputWatcher();
}

var removeWatcher = function ($, formTableBody) {
    var button = formTableBody.find(".remove-existing-code");
    button.unbind("click")
    button.on("click", function (e) {
        e.preventDefault();

        var row = $(this).closest("tr");

        removeCodeFromDropdowns($, row.find(".smarter-analytics-code").val());

        $(this).closest("tr").slideUp().remove();

        if (formTableBody.find("tr").length == 0) {
            formTableBody.closest("table").find("tfoot").show();
        }

        inputWatchers($, formTableBody);
    });
}

var trackingIPWatcher = function ($, exclusionsTableBody) {
    var trackIpsTextarea = exclusionsTableBody.find(".track-ips");

    var createIPTrackingExclusionsString = function () {
        exclusionsString = trackIpsTextarea.val().replace(/ /g, "");

        $("#ip_tracking_exclusions").val(exclusionsString);
    }

    trackIpsTextarea.on("blur", function () {
        createIPTrackingExclusionsString();
    });

    exclusionsTableBody.find("#add-ip-range-button").on("click", function (e) {
        e.preventDefault();
        var low = exclusionsTableBody.find("#add-ip-range-low").val();
        var high = exclusionsTableBody.find("#add-ip-range-high").val();
        trackIpsTextarea.val(trackIpsTextarea.val() + "," + low + "-" + high);
        createIPTrackingExclusionsString();
    });
}

var trackingUserWatcher = function ($, userExclusionsTableBody) {
    var trackCheckboxes = userExclusionsTableBody.find(".track-user");
    var trackUsersTextarea = userExclusionsTableBody.find(".track-users");

    var createUserTrackingExclusionsString = function () {
        var exclusionsString = "";
        $.each(trackCheckboxes, function (i, o) {
            if ($(this).is(":checked")) {
                exclusionsString += $(this).attr("data-usertype") + ",";
            }
        });

        exclusionsString += trackUsersTextarea.val().replace(/ /g, "");

        $("#user_tracking_exclusions").val(exclusionsString);
    }

    trackCheckboxes.on("click", function () {
        createUserTrackingExclusionsString();
    });

    trackUsersTextarea.on("blur", function () {
        createUserTrackingExclusionsString();
    });
}

var resetAllSettings = function($, adminContainer) {
    adminContainer.find(".show-confirm").on("click", function(e) {
        e.preventDefault();
        $(this).fadeOut('fast', function() {
            adminContainer.find(".reset-confirm").fadeIn();
        });
    });
}