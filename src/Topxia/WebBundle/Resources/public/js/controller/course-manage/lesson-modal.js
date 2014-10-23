define(function(require, exports, module) {
    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var VideoChooser = require('../widget/media-chooser/video-chooser6');
    var AudioChooser = require('../widget/media-chooser/audio-chooser7');
    var PPTChooser = require('../widget/media-chooser/ppt-chooser6');
    var Notify = require('common/bootstrap-notify');
        require('jquery.sortable');

            var tmpContents = {};
            var editor;
            var Local_content = {};
            function getTmpContents(){
                var date = new Date(); //日期对象
                var now = "";
                // now = date.getFullYear()+"年"; //读英文就行了
                // now = now + (date.getMonth()+1)+"月"; //取月的时候取的是当前月-1如果想取当前月+1就可以了
                // now = now + date.getDate()+"日";
                now = now + date.getHours()+"时";
                now = now + date.getMinutes()+"分";
                now = now + date.getSeconds()+"秒";
                tmpContents["title"] = $("#lesson-title-field").val();
                tmpContents["summary"] = $("#lesson-summary-field").val();
                tmpContents["courseId"]  = $("#course-lesson-form").data("courseId");
                tmpContents["lessonId"]  = $("#course-lesson-form").data("lessonId");
                var lessonId = tmpContents["lessonId"];
                editor.sync();
                var z = editor.html();
                var x = editor.html().match(/<embed[\s\S]*?\/>/g);
                if (x) {
                        for (var i = x.length - 1; i >= 0; i--) {
                           var y = x[i].replace(/\/>/g,"wmode='Opaque' \/>");
                           var z =  z.replace(x[i],y);
                        };
                }
                tmpContents["content"] = z;
                tmpContents["createdTime"] = now;
                function compare(tmp, local){
                    if($.isEmptyObject(tmp)){
                        return false;
                    }
                    for(var key in tmp){
                        if(key!="courseId" && key!="createdTime" && tmp[key] != "" && tmp[key] != local[key]){
                            return true;
                        }
                    }
                    return false;
                }
                function objClone(jsonObj){
                     var txt=JSON.stringify(jsonObj);
                     return JSON.parse(txt);
                }
                if(compare(tmpContents, Local_content)){
                    if(lessonId == undefined){
                        $.post('/course/draft/create', tmpContents, function(data){
                            Local_content = objClone(tmpContents);
                            $(".modal-title").text('添加课时(草稿已于' + tmpContents['createdTime'] + '保存)');
                        });
                      } else {
                         $.post('/course/edit/draft/'+lessonId+'/create', tmpContents, function(data){
                            Local_content = objClone(tmpContents);
                            $(".modal-title").text('编辑课时(草稿已于' + tmpContents['createdTime'] + '保存)');
                        });
                     }
                }
            }
             $("#see-draft-btn").on('click',function(e) {
                tmpContents["courseId"]  = $("#course-lesson-form").data("courseId");
                var courseId = tmpContents["courseId"];
                $.get('/course/draft/'+courseId+'/see',function(response){  
                    $("#lesson-title-field").val(response.title); 
                    $("#lesson-summary-field").val(response.summary); 
                    editor.sync();
                    var z = editor.html(response.content);
                    $("#lesson-content-field").val(z);        
                }); 
                $("#see-draft-btn").hide();
            });

             $("#see-editdraft-btn").on('click',function(e) {
                tmpContents["courseId"]  = $("#course-lesson-form").data("courseId");
                var courseId = tmpContents["courseId"];
                tmpContents["lessonId"]  = $("#course-lesson-form").data("lessonId");
                var lessonId = tmpContents["lessonId"];
                $.get('/course/'+courseId+'/draft/'+lessonId+'/see',function(response){  
                    $("#lesson-title-field").val(response.title); 
                    $("#lesson-summary-field").val(response.summary); 
                    editor.sync();
                    var z = editor.html(response.content);
                    $("#lesson-content-field").val(z);        
                }); 
                $("#see-editdraft-btn").hide();
            });

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
         var Timer;
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
            if (type == 'text'){
                 Timer = setInterval(getTmpContents,5000);//1000为1秒钟

                 $(".close").on('click',function(e){
                        getTmpContents();
                 });

                 $("#cancel-btn").on('click',function(e){
                        getTmpContents();
                 });
            }

            if (type == 'video') {
                videoChooser.show();
                audioChooser.hide();
                pptChooser.hide();
                clearInterval(Timer);
            } else if (type == 'audio') {
                audioChooser.show();
                videoChooser.hide();
                pptChooser.hide();
                clearInterval(Timer);
            } else if (type == 'ppt') {
                pptChooser.show();
                videoChooser.hide();
                audioChooser.hide();
                clearInterval(Timer);
            }

            $(".modal").on("hide.bs.modal", function(){
            videoChooser.destroy();
            audioChooser.destroy();
            pptChooser.destroy();
        });
            $(".modal").on('hidden.bs.modal', function (){
            clearInterval(Timer);
       });
            switchValidator(validator, type);
        });



        $form.find('[name="type"]:checked').trigger('change');

        editor = EditorFactory.create('#lesson-content-field', 'standard', {extraFileUploadParams:{group:'course'}, height: '300px'});
        
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
