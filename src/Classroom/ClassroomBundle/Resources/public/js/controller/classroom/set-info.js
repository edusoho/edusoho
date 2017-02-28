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

            validator.removeItem('[name=expiryDay]');

            var expiryDay = $("[name='expiryDay']").val();
            if (expiryDay) {
                if (expiryDay.match("-")) {
                    $("[name='expiryDay']").data('date', $("[name='expiryDay']").val());
                } else {
                    $("[name='expiryDay']").data('days', $("[name='expiryDay']").val());
                }
                $("[name='expiryDay']").val('')
            }

            if ($(this).val() == 'none') {
                $('.expiry-day-js').addClass('hidden');
            } else {
                $('.expiry-day-js').removeClass('hidden');
                var $esBlock = $('.expiry-day-js > .controls > .help-block');
                $esBlock.text($esBlock.data($(this).val()));
                toggleExpiryValue($(this).val());
            }
        });
        function toggleExpiryValue(expiryMode) {
            if (!$("[name='expiryDay']").val()) {
                $("[name='expiryDay']").val($("[name='expiryDay']").data(expiryMode));
            }
            switch (expiryMode) {
                case 'days':
                    $('[name="expiryDay"]').datetimepicker('remove');
                    $(".expiry-day-js .controls > span").removeClass('hidden');
                    validator.addItem({
                        element: '[name=expiryDay]',
                        rule: 'positive_integer maxlength{max:10}',
                        required: true,
                        display: '有效期'
                    });
                    break;
                case 'date':
                    $(".expiry-day-js .controls > span").addClass('hidden');
                    validator.addItem({
                        element: '[name=expiryDay]',
                        required: true,
                        display: '有效期'
                    });
                    $("#classroom_expiryDay").datetimepicker({
                        language: 'zh-CN',
                        autoclose: true,
                        format: 'yyyy-mm-dd',
                        minView: 'month'
                    });
                    $("#classroom_expiryDay").datetimepicker('setStartDate', new Date);
                    break;
                default:
                    break;
            }
        }
        
        
    };

});