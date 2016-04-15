define(function(require, exports, module) {

    var Lazyload = require('echo.js');


    exports.run = function() {
        Lazyload.init();
        $('#live, #free').on('click', function(event) {
        	$('input:checkbox').attr('checked',false);
        	$(this).attr('checked',true);

        	window.location.href = $(this).val();
        });
    };

});