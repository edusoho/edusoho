define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var validator = new Validator({
            element: '#login_bind-form',
        });

        validator.addItem({
            element: '[name=temporary_lock_allowed_times]',
            rule: 'integer'
        });

        validator.addItem({
            element: '[name=temporary_lock_minutes]',
            rule: 'integer'
        });

        validator.addItem({
            element: '[name=verify_code]',
            rule: 'htmlTag'
        });

        Validator.addRule("htmlTag", function(options) {
            var value = $(options.element).val();
            var illegalMatch = value.match(/>\s*\w{1,}|^\w{1,}\s*</gm);
            var legalMatch = value.match(/^<(meta|link|script)(.*)?(\/*)>$/gm);
            return (illegalMatch == null ) && (legalMatch && legalMatch.length >0 )
        }, Translator.trans('validate_old.html_tag.message', {display:'{{display}}'}));

        var hideOrShowTimeAndMinutes = function() {
            if ($('[name=temporary_lock_enabled]').filter(':checked').attr("value") == 1) {
                $('#times_and_minutes').show();
            } else if ($('[name=temporary_lock_enabled]').filter(':checked').attr("value") == 0) {
                $('#times_and_minutes').hide();
            };
        };
        hideOrShowTimeAndMinutes();
        $('[name=temporary_lock_enabled]').change(function() {
            hideOrShowTimeAndMinutes();
        });


        $('[name=enabled]').change(function() {
            if ($('[name=enabled]').filter(':checked').attr("value") == 1) {
                $('#third_login').show();
            } else if ($('[name=enabled]').filter(':checked').attr("value") == 0) {
                $('#third_login').hide();
            };
        });


        $('[data-role=oauth2-setting]').each(function() {
            var type = $(this).data('type');
            $('[name=' + type + '_enabled]').change(function() {
                if ($(this).val() == '1') {
                    validator.addItem({
                        element: '[name=' + type + '_key]',
                        required: true
                    });
                    validator.addItem({
                        element: '[name=' + type + '_secret]',
                        required: true
                    });
                } else {
                    validator.removeItem('[name=' + type + '_key]');
                    validator.removeItem('[name=' + type + '_secret]');
                }
            })

            $('[name=' + type + '_enabled]:checked').change();
        });

        $('#help').popover({
            html: true,
            container: "body",
            template: '<div class="popover help-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
        });

    };

});