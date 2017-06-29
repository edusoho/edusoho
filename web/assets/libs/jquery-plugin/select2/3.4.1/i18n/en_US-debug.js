define("jquery-plugin/select2/3.4.1/i18n/en_US-debug", [ "$-debug" ], function(require) {
    var jQuery = require("$-debug");
    /**
     * Select2 English translation
     */
    (function($) {
        "use strict";
        $.extend($.fn.select2.defaults, {
            formatNoMatches: function() {
                return "No matches found";
            },
            formatInputTooShort: function(input, min) {
                var n = min - input.length;
                return "Please enter " + n + " character";
            },
            formatInputTooLong: function(input, max) {
                var n = input.length - max;
                return "Please delete " + n + " character";
            },
            formatSelectionTooBig: function(limit) {
                return "You can only select " + limit + " item";
            },
            formatLoadMore: function(pageNumber) {
                return "Loading more results...";
            },
            formatSearching: function() {
                return "Searching...";
            }
        });
    })(jQuery);
});
