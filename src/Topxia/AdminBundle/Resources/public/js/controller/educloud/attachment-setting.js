define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $("input[name='enable']").on('click', function() {
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
            rule: 'positive_integer fileSize',
        });

        Validator.addRule("fileSize", function(options) {
            var element = $(options.element);
            return element.val() <= 2 * 1024; 
        }, Translator.trans('validate_old.server_upload_attachment_limit.message'));
    }

});