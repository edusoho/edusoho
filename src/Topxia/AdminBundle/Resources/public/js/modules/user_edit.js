define(function(require, exports, module) {
    var $ = require('jquery');

    exports.bootstrap = function(options) {
        $(function(options) {
            $('#user-opts a').click(function(){
                return confirm('真的要这么做吗？');
            });
        });
    };

});
