/**
 * Created by liumengte on 15-6-15.
 */
define(function (require, exports, module) {
    var AutoComplete = require('autocomplete');

    var AutoCompleteCustom = AutoComplete.extend({
        _initFilter: function () {
            filter = {
                func: stringIgnoreMatch
            };
            this.set("filter", filter);
        }
    });
    module.exports = AutoCompleteCustom;
    function stringIgnoreMatch(data, query) {
        query = query.toUpperCase() || '';
        var result = [], l = query.length;
        if (!l) return []
        $.each(data, function (index, item) {
            var matchKeys = item['nickname'].toUpperCase();
            // 匹配 value 和 alias 中的
            if (matchKeys.indexOf(query) > -1) {
                // 匹配和显示相同才有必要高亮
                item.highlightIndex = stringMatch(matchKeys, query);
                item.matchKey = item.nickname;
                result.push(item);
            }
        });
        return result;
    }

    function stringMatch(matchKey, query) {
        var r = [],
            a = matchKey.split('');
        var queryIndex = 0,
            q = query.split('');
        for (var i = 0, l = a.length; i < l; i++) {
            var v = a[i];
            if (v === q[queryIndex]) {
                if (queryIndex === q.length - 1) {
                    r.push([i - q.length + 1, i + 1]);
                    queryIndex = 0;
                    continue;
                }
                queryIndex++;
            } else {
                queryIndex = 0;
            }
        }
        return r;
    }

});

