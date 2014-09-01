define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var VideoChooser = require('../widget/media-chooser/video-chooser3');
    var AudioChooser = require('../widget/media-chooser/audio-chooser3');
    var PPTChooser = require('../widget/media-chooser/ppt-chooser3');
    var Notify = require('common/bootstrap-notify');
        require('jquery.sortable');
        
    var sortList = function($list) {
            var data = $list.sortable("serialize").get();
            $.post($list.data('sortUrl'), {ids:data}, function(response){
                var lessonNum = chapterNum = unitNum = 0;

                $list.find('.item-lesson, .item-chapter').each(function() {
                    var $item = $(this);
                    if ($item.hasClass('item-lesson')) {
                        lessonNum ++;
                        $item.find('.number').text(lessonNum);
                    } else if ($item.hasClass('item-chapter-unit')) {
                        unitNum ++;
                        $item.find('.number').text(unitNum);
                    } else if ($item.hasClass('item-chapter')) {
                        chapterNum ++;
                        unitNum = 0;
                        $item.find('.number').text(chapterNum);
                    }

                });
            });
        };

    function createValidator ($form, $choosers) {

        Validator.addRule('mediaValueEmpty', function(options) {
            var value = options.element.val();
            if (value == '""') {
                return false;
            }

            var value = $.parseJSON(value);
            if (!value || !value.source) {
                return false;
            }

            return true;
        }, '请选择或上传{{display}}文件');

        Validator.addRule('timeLength', function(options) {
            return /^\d+:\d+$/.test(options.element.val())
        }, '时长格式不正确');
        validator = new Validator({
            element: $form,
            failSilently: true,
            autoSubmit: false
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }
            for(var i=0; i<$choosers.length; i++){
                if($choosers[i].isUploading()){
                    Notify.danger('文件正在上传，等待上传完后再保存。');
                    return;
                }
            }

            $('#course-lesson-btn').button('submiting').addClass('disabled');

            var $panel = $('.lesson-manage-panel');
            $.post($form.attr('action'), $form.serialize(), function(html) {
                var id = '#' + $(html).attr('id'),
                    $item = $(id);
                var $parent = $('#'+$form.data('parentid'));
                if ($item.length) {
                    $item.replaceWith(html);
                    Notify.success('课时已保存');
                } else {
                    $panel.find('.empty').remove();

                    if($parent.length){
                        var add = 0;
                        if($parent.hasClass('item-chapter  clearfix')){
                            $parent.nextAll().each(function(){
                                if($(this).hasClass('item-chapter  clearfix')){
                                    $(this).before(html);
                                    add = 1;
                                    return false;
                                }
                            });
                            if(add !=1 ){
                                $("#course-item-list").append(html);
                                add = 1;
                            }

                        }else{
                             $parent.nextAll().each(function() {
                                if($(this).hasClass('item-chapter  clearfix')){
                                    $(this).before(html);
                                    add = 1;
                                    return false;
                                }
                                if($(this).hasClass('item-chapter item-chapter-unit clearfix')){
                                    $(this).before(html);
                                    add = 1;
                                    return false;
                                }
                            });
                        }
                        if(add != 1 ) {
                         $("#course-item-list").append(html); 
                        }
                        var $list = $("#course-item-list");
                        sortList($list);
                    }else{
                        $("#course-item-list").append(html);
                    }
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
        validator.removeItem('#lesson-second-field');
        validator.removeItem('#lesson-minute-field');

        validator.addItem({
            element: '#lesson-title-field',
            required: true
        });

        validator.addItem({
            element: '#lesson-give-credit-field',
            required: true,
            rule: 'integer'
        });

        switch (type) {
            case 'video':
            case 'audio':
                validator.addItem({
                    element: '#lesson-media-field',
                    required: true,
                    rule: 'mediaValueEmpty',
                    display: type == 'video' ? '视频' : '音频'
                });

                validator.addItem({
                    element: '#lesson-minute-field',
                    required: true,
                    rule: 'integer',
                    display: '时长'
                });

                validator.addItem({
                    element: '#lesson-second-field',
                    required: true,
                    rule: 'second_range',
                    display: '时长'
                });

                break;
            case 'text':
                validator.addItem({
                    element: '#lesson-content-field',
                    required: true
                });
                break;
            case 'ppt':
                validator.addItem({
                    element: '#lesson-media-field',
                    required: true,
                    rule: 'mediaValueEmpty',
                    display: 'PPT'
                });
                break;
        }

    }

    exports.run = function() {
        var updateDuration = function (length) {
            length = parseInt(length);
            if (isNaN(length) || length == 0) {
                return ;
            }
            var minute = parseInt(length / 60);
            var second = length - minute * 60;

            $("#lesson-minute-field").val(minute);
            $("#lesson-second-field").val(second);
        }

        var $form = $("#course-lesson-form");

        var $content = $("#lesson-content-field");

        var choosedMedia = $form.find('[name="media"]').val();
        choosedMedia = choosedMedia ? $.parseJSON(choosedMedia) : {};
        
        var videoChooser = new VideoChooser({
            element: '#video-chooser',
            choosed: choosedMedia,
        });

        var audioChooser = new AudioChooser({
            element: '#audio-chooser',
            choosed: choosedMedia,
        });

        var pptChooser = new PPTChooser({
            element: '#ppt-chooser',
            choosed: choosedMedia,
        });

        videoChooser.on('change', function(item) {
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
            updateDuration(item.length);
        });

        audioChooser.on('change', function(item) {
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
            updateDuration(item.length);
        });

        pptChooser.on('change', function(item) {
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
        });

        $('.modal').unbind("hide.bs.modal");
        $(".modal").on("hide.bs.modal", function(){
            videoChooser.destroy();
            audioChooser.destroy();
            pptChooser.destroy();
        });

        var validator = createValidator($form, [videoChooser,pptChooser,audioChooser]);

        $form.on('change', '[name=type]', function(e) {
            var type = $(this).val();

            $form.removeClass('lesson-form-video').removeClass("lesson-form-audio").removeClass("lesson-form-text").removeClass("lesson-form-ppt")
            $form.addClass("lesson-form-" + type);

            if (type == 'video') {
                videoChooser.show();
                audioChooser.hide();
                pptChooser.hide();

            } else if (type == 'audio') {
                audioChooser.show();
                videoChooser.hide();
                pptChooser.hide();
            } else if (type == 'ppt') {
                pptChooser.show();
                videoChooser.hide();
                audioChooser.hide();
            }

            switchValidator(validator, type);
        });

        $form.find('[name="type"]:checked').trigger('change');

        var editor = EditorFactory.create('#lesson-content-field', 'standard', {extraFileUploadParams:{group:'course'}, height: '300px'});
        
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
            var z = editor.html();
            var x = editor.html().match(/<embed[\s\S]*?\/>/g);
            if (x) {
                for (var i = x.length - 1; i >= 0; i--) {
                   var y = x[i].replace(/\/>/g,"wmode='Opaque' \/>");
                   var z =  z.replace(x[i],y);
                };
            }
            $content.val(z);
        });
        
    };
});
