define(function(require, exports, module) {

    let WebUploader = require('edusoho.webuploader');
    let Notify = require('common/bootstrap-notify');
    let Uploader = require('upload');


    exports.run = function() {
        let $form = $("#license-form");
        let uploader = new WebUploader({
            element: '#license-picture-upload'
        });

        uploader.on('uploadSuccess', function (file, response) {
            let url = $("#license-picture-upload").data("gotoUrl");

            $.post(url, response, function (data) {
                $("#license-picture-container").html('<img src="' + data.url + '">');
                $form.find('#license_picture').val(data.path);
                $("#license-picture-remove").show();
                Notify.success(Translator.trans('admin.setting.upload_license_picture_success_hint'));
            });
        });

        $("#license-picture-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
            let $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#license-picture-container").html('');
                $form.find('#license_picture').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.delete_license_picture_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.delete_license_picture_fail_hint'));
            });
        });

        let upload = function(){
            uploader1.on('uploadSuccess', function(file, response ) {
                let url = $("#permit-picture-upload").data("gotoUrl");

                $.post(url, response ,function(data){
                    $("#permit-picture-container").html('<img src="' + data.url + '">');
                    $form.find('#permit_picture').val(data.path);
                    $("#permit-picture-remove").show();
                    Notify.success(Translator.trans('admin.setting.upload_permit_picture_success_hint'));
                });
            });

            $("#permit-picture-remove").on('click', function(){
                if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
                let $btn = $(this);
                $.post($btn.data('url'), function(){
                    $("#permit-picture-container").html('');
                    $form.find('#license_picture').val('');
                    $btn.hide();
                    Notify.success(Translator.trans('admin.setting.delete_permit_picture_success_hint'));
                }).error(function(){
                    Notify.danger(Translator.trans('admin.setting.delete_permit_picture_fail_hint' + ''));
                });
            });
        }


        let uploader1 = new WebUploader({
            element: '.permit-picture-upload'
        });

        $("#add-permit-picture-btn").on('click', function(){

            let fileNode = $('#file');
            let fromPermitDiv = $('.setting_permit').prop("outerHTML");
            let uploaderArray = [];

            fileNode.append(fromPermitDiv);

            $('.setting_permit').each(function() {
                $('.setting_permit').attr("id", function(j, origValue) {
                    return "setting_permit" + "_" + j;
                });
            });

            $('.setting_permit').each(function (i , val) {
                let children = [];
                children[i] = $('.setting_permit').find('#permit-picture-upload');
                uploaderArray[i] = new WebUploader({
                    element: children[i]
                })
            });

            upload();
        })


        uploader1.on('uploadSuccess', function(file, response ) {
            let url = $("#permit-picture-upload").data("gotoUrl");

            $.post(url, response ,function(data){
                $("#permit-picture-container").html('<img src="' + data.url + '">');
                $form.find('#permit_picture').val(data.path);
                $("#permit-picture-remove").show();
                Notify.success(Translator.trans('admin.setting.upload_permit_picture_success_hint'));
            });
        });

        $("#permit-picture-remove").on('click', function(){
            if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
            let $btn = $(this);
            $.post($btn.data('url'), function(){
                $("#permit-picture-container").html('');
                $form.find('#license_picture').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.delete_permit_picture_success_hint'));
            }).error(function(){
                Notify.danger(Translator.trans('admin.setting.delete_permit_picture_fail_hint' + ''));
            });
        });


        $('#save-license').on('click', function(){
            $.post($form.data('saveUrl'), $form.serialize(), function(data){
                Notify.success(data.message);
            })
        })
    };
});
