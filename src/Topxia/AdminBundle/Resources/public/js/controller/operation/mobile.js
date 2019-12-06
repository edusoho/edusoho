 define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var WebUploader = require('edusoho.webuploader');
    require('es-ckeditor');

    exports.run = function() {

        $('#upgrade-modal').modal('show');

        var $form = $("#mobile-form");

        var uploader1 = new WebUploader({
          element: '#mobile-banner1-upload'
        });

        uploader1.on('uploadSuccess', function(file, response ) {
          var url = $("#mobile-banner1-upload").data('url');
          $.post(url, response, function (data) {
            var responseData = $.parseJSON(data);
            $("#mobile-banner1-container").html('<img src="' + responseData.url + '">');
            $form.find('[name=banner1]').val(responseData.path);
            $("#mobile-banner1-remove").show();
            $form.find('div[role="banner1-setting"]').show();
            Notify.success(Translator.trans('admin.operation.upload_carousel_one_success_hint'));
          });

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
            if (!confirm(Translator.trans('admin.operation.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner1-container").html('');
                $form.find('[name=banner1]').val('');
                $btn.hide();
                $form.find('div[role="banner1-setting"]').hide();
                Notify.success(Translator.trans('admin.operation.delete_carousel_one_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.operation.delete_carousel_one_fail_hint'));
            });
        });

        var uploader2 = new WebUploader({
          element: '#mobile-banner2-upload'
        });

        uploader2.on('uploadSuccess', function(file, response ) {
          var url = $("#mobile-banner2-upload").data('url');
          $.post(url, response, function (data) {
            var responseData = $.parseJSON(data);
            $("#mobile-banner2-container").html('<img src="' + responseData.url + '">');
            $form.find('[name=banner2]').val(responseData.path);
            $("#mobile-banner2-remove").show();
            $form.find('div[role="banner2-setting"]').show();
            Notify.success(Translator.trans('admin.operation.upload_carousel_two_success_hint'));
          })

        });


        $("input[role='bannerClick2']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl2").show();
            }else{
                $("#bannerUrl2").hide();
            }
        })

        $("#mobile-banner2-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.operation.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner2-container").html('');
                $form.find('[name=banner2]').val('');
                $btn.hide();
                $form.find('div[role="banner2-setting"]').hide();
                Notify.success(Translator.trans('dmin.operation.delete_carousel_two_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.operation.delete_carousel_two_fail_hint'));
            });
        });

        var uploader3 = new WebUploader({
          element: '#mobile-banner3-upload'
        });

        uploader3.on('uploadSuccess', function(file, response ) {
          var url = $("#mobile-banner3-upload").data('url');
          $.post(url, response, function (data) {
            var responseData = $.parseJSON(data);
            $("#mobile-banner3-container").html('<img src="' + responseData.url + '">');
            $form.find('[name=banner3]').val(responseData.path);
            $("#mobile-banner3-remove").show();
            $form.find('div[role="banner3-setting"]').show();
            Notify.success(Translator.trans('admin.operation.upload_carousel_three_success_hint'));
          })

        });

        $("input[role='bannerClick3']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl3").show();
            }else{
                $("#bannerUrl3").hide();
            }
        })

        $("#mobile-banner3-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.operation.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner3-container").html('');
                $form.find('[name=banner3]').val('');
                $btn.hide();
                $form.find('div[role="banner3-setting"]').hide();
                Notify.success(Translator.trans('admin.operation.delete_carousel_three_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.operation.delete_carousel_three_fail_hint'));
            });
        });

        var uploader4 = new WebUploader({
          element: '#mobile-banner4-upload'
        });

        uploader4.on('uploadSuccess', function(file, response ) {
          var url = $("#mobile-banner4-upload").data('url');
          $.post(url, response, function (data) {
            var responseData = $.parseJSON(data);
            $("#mobile-banner4-container").html('<img src="' + responseData.url + '">');
            $form.find('[name=banner4]').val(responseData.path);
            $("#mobile-banner4-remove").show();
            $form.find('div[role="banner4-setting"]').show();
            Notify.success(Translator.trans('admin.operation.upload_carousel_four_success_hint'));
          })

        });

        $("input[role='bannerClick4']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl4").show();
            }else{
                $("#bannerUrl4").hide();
            }
        })

        $("#mobile-banner4-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.operation.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner4-container").html('');
                $form.find('[name=banner4]').val('');
                $btn.hide();
                $form.find('div[role="banner4-setting"]').hide();
                Notify.success(Translator.trans('admin.operation.delete_carousel_four_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.operation.delete_carousel_four_fail_hint'));
            });
        });

        var uploader5 = new WebUploader({
          element: '#mobile-banner5-upload'
        });

        uploader5.on('uploadSuccess', function(file, response ) {
          var url = $("#mobile-banner5-upload").data('url');
          $.post(url, response, function (data) {
            var responseData = $.parseJSON(data);
            $("#mobile-banner5-container").html('<img src="' + responseData.url + '">');
            $form.find('[name=banner5]').val(responseData.path);
            $("#mobile-banner5-remove").show();
            $form.find('div[role="banner5-setting"]').show();
            Notify.success(Translator.trans('admin.operation.upload_carousel_five_success_hint'));
          })
        });

        $("input[role='bannerClick5']").on('click', function(){
            if($(this).val()==1) {
                $("#bannerUrl5").show();
            }else{
                $("#bannerUrl5").hide();
            }
        })

        $("#mobile-banner5-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.operation.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#mobile-banner5-container").html('');
                $form.find('[name=banner5]').val('');
                $btn.hide();
                $form.find('div[role="banner5-setting"]').hide();
                Notify.success(Translator.trans('admin.operation.delete_carousel_five_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.operation.delete_carousel_five_fail_hint'));
            });
        });


    };

});