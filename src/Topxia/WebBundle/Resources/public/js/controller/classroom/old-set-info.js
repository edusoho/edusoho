define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');

    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require('es-ckeditor');

    require('/bundles/topxiaweb/js/controller/widget/category-select').run();
    require('jquery.bootstrap-datetimepicker');

    exports.run = function() {

/*        var editor_classroom = CKEDITOR.replace('description', {
            toolbar: 'Detail',
            filebrowserImageUploadUrl: $('#description').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#description').data('flashUploadUrl')
        });*/

        var editor_classroom_about = CKEDITOR.replace('about', {
            allowedContent: true,
            toolbar: 'Detail',
            filebrowserImageUploadUrl: $('#about').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#about').data('flashUploadUrl')
        });

        var validator = new Validator({
            element: '#classroom-set-form',
            triggerType: 'change',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#classroom-save').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="title"]',
            required: true
        });

        validator.on('formValidate', function(elemetn, event) {
            editor_classroom.updateElement();
        });

        toggleExpiryValue($("[name=expiryMode]:checked").val());

        $("[name='expiryMode']").change(function () {
            if (app.arguments.classroomStatus == 'published') {
                return false;
            }

            validator.removeItem('[name=expiryValue]');

            var expiryValue = $("[name='expiryValue']").val();
            if (expiryValue) {
                if (expiryValue.match("-")) {
                    $("[name='expiryValue']").data('date', $("[name='expiryValue']").val());
                } else {
                    $("[name='expiryValue']").data('days', $("[name='expiryValue']").val());
                }
                $("[name='expiryValue']").val('')
            }

            if ($(this).val() == 'none') {
                $('.expiry-value-js').addClass('hidden');
            } else {
                $('.expiry-value-js').removeClass('hidden');
                var $esBlock = $('.expiry-value-js > .controls > .help-block');
                $esBlock.text($esBlock.data($(this).val()));
                toggleExpiryValue($(this).val());
            }
        });
        function toggleExpiryValue(expiryMode) {
            if (!$("[name='expiryValue']").val()) {
                $("[name='expiryValue']").val($("[name='expiryValue']").data(expiryMode));
            }
            switch (expiryMode) {
                case 'days':
                    $('[name="expiryValue"]').datetimepicker('remove');
                    $(".expiry-value-js .controls > span").removeClass('hidden');
                    validator.addItem({
                        element: '[name=expiryValue]',
                        rule: 'positive_integer maxlength{max:10}',
                        required: true,
                        display: '有效期'
                    });
                    break;
                case 'date':
                    $(".expiry-value-js .controls > span").addClass('hidden');
                    validator.addItem({
                        element: '[name=expiryValue]',
                        required: true,
                        display: '有效期'
                    });
                    $("#classroom_expiryValue").datetimepicker({
                        language: 'zh-CN',
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        minView: 'month'
                    });
                    $("#classroom_expiryValue").datetimepicker('setStartDate', new Date);
                    break;
                default:
                    break;
            }
        }
        
        
    };

});