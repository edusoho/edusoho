define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');
    require('ckeditor');

    exports.run = function() {

        var $form = $("#mobile-form");

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

        $("input[name='enabled']").on("click",function(){
            $this = $(this);
            if($this.val() == 1){
                $this.parents('.form-group').next('.form-group').removeClass('hidden');
            }else{
                $this.parents('.form-group').next('.form-group').addClass('hidden');
            }
        })

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

       group: 'default'
        CKEDITOR.replace('mobile_about', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#mobile_about').data('imageUploadUrl')
        });

        //

        var uploader = new Uploader({
            trigger: '#site-applogo-upload',
            name: 'applogo',
            action: $('#site-applogo-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传app图标失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#site-applogo-container").html('<img src="' + response.url + '">');
                $form.find('[name=applogo]').val(response.path);
                $("#site-applogo-remove").show();
                Notify.success('上传app图标成功！');
            }
        });

        $("#site-applogo-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#site-applogo-container").html('');
                $form.find('[name=applogo]').val('');
                $btn.hide();
                Notify.success('删除网校app图标成功！');
            }).error(function(){
                Notify.danger('删除网校app图标失败！');
            });
        });

        //

        var uploader = new Uploader({
            trigger: '#site-appcover-upload',
            name: 'appcover',
            action: $('#site-appcover-upload').data('url'),
            data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
            accept: 'image/*',
            error: function(file) {
                Notify.danger('上传app封面失败，请重试！')
            },
            success: function(response) {
                response = $.parseJSON(response);
                $("#site-appcover-container").html('<img src="' + response.url + '">');
                $form.find('[name=appcover]').val(response.path);
                $("#site-appcover-remove").show();
                Notify.success('上传app封面成功！');
            }
        });

        $("#site-appcover-remove").on('click', function(){
            if (!confirm('确认要删除吗？')) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#site-appcover-container").html('');
                $form.find('[name=appcover]').val('');
                $btn.hide();
                Notify.success('删除app封面成功！');
            }).error(function(){
                Notify.danger('删除app封面失败！');
            });
        });

    };

});