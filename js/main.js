
requirejs.config({

    baseUrl : "js/",

    paths : {
        // Note the below is a "protocol relative" url (eg. it uses the protocol [http|https] of the current page)
        jquery : "jquery-1.11.0" //"//code.jquery.com/jquery-1.10.2.min"
    },

    shim : {
        // Shim to load in bootstrap javascript, since it isn't AMD-compliant
        bootstrap : {
            deps : [ "jquery" ],
            exports : "$.fn.popover"
        },

        "bootstrap-datepicker" : {
            deps : [ "jquery", "bootstrap" ],
            exports : "$.fn.datepicker"
        }
    }

});


function registerBuildUI(fn) {
    if (fn instanceof Function) {
        if (registerBuildUI.jQuery) {
            fn(registerBuildUI.jQuery);
        } else {
            registerBuildUI.callbacks = registerBuildUI.callbacks || [];
            registerBuildUI.callbacks.push(fn);
        }
    } else
        throw new Error("Parameter must be a Function");
} // registerBuildUI

require(["jquery", "bootstrap"], function($) {
    $(function($) {
        registerBuildUI.jQuery = $;
        if (registerBuildUI.callbacks) {
            for (var i = 0; i < registerBuildUI.callbacks.length; ++i) {
                registerBuildUI.callbacks[i]($);
            } // for
        }
    });
});

function requiredField(elem, errorMsg) {
    var rv = elem.val();
    if ((rv == "") || (rv == null)) {
        elem.tooltip("destroy")
            .addClass("error")
            .data("title", errorMsg)
            .tooltip();
    } else {
        elem.tooltip("destroy")
            .removeClass("error")
            .data("title", "");
    }
    return rv;
} // requiredField


function formatNumber(val, places) {
    if (!formatNumber._decimalSeparator)
        formatNumber._decimalSeparator = Number("1.2").toLocaleString().substr(1,1);

    val = Number(val);
    var AmountWithCommas = Number(val).toLocaleString();
    var arParts = String(AmountWithCommas).split(formatNumber._decimalSeparator);
    var intPart = arParts[0];
    var decPart = val.toFixed(places).split(".")[1];
    return intPart + (decPart != null ? formatNumber._decimalSeparator + decPart : "");
} // formatNumber


function formatDate(date, fmt) {
    if (date == null)
        return null;

    if (!(date instanceof Date))
        date = new Date(date);

    if (!formatDate.monthNames)
        formatDate.monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

    return fmt.replace(/[YmdF]/g, function(match, offset, str) {
            var rv = match;

            switch (match) {
            case "Y":
                rv = date.getUTCFullYear();
                break;
            case "m":
                rv = date.getUTCMonth() + 1;
                if (rv < 10) rv = "0"+ rv;
                break;
            case "d":
                rv = date.getUTCDate();
                if (rv < 10) rv = "0"+ rv;
                break;
            case "F":
                rv = formatDate.monthNames[date.getUTCMonth()];
                break;
            }

            return rv;
        });
} // formatDate

