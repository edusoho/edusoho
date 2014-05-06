define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('#lesson-mediaId-field').change(function() {
            var mediaId = $('#lesson-mediaId-field').find('option:selected').val();
            if (mediaId != '') {
                $('#lesson-title-field').val($('#lesson-mediaId-field').find('option:selected').text());
            } else {
                $('#lesson-title-field').val('');
            }
        });

        validator = new Validator({
            element: '#course-lesson-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '#lesson-mediaId-field',
            required: true,
            errormessageRequired: '请选择试卷'
        });

        validator.addItem({
            element: '#lesson-title-field',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }
            $('#course-testpaper-btn').button('submiting').addClass('disabled');

            var $panel = $('.lesson-manage-panel');
            $.post($form.attr('action'), $form.serialize(), function(html) {

                var id = '#' + $(html).attr('id'),
                    $item = $(id);
                if ($item.length) {
                    $item.replaceWith(html);
                    Notify.success('试卷课时已保存');
                } else {
                    $panel.find('.empty').remove();
                    $("#course-item-list").append(html);
                    Notify.success('添加试卷课时成功');
                }
                $(id).find('.btn-link').tooltip();
                $form.parents('.modal').modal('hide');
            });

        });


    };
});