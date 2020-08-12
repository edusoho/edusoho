define(function (require, exports, module) {

    let WebUploader = require('edusoho.webuploader');
    let Notify = require('common/bootstrap-notify');

    exports.run = function () {
        let $form = $("#license-form");

        if ($('.setting_permit').length > 1){
            $('#remove-permit-picture-btn').show();
        }

        let uploader = new WebUploader({element: '#license-picture-upload'});

        uploader.on('uploadSuccess', function (file, response) {
            let url = $("#license-picture-upload").data("gotoUrl");

            $.post(url, response, function (data) {
                $("#license-picture-container").html('<img src="' + data.url + '">');
                $form.find('#license_picture').val(data.path);
                $("#license-picture-remove").show();
                Notify.success(Translator.trans('admin.setting.upload_license_picture_success_hint'));
            });
        });

        $("#license-picture-remove").on('click', function () {
            if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
            let $btn = $(this);
            $.post($btn.data('url'), function () {
                $("#license-picture-container").html('');
                $form.find('#license_picture').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.delete_license_picture_success_hint'));
            }).error(function () {
                Notify.danger(Translator.trans('admin.setting.delete_license_picture_fail_hint'));
            });
        });

        let uploadPermit = function ($uploaderArray, $permitIndex) {
            $uploaderArray[$permitIndex].on('uploadSuccess', function (file, response) {
                let permitPicture = "permit_picture" + "_" + $permitIndex;
                let permitPictureUpload = "permit_picture_upload" + "_" + $permitIndex;
                let permitPictureContainer = "permit_picture_container" + "_" + $permitIndex;
                let permitPictureRemove = "permit_picture_remove" + "_" + $permitIndex;

                let url = $("#" + permitPictureUpload).data("gotoUrl");

                $.post(url, response, function (data) {
                    $("#" + permitPictureContainer).html('<img src="' + data.url + '">');
                    $form.find("#" + permitPicture).val(data.path);
                    $("#" + permitPictureRemove).show();
                    Notify.success(Translator.trans('admin.setting.upload_permit_picture_success_hint'));
                });
            });
        };

        let removePermitPicture = function ($removeBtnArray, $permitIndex) {
            $(removeBtnArray[$permitIndex]).on('click', function () {
                let permitPictureContainer = "permit_picture_container" + "_" + $permitIndex;
                let permitPicture = "permit_picture" + "_" + $permitIndex;

                if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
                let $btn = $(this);
                $.post($btn.data('url'), function () {
                    $("#" + permitPictureContainer).html('');
                    $form.find("#" + permitPicture).val('');
                    $btn.hide();
                    Notify.success(Translator.trans('admin.setting.delete_permit_picture_success_hint'));
                }).error(function () {
                    Notify.danger(Translator.trans('admin.setting.delete_permit_picture_fail_hint' + ''));
                });
            });
        };

        //为上传和删除增加事件监听
        let permitIndex = $('.setting_permit').length - 1;
        let uploaderArray = [];
        let removeBtnArray = [];
        for (let i = 0; i <= permitIndex; i++) {
            let uploaderPermitString = "#permit_picture_upload" + "_" + i;
            uploaderArray[i] = new WebUploader({element: uploaderPermitString});
            uploadPermit(uploaderArray, i);

            removeBtnArray[i] = "#permit_picture_remove" + "_" + i;
            removePermitPicture(removeBtnArray, i);
        }

        $("#permit_picture_remove").on('click', function () {
            if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
            let $btn = $(this);
            $.post($btn.data('url'), function () {
                $("#permit_picture_container").html('');
                $form.find('#license_picture').val('');
                $btn.hide();
                Notify.success(Translator.trans('admin.setting.delete_permit_picture_success_hint'));
            }).error(function () {
                Notify.danger(Translator.trans('admin.setting.delete_permit_picture_fail_hint' + ''));
            });
        });

        function appendPermit($id, $permitNum) {

            let permitArea = $('#permit_area');
            let fromPermitDiv = $('.setting_permit').prop("outerHTML");

            let permitsName = 'permits' + '[' + $permitNum + ']' + '[name]';
            let permitsRecordNumber = 'permits' + '[' + $permitNum + ']' + '[record_number]';
            let permitsPicture = 'permits' + '[' + $permitNum + ']' + '[picture]';

            permitArea.append(fromPermitDiv);

            $('.setting_permit').last().find('.permit_name').attr("name", permitsName);
            $('.setting_permit').last().find('.permit_record_number').attr("name", permitsRecordNumber);
            $('.setting_permit').last().find('.permit_picture').attr("name", permitsPicture);

            $('.setting_permit').last().find('.permit_picture').attr("id", function () {
                let id = "permit_picture" + "_" + $permitNum;
                return id;
            });
            $('.setting_permit').last().find('.permit_picture_upload').attr("id", function () {
                let id = "permit_picture_upload" + "_" + $permitNum;
                return id;
            });
            $('.setting_permit').last().find('.permit_picture_container').attr("id", function () {
                let id = "permit_picture_container" + "_" + $permitNum;
                return id;
            });
            $('.setting_permit').last().find('.permit_picture_remove').attr("id", function () {
                let id = "permit_picture_remove" + "_" + $permitNum;
                return id;
            });

            $('.setting_permit').last().find('.permit_name').attr("value", "");
            $('.setting_permit').last().find('.permit_record_number').attr("value", "");
            $('.setting_permit').last().find('.permit_picture').attr("value", "");
            $('.setting_permit').last().find('img').attr("src", "");

            $('.setting_permit').last().find('.permit_picture_remove').hide();

            let uploaderArray = [];
            let uploaderPermitString = "#permit_picture_upload" + "_" + $permitNum;
            uploaderArray[$permitNum] = new WebUploader({element: uploaderPermitString});

            uploadPermit(uploaderArray, $permitNum);
        }

        function removeSettingPermit() {
            let delConfirm = confirm('确定要删除吗？');
            if (delConfirm) {
                $('.setting_permit').last().remove();
            }
            if ($('.setting_permit').length == 1){
                $('#remove-permit-picture-btn').hide();
            }
        }

        $("#add-permit-picture-btn").on('click', function () {
            $("#remove-permit-picture-btn").show();
            if (permitIndex < 9) {
                appendPermit("#add-permit-picture-btn", ++permitIndex);
            } else {
                alert('新增数量已达上限，暂不支持新增哦~');
            }
        });

        $("#remove-permit-picture-btn").on('click', function () {
            removeSettingPermit();
        });

        $('#save_license').on('click', function () {
            $.post($form.data('saveUrl'), $form.serialize(), function (data) {
                Notify.success(data.message);
            })
        })
    };
});
