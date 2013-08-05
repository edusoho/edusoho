define(function(require, exports, module) {
    var $ = require('jquery');

    exports.bootstrap = function(options) {

        $('.pick-btn, .unpick-btn').click(function(){
            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

    };

});
