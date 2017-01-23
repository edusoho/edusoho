define(function(require, exports, module) {

    var Lazyload = require('echo.js');


    exports.run = function() {
        Lazyload.init();
        $('#free').on('click', function(event) {
        	window.location.href = $(this).val();
        });
    };

});