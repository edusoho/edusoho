define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');

    exports.run = function() {
        $('.bind').click(function(){
             window.location.reload();
        });

         $('.unbind').click(function(){
             window.location.reload();
        });


    };

});