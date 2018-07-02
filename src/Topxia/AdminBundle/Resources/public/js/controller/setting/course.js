define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.sortable');
    var Notify = require('common/bootstrap-notify');
    var WebUploader = require('edusoho.webuploader');

    exports.run = function() {

        $(".buy-userinfo-list").sortable({
            'distance': 20
        });
      
        if ($("[name=buy_fill_userinfo]:checked").val() == 1) $("#buy-userinfo-list").hide();
        if ($("[name=buy_fill_userinfo]:checked").val() == 0) {
            $("#buy-userinfo-list").hide();
            $("#show-list").hide();
        }

        $("[name=buy_fill_userinfo]").on("click", function() {
            if ($("[name=buy_fill_userinfo]:checked").val() == 1) {
                $("#show_course_chapter_name").show()
            }else{
               $("#show_course_chapter_name").hide();
            }
        });

        $("#hide-list-btn").on("click", function() {
            $("#buy-userinfo-list").hide();
            $("#show-list").show();
        });

        $("#show-list-btn").on("click", function() {
            $("#buy-userinfo-list").show();
            $("#show-list").hide();
        });

        var initChapterStatus = function(use_chapter_name){
            if(use_chapter_name ==1){
                $("#show_course_chapter_name").removeClass('hide');
            }else{
                $("#show_course_chapter_name").addClass('hide');
            }
        };

        $( "input[name='custom_chapter_enabled']").on('click',function(){
            initChapterStatus($( "input[name='custom_chapter_enabled']:checked").val());
        });

        var $form = $("#course-form");

        var validator = new Validator({
          element: $form,
          autoSubmit: true
        });

        validator.addItem({
          element: '[name="task_name"]',
          required: true,
          rule: 'minlength{min:5} maxlength{max:10}',
          display: '任务名称'
        });

        if($('#live-course-logo-upload').length>0) {
            var uploader = new WebUploader({
                element: '#live-course-logo-upload'
            });

            uploader.on('uploadSuccess', function(file, response ) {
                var url = $("#live-course-logo-upload").data("gotoUrl");

                $("#live-course-logo-container").html('<img src="' + response.url + '">');
                $form.find('[name=live_logo]').val(response.url);
                $("#live-course-logo-remove").show();
                Notify.success(Translator.trans('上传直播课程的LOGO成功！'));
                
            });

            $("#live-course-logo-remove").on('click', function(){
                if (!confirm(Translator.trans('确认要删除吗？'))) return false;
                var $btn = $(this);
                $.post($btn.data('url'), function(){
                    $("#live-course-logo-container").html('');
                    $form.find('[name=live_logo]').val('');
                    $btn.hide();
                    Notify.success(Translator.trans('删除直播课程LOGO成功！'));
                }).error(function(){
                    Notify.danger(Translator.trans('删除直播课程LOGO失败！'));
                });
            });
        }
    };

});