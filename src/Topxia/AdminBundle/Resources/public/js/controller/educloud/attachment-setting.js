define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $("input[name='attachment_enable']").on('click', function() {
            if ($(this).val() == 1) {
                $('.attachement_detail').removeClass('hidden');
            } else {
                $('.attachement_detail').addClass('hidden');
            }
        });


        var validator = new Validator({
            element: '#cloud-attachment'
        });
        validator.addItem({
            element: '[name="fileSize"]',
            required: true,
            rule: 'integer',
        });
    }

});