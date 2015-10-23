define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    require('jquery.select2-css');
    require('jquery.select2');

    exports.run = function() {

        $('#setting-email-form').on('submit', function(){
            $('#set-email-btn').attr('disabled', 'true');
        });

    };

});