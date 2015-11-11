/*!
 * 输入过滤器
 */

define( function ( require ) {

    // 过滤列表， 其中的key对应于键盘事件的keycode， 带有s+字样的key，匹配的是shift+keycode
    var LIST = {
        32: "\\,",
        "s+219": "\\{",
        "s+221": "\\}",
        "220": "\\backslash",
        "s+51": "\\#",
        "s+52": "\\$",
        "s+53": "\\%",
        "s+54": "\\^",
        "s+55": "\\&",
        "s+189": "\\_",
        "s+192": "\\~"
    };

    return {

        getReplaceString: function ( key ) {
            return LIST[ key ] || null;
        }

    };

} );