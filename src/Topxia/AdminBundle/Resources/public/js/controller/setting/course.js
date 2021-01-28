define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.sortable');
    var Notify = require('common/bootstrap-notify');
    var WebUploader = require('edusoho.webuploader');

    exports.run = function() {
      if($('#show-student-num-open').data('showable') == 1) {
        $('#show-student-num-open').attr('checked','checked');
        if($('#show-student-num').data('showmode') == 'studentNum') {
          $('#show-student-num').attr('checked','checked');
        } else {
          $('#show-hit-num').attr('checked','checked');
        }
      } else {
        $('#show-student-num-close').attr('checked','checked');
        if ($('#show-student-num').data('showmode') == 'studentNum') {
          $('#show-student-num').attr('checked','checked');
        } else {
          $('#show-hit-num').attr('checked','checked');
        }
        $('#show_cover_num_mode').attr('hidden','hidden');
      }

      $('#show-student-num-close').click(function(){
        $('#show_cover_num_mode').attr('hidden','hidden');
      });
      $('#show-student-num-open').click(function(){
        $('#show_cover_num_mode').removeAttr('hidden');
      });


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
          autoSubmit: false,
          onFormValidated: function(error, results, $form) {
            if (error) {
              return ;
            }

            if ($('[name="task_name"]').data('beforeValue') !== $('[name="task_name"]').val()) {
              if (!confirm(Translator.trans("系统后台和用户前台显示处的\"任务\"将会被新的名称覆盖，请确认进行修改。"))) {
                return ;
              }
            }
            if ($('[name="chapter_name"]').data('beforeValue') !== $('[name="chapter_name"]').val()
              || $('[name="part_name"]').data('beforeValue') !== $('[name="part_name"]').val()) {
              if (!confirm(Translator.trans("系统后台和用户前台显示处的\"章节\"将会被新的名称覆盖，请确认进行修改。"))) {
                return ;
              }
            }

            $.post($form.attr('action'), $form.serialize(), function(response){
              if (response) {
                window.location.reload();
              } else {
                Notify.success(Translator.trans('更新失败!'));
              }
            }, 'json');
          }
        });

        // validator.addItem({
        //   element: '[name="task_name"]',
        //   required: true,
        //   rule: 'maxlength{max:10}',
        //   display: '名称'
        // });
        validator.addItem({
          element: '[name="chapter_name"]',
          required: true,
          rule: 'maxlength{max:10}',
          display: '名称'
        });
        validator.addItem({
          element: '[name="part_name"]',
          required: true,
          rule: 'maxlength{max:10}',
          display: '名称'
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
                Notify.success(Translator.trans('admin.setting.course.upload_live_course_logo_success_hint'));

            });

            $("#live-course-logo-remove").on('click', function(){
                if (!confirm(Translator.trans('admin.setting.course.live_course_logo_delete_hint'))) return false;
                var $btn = $(this);
                $.post($btn.data('url'), function(){
                    $("#live-course-logo-container").html('');
                    $form.find('[name=live_logo]').val('');
                    $btn.hide();
                    Notify.success(Translator.trans('admin.setting.course.live_course_logo_delete_success_hint'));
                }).error(function(){
                    Notify.danger(Translator.trans('admin.setting.course.live_course_logo_delete_fail_hint'));
                });
            });
        }
    };

});