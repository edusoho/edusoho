define(function(require, exports, module) {

    var Lazyload = require('echo.js');
    require('topxiawebbundle/util/follow-btn');
    require('jquery.lavaTab');

    exports.run = function() {

        Lazyload.init();
        
        $('.nav-tabs').lavaTab({
        	fx: "backout",
        	speed: 700
        });

    };

});