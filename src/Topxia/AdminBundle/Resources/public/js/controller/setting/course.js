define(function(require, exports, module) {

    require('jquery.sortable');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

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
                $("#show-list").show();
                $("#buy-userinfo-list").hide();
            }
            if ($("[name=buy_fill_userinfo]:checked").val() == 0) {
                $("#buy-userinfo-list").hide();
                $("#show-list").hide();
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

        var $form = $("#course-form");
        var uploader = new Uploader({
            trigger: '#live-course-logo-upload',
            name: 'logo',
            action: $('#live-course-logo-upload').data('url'),
            data: {
                '_csrf_token': $('meta[name=csrf-token]').attr('content')
            },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传直播课程LOGO失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#live-course-logo-container").html('<img src="' + response.url + '">');
                $form.find('[name=live_logo]').val(response.path);
                $("#live-course-logo-remove").show();
                Notify.success('上传直播课程的LOGO成功！');
            }
        });

        $("#live-course-logo-remove").on('click', function() {
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $("#live-course-logo-container").html('');
                $form.find('[name=live_logo]').val('');
                $btn.hide();
                Notify.success('删除直播课程LOGO成功！');
            }).error(function() {
                Notify.danger('删除直播课程LOGO失败！');
            });
        });

        // var $avatarForm = $("#avatar-form");

        // avatarValidator = new Validator({
        //     element: $avatarForm
        // })

        // avatarValidator.addItem({
        //     element: '#avatar-field',
        //     required: true,
        //     rule: 'maxsize_image',
        //     errormessageRequired: '请选择要上传的默认头像文件'
        // });

        // if ($('#system-course-picture-class').length > 0) {

        //     var $coursePictureForm = $("#course-picture-form");
        //     coursePictureValidator = new Validator({
        //         element: $coursePictureForm
        //     });

        //     coursePictureValidator.addItem({
        //         element: '#course-picture-field',
        //         required: true,
        //         rule: 'maxsize_image',
        //         errormessageRequired: '请选择要上传的默认课程图片文件'
        //     });

        //     var $systemCoursePictureClass = $('#system-course-picture-class');

        //     if ($('[name=coursePicture]:checked').val() == 0) $('#course-picture-class').hide();
        //     if ($('[name=coursePicture]:checked').val() == 1) $systemCoursePictureClass.hide();

        //     $("[name=coursePicture]").on("click", function() {
        //         if ($("[name=coursePicture]:checked").val() == 0) {
        //             $systemCoursePictureClass.show();
        //             $('#course-picture-class').hide();
        //         }
        //         if ($("[name=coursePicture]:checked").val() == 1) {
        //             $systemCoursePictureClass.hide();
        //             $('#course-picture-class').show();
        //         }
        //     });
        //     var $defaultCoursePicture = $("[name=defaultCoursePicture]");
        //     $("[name=coursePicture]").change(function() {
        //         $defaultCoursePicture.val($("[name=coursePicture]:checked").val());
        //     });
        // };


        // var validator = new Validator({
        //     element: '#course-form'
        // });

        // validator.addItem({
        //     element: '[name="chapter_name"]',
        //     required: true
        // });

        // validator.addItem({
        //     element: '[name="part_name"]',
        //     required: true
        // });

    };

});