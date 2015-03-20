define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');
    require('ckeditor');

    exports.run = function() {

        var $form = $("#mobile-form");
        var uploader = new Uploader({
            trigger: '#mobile-logo-upload',
            name: 'logo',
            action: $('#mobile-logo-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网校LOGO失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-logo-container").html('<img src="' + response.url + '">');
                $form.find('[name=logo]').val(response.path);
                $("#mobile-logo-remove").show();
                Notify.success('上传网校LOGO成功！');
            }
        });

        $("#mobile-logo-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-logo-container").html('');
                $form.find('[name=logo]').val('');
                $btn.hide();
                Notify.success('删除网校LOGO成功！');
            }).error(function(){
                Notify.danger('删除网校LOGO失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-splash1-upload',
            name: 'splash1',
            action: $('#mobile-splash1-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网校启动图1失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-splash1-container").html('<img src="' + response.url + '">');
                $form.find('[name=splash1]').val(response.path);
                $("#mobile-splash1-remove").show();
                Notify.success('上传网校启动图1成功！');
            }
        });

        $("#mobile-splash1-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-splash1-container").html('');
                $form.find('[name=splash1]').val('');
                $btn.hide();
                Notify.success('删除网校启动图1成功！');
            }).error(function(){
                Notify.danger('删除网校启动图1失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-splash2-upload',
            name: 'splash2',
            action: $('#mobile-splash2-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网校启动图2失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-splash2-container").html('<img src="' + response.url + '">');
                $form.find('[name=splash2]').val(response.path);
                $("#mobile-splash2-remove").show();
                Notify.success('上传网校启动图2成功！');
            }
        });

        $("#mobile-splash2-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-splash2-container").html('');
                $form.find('[name=splash2]').val('');
                $btn.hide();
                Notify.success('删除网校启动图2成功！');
            }).error(function(){
                Notify.danger('删除网校启动图2失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-splash3-upload',
            name: 'splash3',
            action: $('#mobile-splash3-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网校启动图3失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-splash3-container").html('<img src="' + response.url + '">');
                $form.find('[name=splash3]').val(response.path);
                $("#mobile-splash3-remove").show();
                Notify.success('上传网校启动图3成功！');
            }
        });

        $("#mobile-splash3-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-splash3-container").html('');
                $form.find('[name=splash3]').val('');
                $btn.hide();
                Notify.success('删除网校启动图3成功！');
            }).error(function(){
                Notify.danger('删除网校启动图3失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-splash4-upload',
            name: 'splash4',
            action: $('#mobile-splash4-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网校启动图4失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-splash4-container").html('<img src="' + response.url + '">');
                $form.find('[name=splash4]').val(response.path);
                $("#mobile-splash4-remove").show();
                Notify.success('上传网校启动图4成功！');
            }
        });

        $("#mobile-splash4-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-splash4-container").html('');
                $form.find('[name=splash4]').val('');
                $btn.hide();
                Notify.success('删除网校启动图4成功！');
            }).error(function(){
                Notify.danger('删除网校启动图4失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-splash5-upload',
            name: 'splash5',
            action: $('#mobile-splash5-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传网校启动图5失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-splash5-container").html('<img src="' + response.url + '">');
                $form.find('[name=splash5]').val(response.path);
                $("#mobile-splash5-remove").show();
                Notify.success('上传网校启动图5成功！');
            }
        });

        $("#mobile-splash5-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-splash5-container").html('');
                $form.find('[name=splash5]').val('');
                $btn.hide();
                Notify.success('删除网校启动图5成功！');
            }).error(function(){
                Notify.danger('删除网校启动图5失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-banner1-upload',
            name: 'banner1',
            action: $('#mobile-banner1-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传轮播图1失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-banner1-container").html('<img src="' + response.url + '">');
                $form.find('[name=banner1]').val(response.path);
                $("#mobile-banner1-remove").show();
                $form.find('div[role="banner1-setting"]').show();
                Notify.success('上传轮播图1成功！');
            }
        });


        $("[data-role='selectBannerCourse']").find('[data-role="selectCourse"]').click(function(){
            $('[data-status="active"]').attr("data-status", "none");
            $(this).attr("data-status","active");
        });


        $("input[role='bannerClick1']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl1").show();
                $("#selectBannerCourse1").hide();
            }else if($(this).val()==2){
                $("#selectBannerCourse1").show();
                $("#bannerUrl1").hide();
            }else{
                $("#bannerUrl1").hide();
                $("#selectBannerCourse1").hide();
            }
        })


        $("input[role='bannerClick2']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl2").show();
                $("#selectBannerCourse2").hide();
            }else if($(this).val()==2){
                $("#selectBannerCourse2").show();
                $("#bannerUrl2").hide();
            }else{
                $("#bannerUrl2").hide();
                $("#selectBannerCourse2").hide();
            }
        })


        $("input[role='bannerClick3']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl3").show();
                $("#selectBannerCourse3").hide();
            }else if($(this).val()==2){
                $("#selectBannerCourse3").show();
                $("#bannerUrl3").hide();
            }else{
                $("#bannerUrl3").hide();
                $("#selectBannerCourse3").hide();
            }
        })



        $("input[role='bannerClick4']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl4").show();
                $("#selectBannerCourse4").hide();
            }else if($(this).val()==2){
                $("#selectBannerCourse4").show();
                $("#bannerUrl4").hide();
            }else{
                $("#bannerUrl4").hide();
                $("#selectBannerCourse4").hide();
            }
        })


        $("input[role='bannerClick5']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl5").show();
                $("#selectBannerCourse5").hide();
            }else if($(this).val()==2){
                $("#selectBannerCourse5").show();
                $("#bannerUrl5").hide();
            }else{
                $("#bannerUrl5").hide();
                $("#selectBannerCourse5").hide();
            }
        })


        $("#mobile-banner1-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner1-container").html('');
                $form.find('[name=banner1]').val('');
                $btn.hide();
                $form.find('div[role="banner1-setting"]').hide();
                Notify.success('删除轮播图1成功！');
            }).error(function(){
                Notify.danger('删除轮播图1失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-banner2-upload',
            name: 'banner2',
            action: $('#mobile-banner2-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传轮播图2失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-banner2-container").html('<img src="' + response.url + '">');
                $form.find('[name=banner2]').val(response.path);
                $("#mobile-banner2-remove").show();
                $form.find('div[role="banner2-setting"]').show();
                Notify.success('上传轮播图2成功！');
            }
        });

        $("input[role='bannerClick2']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl2").show();
            }else{
                $("#bannerUrl2").hide();
            }
        })

        $("#mobile-banner2-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner2-container").html('');
                $form.find('[name=banner2]').val('');
                $btn.hide();
                $form.find('div[role="banner2-setting"]').hide();
                Notify.success('删除轮播图2成功！');
            }).error(function(){
                Notify.danger('删除轮播图2失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-banner3-upload',
            name: 'banner3',
            action: $('#mobile-banner3-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传轮播图3失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-banner3-container").html('<img src="' + response.url + '">');
                $form.find('[name=banner3]').val(response.path);
                $("#mobile-banner3-remove").show();
                $form.find('div[role="banner3-setting"]').show();
                Notify.success('上传轮播图3成功！');
            }
        });

        $("input[role='bannerClick3']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl3").show();
            }else{
                $("#bannerUrl3").hide();
            }
        })

        $("#mobile-banner3-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner3-container").html('');
                $form.find('[name=banner3]').val('');
                $btn.hide();
                $form.find('div[role="banner3-setting"]').hide();
                Notify.success('删除轮播图3成功！');
            }).error(function(){
                Notify.danger('删除轮播图3失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-banner4-upload',
            name: 'banner4',
            action: $('#mobile-banner4-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传轮播图4失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-banner4-container").html('<img src="' + response.url + '">');
                $form.find('[name=banner4]').val(response.path);
                $("#mobile-banner4-remove").show();
                $form.find('div[role="banner4-setting"]').show();
                Notify.success('上传轮播图4成功！');
            }
        });

        $("input[role='bannerClick4']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl4").show();
            }else{
                $("#bannerUrl4").hide();
            }
        })

        $("#mobile-banner4-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner4-container").html('');
                $form.find('[name=banner4]').val('');
                $btn.hide();
                $form.find('div[role="banner4-setting"]').hide();
                Notify.success('删除轮播图4成功！');
            }).error(function(){
                Notify.danger('删除轮播图4失败！');
            });
        });

        var uploader = new Uploader({
            trigger: '#mobile-banner5-upload',
            name: 'banner5',
            action: $('#mobile-banner5-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传轮播图5失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#mobile-banner5-container").html('<img src="' + response.url + '">');
                $form.find('[name=banner5]').val(response.path);
                $("#mobile-banner5-remove").show();
                $form.find('div[role="banner5-setting"]').show();
                Notify.success('上传轮播图5成功！');
            }
        });

        $("input[role='bannerClick5']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl5").show();
            }else{
                $("#bannerUrl5").hide();
            }
        })

        $("#mobile-banner5-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner5-container").html('');
                $form.find('[name=banner5]').val('');
                $btn.hide();
                $form.find('div[role="banner5-setting"]').hide();
                Notify.success('删除轮播图5成功！');
            }).error(function(){
                Notify.danger('删除轮播图5失败！');
            });
        });

        $('li[role="course-item"]').find("[role='course-item-delete']").on('click',function(){
            var courseId=$(this).data("courseId");
            var courseIds = $('input[name="courseIds"]');

            $(this).parents('li[role="course-item"]').remove();
            courseIds.val(courseIds.val().replace(courseId+',', ''));
            if(courseIds.val().split(",").length<=3){
                $('[role="add-course"]').show();
            }
        });

       // group: 'default'
        CKEDITOR.replace('mobile_about', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#mobile_about').data('imageUploadUrl')
        });

    };

});