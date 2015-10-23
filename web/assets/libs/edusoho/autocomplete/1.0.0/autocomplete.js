/**
 * Created by liumengte on 15-6-15.
 */
define(function (require, exports, module) {

    var BaseAutoComplete = require('autocomplete');

    var AutoComplete = BaseAutoComplete.extend({

        _initFilter: function() {
            var filter = this.get("filter");
            if(filter.name in CustomFunc){
                filter = {
                    name: filter.name,
                    func: CustomFunc[filter.name],
                    options : filter.options
                }
                this.set("filter", filter);
            }else{
                AutoComplete.superclass._initFilter.call(this);
            }
        }
    });
    module.exports = AutoComplete;

    var CustomFunc = {
        "stringIgnoreCaseMatch": function (data, query,options) {
            options = this.attrs.filter.value.options;
            query = query || '';
            var result = [], l = query.length;
            if (!l) return []
            $.each(data, function (index, item) {
                var matchKeys = getMatchKey(item,options);
                // 匹配和显示相同才有必要高亮 忽略大小写
                if (matchKeys.toUpperCase().indexOf(query.toUpperCase()) > -1) {
                    item.highlightIndex = stringMatch(matchKeys.toUpperCase(), query.toUpperCase());
                    item.matchKey = matchKeys;
                    result.push(item);
                }
            });
            return result;
        }
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

    function getMatchKey(item,options){
        if ($.isPlainObject(item)) {
            // 默认取对象的 value 属性
            // 没有key,且对象无value属性那么将无法在页面上显示出匹配的项
            var key = options && options.key || "value";
            return item[key] || "";
        } else {
            return item;
        }
    }
});

