define(function(require, exports, module) {

    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');

    exports.run = function() {
        var $form = $("#site-form");
        var uploader = new WebUploader({
            element: '#site-logo-upload'
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#site-logo-upload").data("gotoUrl");

            $.post(url, response ,function(data){
                $("#site-logo-container").html('<img src="' + data.url + '">');
                $form.find('[name=logo]').val(data.path);
                $("#site-logo-remove").show();
                Notify.success(Translator.trans('admin.site.upload_logo_success_hint'));
            });
        });

        $("#site-logo-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#site-logo-container").html('');
                $form.find('[name=logo]').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.site.delete_logo_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.site.delete_logo_fail_hint'));
            });
        });

        var uploader1 = new WebUploader({
            element: '#site-favicon-upload'
        });

        uploader1.on('uploadSuccess', function(file, response ) {
            var url = $("#site-favicon-upload").data("gotoUrl");

            $.post(url, response ,function(data){
                $("#site-favicon-container").html('<img src="' + data.url + '" style="margin-bottom: 10px;">');
                $form.find('[name=favicon]').val(data.path);
                $("#site-favicon-remove").show();
                Notify.success(Translator.trans('admin.site.upload_favicon_success_hint'));
            });
        });

        $("#site-favicon-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#site-favicon-container").html('');
                $form.find('[name=favicon]').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.site.delete_favicon_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.site.delete_favicon_fail_hint'));
            });
        });

      $('#save-site').on('click', function(){
        $.post($form.data('saveUrl'), $form.serialize(), function(data){
            Notify.success(data.message);
        })
      })
    };

});
