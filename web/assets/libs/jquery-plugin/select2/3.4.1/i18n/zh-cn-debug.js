define("jquery-plugin/select2/3.4.1/i18n/zh-cn-debug", [ "$-debug" ], function(require) {
    var jQuery = require("$-debug");
    /**
 * Select2 Chinese translation
 */
    (function($) {
        "use strict";
        $.extend($.fn.select2.defaults, {
            formatNoMatches: function() {
                return "没有找到匹配项";
            },
            formatInputTooShort: function(input, min) {
                var n = min - input.length;
                return "请再输入" + n + "个字符";
            },
            formatInputTooLong: function(input, max) {
                var n = input.length - max;
                return "请删掉" + n + "个字符";
            },
            formatSelectionTooBig: function(limit) {
                return "你只能选择最多" + limit + "项";
            },
            formatLoadMore: function(pageNumber) {
                return "加载结果中...";
            },
            formatSearching: function() {
                return "搜索中...";
            }
        });
    })(jQuery);
});
