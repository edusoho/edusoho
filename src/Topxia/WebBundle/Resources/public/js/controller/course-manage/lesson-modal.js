define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var VideoChooser = require('../widget/media-chooser/video-chooser7');
    var AudioChooser = require('../widget/media-chooser/audio-chooser8');
    var PPTChooser = require('../widget/media-chooser/ppt-chooser7');
    var DocumentChooser = require('../widget/media-chooser/document-chooser7');
    var FlashChooser = require('../widget/media-chooser/flash-chooser');
    var Notify = require('common/bootstrap-notify');
    require('jquery.sortable');
    require('ckeditor');

    function getEditorContent(editor){
        editor.updateElement();
        var z = editor.getData();
        var x = editor.getData().match(/<embed[\s\S]*?\/>/g);
        if (x) {
            for (var i = x.length - 1; i >= 0; i--) {
               var y = x[i].replace(/\/>/g,"wmode='Opaque' \/>");
               var z =  z.replace(x[i],y);
            };
        }
        return z;
    }

    function compare(tmp, local){
        if($.isEmptyObject(tmp)){
            return false;
        }
        for(var key in tmp){
            if(key!="courseId" 
                && key!="lessonId" 
                && key!="createdTime" 
                && tmp[key] != "" 
                && tmp[key] != local[key]){
                return true;
            }
        }
        return false;
    }
    function objClone(jsonObj){
         var txt=JSON.stringify(jsonObj);
         return JSON.parse(txt);
    }

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
            case 'document':
                validator.addItem({
                    element: '#lesson-media-field',
                    required: true,
                    rule: 'mediaValueEmpty',
                    display: '文档'
                });
                break;
        }

    }

    exports.run = function() {
        var Timer;
        var editor;
        var tmpContents = {};
        var localContent = {};
        var $form = $("#course-lesson-form");

        function getTmpContents(){
            var date = new Date(); //日期对象
            var now = "";
            now = now + date.getHours()+"时";
            now = now + date.getMinutes()+"分";
            now = now + date.getSeconds()+"秒";
            tmpContents["title"] = $("#lesson-title-field").val();
            tmpContents["summary"] = $("#lesson-summary-field").val();
            tmpContents["courseId"]  = $("#course-lesson-form").data("courseId");
            tmpContents["lessonId"]  = $("#course-lesson-form").data("lessonId");
            tmpContents["content"] = getEditorContent(editor);
            tmpContents["createdTime"] = now;


            var lessonId = 0;
            if(compare(tmpContents, localContent)){
                var titleName = "添加课时";
                if(tmpContents["lessonId"] != undefined){
                    titleName = "编辑课时";
                    lessonId = tmpContents["lessonId"];
                }
                $.post($form.data("createDraftUrl"), tmpContents, function(data){
                    localContent = objClone(tmpContents);
                    $(".modal-title").text(titleName + '(草稿已于' + tmpContents['createdTime'] + '保存)');
                });
            }
        }

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

        var documentChooser = new DocumentChooser({
            element: '#document-chooser',
            choosed: choosedMedia,
        });
        var flashChooser = new FlashChooser({
            element: '#flash-chooser',
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

        documentChooser.on('change', function(item) {
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
        });

        flashChooser.on('change', function(item) {
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
        });

        $('.modal').unbind("hide.bs.modal");
        $(".modal").on("hide.bs.modal", function(){
            videoChooser.destroy();
            audioChooser.destroy();
            pptChooser.destroy();
            documentChooser.destroy();
            flashChooser.destroy();
        });

        var validator = createValidator($form, [videoChooser,pptChooser,audioChooser,documentChooser,flashChooser]);
       
        $form.on('change', '[name=type]', function(e) {
            var type = $(this).val();

            $form.removeClass('lesson-form-video').removeClass("lesson-form-audio").removeClass("lesson-form-text").removeClass("lesson-form-ppt").removeClass("lesson-form-document").removeClass("lesson-form-flash")
            $form.addClass("lesson-form-" + type);
            
            if (type == 'text'){
                Timer = setInterval(getTmpContents, 5000);
            }

            if (type == 'video') {
                videoChooser.show();
                audioChooser.hide();
                pptChooser.hide();
                documentChooser.hide();
                flashChooser.hide();
                clearInterval(Timer);
            } else if (type == 'audio') {
                audioChooser.show();
                videoChooser.hide();
                pptChooser.hide();
                documentChooser.hide();
                flashChooser.hide();
                clearInterval(Timer);
            } else if (type == 'ppt') {
                pptChooser.show();
                videoChooser.hide();
                audioChooser.hide();
                documentChooser.hide();
                flashChooser.hide();
                clearInterval(Timer);
            } else if (type == 'document') {
                documentChooser.show();
                pptChooser.hide();
                videoChooser.hide();
                audioChooser.hide();
                flashChooser.hide();
                clearInterval(Timer);
            } else if (type == 'flash') {
                flashChooser.show();
                documentChooser.hide();
                pptChooser.hide();
                videoChooser.hide();
                audioChooser.hide();
                clearInterval(Timer);
            }

            $(".modal").on('hidden.bs.modal', function (){
                clearInterval(Timer);
            });

            switchValidator(validator, type);
        });



        $form.find('[name="type"]:checked').trigger('change');

        // course
        editor = CKEDITOR.replace('lesson-content-field', {
            toolbar: 'Full',
            filebrowserImageUploadUrl: $('#lesson-content-field').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#lesson-content-field').data('flashUploadUrl'),
            height: 300
        });


        
        validator.on('formValidate', function(elemetn, event) {
            var content = getEditorContent(editor);
            $content.val(content);
        });

        $(".close,#cancel-btn").on('click',function(e){
            if($form.find('[name="type"]:checked').val()=='text'){
                getTmpContents();
            }
        });

        $("#see-draft-btn").on('click',function(e) {
            tmpContents["courseId"]  = $("#course-lesson-form").data("courseId");
            var courseId = tmpContents["courseId"];
            tmpContents["lessonId"]  = $("#course-lesson-form").data("lessonId");
            var lessonId;
            if(tmpContents["lessonId"] == undefined)  {
                lessonId =0;
            } else{
                lessonId = tmpContents["lessonId"];
            }
            $.get($(this).data("url"), {courseId: courseId, lessonId:lessonId}, function(response){  
                $("#lesson-title-field").val(response.title); 
                $("#lesson-summary-field").val(response.summary); 
                editor.updateElement();
                editor.setData(response.content);
                $("#lesson-content-field").val(response.content);        
            });
            $("#see-draft-btn").hide();
        });
        
    };
});
