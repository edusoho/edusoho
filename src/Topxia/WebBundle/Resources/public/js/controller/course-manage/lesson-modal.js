define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var MediaChoose = require('./media-choose');
    var Notify = require('common/bootstrap-notify');

    require('ckeditor');

    function createValidator ($form) {


        Validator.addRule('timeLength', function(options) {
            console.log(options.element.val());
            return /^\d+:\d+$/.test(options.element.val())
        }, '时长格式不正确');

        validator = new Validator({
            element: $form,
            autoSubmit: false
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }

            $.post($form.attr('action'), $form.serialize(), function(html) {
                var id = '#' + $(html).attr('id'),
                    $item = $(id);
                if ($item.length) {
                    $item.replaceWith(html);
                    Notify.success('课时已保存');
                } else {
                    $("#course-item-list").append(html);
                    Notify.success('添加课时成功');
                }
                $(id).find('.btn-link').tooltip();
                $form.parents('.modal').modal('hide');
            });

        });

        return validator;
    };

    function switchValidator(validator, type) {
        validator.removeItem('#lesson-title-field');
        validator.removeItem('#lesson-content-field');
        validator.removeItem('#lesson-media-field');
        validator.removeItem('#lesson-length-field');

        validator.addItem({
            element: '#lesson-title-field',
            required: true
        });

        switch (type) {
            case 'video':
            case 'audio':
                validator.addItem({
                    element: '#lesson-media-field',
                    required: true,
                    errormessageRequired: '请选择或上传' + (type == 'video' ? '视频' : '音频')
                });

                validator.addItem({
                    element: '#lesson-length-field',
                    required: true,
                    rule: 'timeLength'
                });

                break;
            case 'text':
                validator.addItem({
                    element: '#lesson-content-field',
                    required: true
                });
                break;
        }

    }

    exports.run = function() {
        var $form = $("#course-lesson-form");

        var choosedMedia = $form.find('[name="media"]').val();
        choosedMedia = choosedMedia ? $.parseJSON(choosedMedia) : {};

        var lessonMediaChoose = new MediaChoose({
            element: '#lesson-media-choose',
            choosed: choosedMedia
        });

        lessonMediaChoose.on('change', function(item) {
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
        });

        var validator = createValidator($form);

        $form.on('change', '[name="type"]', function() {
            var type = $(this).val();

            $form.removeClass('lesson-form-video').removeClass("lesson-form-audio").removeClass("lesson-form-text")
            $form.addClass("lesson-form-" + type);

            if (type == 'video' || type == 'audio') {
                lessonMediaChoose.set('type', type);
            }

            switchValidator(validator, type);
        });

        $form.find('[name="type"]:checked').trigger('change');

        CKEDITOR.replace('lesson-content-field', {
            height: 300,
            resize_enabled: false,
            forcePasteAsPlainText: true,
            toolbar: 'Simple',
            removePlugins: 'elementspath',
            filebrowserUploadUrl: '/ckeditor/upload?group=course'
        });

        validator.on('formValidate', function(elemetn, event) {
            CKEDITOR.instances['lesson-content-field'].updateElement();
        });

    };
});