define(function (require, exports) {

  let WebUploader = require('edusoho.webuploader');
  let Notify = require('common/bootstrap-notify');

  exports.run = function () {
    let $form = $("#license-form");
    let permitIndex = $('.setting_permit').length - 1;
    $('.setting_permit').last().find('.addSettingPermitBtn').show();

    let licensePictureUploader = new WebUploader({element: '#license-picture-upload'});
    licensePictureUploader.on('uploadSuccess', function (file, response) {
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
      $("#license-picture-container").html('');
      $form.find('#license_picture').val('');
      $btn.hide();
      Notify.success(Translator.trans('admin.setting.delete_license_picture_success_hint'));
    }).error(function () {
      Notify.danger(Translator.trans('admin.setting.delete_license_picture_fail_hint'));
    });

    let uploadPermitPicture = function ($uploaderArray, $permitIndex) {
      $uploaderArray[$permitIndex].on('uploadSuccess', function (file, response) {
        let permitPicture = "permit_picture_" + $permitIndex;
        let permitPictureUpload = "permit_picture_upload_" + $permitIndex;
        let permitPictureContainer = "permit_picture_container_" + $permitIndex;
        let permitPictureRemove = "permit_picture_remove_" + $permitIndex;

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
      $($removeBtnArray[$permitIndex]).on('click', function () {
        let permitPictureContainer = "permit_picture_container_" + $permitIndex;
        let permitPicture = "permit_picture_" + $permitIndex;

        if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
        let $btn = $(this);
        $("#" + permitPictureContainer).html('');
        $form.find("#" + permitPicture).val('');
        $btn.hide();
        Notify.success(Translator.trans('admin.setting.delete_permit_picture_success_hint'));
      }).error(function () {
        Notify.danger(Translator.trans('admin.setting.delete_permit_picture_fail_hint'));
      });
    };

    let removeSettingPermit = function ($removeSettingPermitBtnArray, $permitIndex) {
      $($removeSettingPermitBtnArray[$permitIndex]).on('click', function () {
        let $settingPermit = '#settingPermit_' + $permitIndex;
        let delConfirm = confirm(Translator.trans('admin.setting.delete_permit_setting_delete_confirom'));
        if (delConfirm) {
          $($settingPermit).remove();
          $(".setting_permit").last().find(".addSettingPermitBtn").show();
        }
      })
    };

    let addSettingPermit = function ($addSettingPermitBtnArray, $permitIndex) {
      $($addSettingPermitBtnArray[$permitIndex]).on('click', function () {
        $(this).hide();
        if ($('.setting_permit').length < 10) {
          appendPermit("#settingPermit_", ++permitIndex);
        } else {
          Notify.warning(Translator.trans('admin.setting.delete_permit_setting_max_number'));
        }
      })
    };

    let permitSettingElementAction = function($permitIndex) {
      let uploaderArray = [];
      let removeBtnArray = [];
      let removeSettingPermitBtnArray = [];
      let addSettingPermitBtnArray = [];
      if ($permitIndex === 0) {
        $('#removeSettingPermitBtn_0').hide();
      }
      let uploaderPermitString = "#permit_picture_upload_" + $permitIndex;
      if ($(uploaderPermitString).length > 0) {
        uploaderArray[$permitIndex] = new WebUploader({element: uploaderPermitString});
        uploadPermitPicture(uploaderArray, $permitIndex);
      }

      removeBtnArray[$permitIndex] = "#permit_picture_remove_" + $permitIndex;
      removeSettingPermitBtnArray[$permitIndex] = "#removeSettingPermitBtn_" + $permitIndex;
      addSettingPermitBtnArray[$permitIndex] = "#addSettingPermitBtn_" + $permitIndex;
      removePermitPicture(removeBtnArray, $permitIndex);
      removeSettingPermit(removeSettingPermitBtnArray, $permitIndex);
      addSettingPermit(addSettingPermitBtnArray, $permitIndex);
    };

    let permitSettingAction = function () {
      let permitIndex = $('.setting_permit').length - 1;
      for (let i = 0; i <= permitIndex; i++) {
        permitSettingElementAction(i);
      }
    };

    let permitSettingLastAction = function () {
      let permitIndex = $('.setting_permit').last().attr('id').replace(/[^0-9]/ig, "");
      permitSettingElementAction(permitIndex);
    };

    let resetElementId = function ($id, $permitNum) {
      let lastSettingPermit = $('.setting_permit').last();
      lastSettingPermit.find($id).attr("id", function () {
        $id = ($id + "_" + $permitNum).replace(/\./ , '');
        return $id;
      })
    };

    function appendPermit($id, $permitNum) {
      let permitArea = $('#permit_area');
      let fromPermitDiv = $('.setting_permit').prop("outerHTML");

      let permitsName = 'permits' + '[' + $permitNum + ']' + '[name]';
      let permitsRecordNumber = 'permits' + '[' + $permitNum + ']' + '[record_number]';
      let permitsPicture = 'permits' + '[' + $permitNum + ']' + '[picture]';

      permitArea.append(fromPermitDiv);

      let lastSettingPermit = $('.setting_permit').last();
      lastSettingPermit.find('.permit_picture_remove').hide();
      lastSettingPermit.find('.removeSettingPermitBtn').show();
      lastSettingPermit.find('.addSettingPermitBtn').show();

      lastSettingPermit.find('.permit_name').attr("name", permitsName);
      lastSettingPermit.find('.permit_record_number').attr("name", permitsRecordNumber);
      lastSettingPermit.find('.permit_picture').attr("name", permitsPicture);

      lastSettingPermit.attr("id", function () {
        let id = "settingPermit_" + $permitNum;
        return id;
      });

      resetElementId('.permit_picture', $permitNum);
      resetElementId('.permit_picture_upload', $permitNum);
      resetElementId('.permit_picture_container', $permitNum);
      resetElementId('.permit_picture_remove', $permitNum);
      resetElementId('.removeSettingPermitBtn', $permitNum);
      resetElementId('.addSettingPermitBtn', $permitNum);

      lastSettingPermit.find('.permit_name').attr("value", "");
      lastSettingPermit.find('.permit_record_number').attr("value", "");
      lastSettingPermit.find('.permit_picture').attr("value", "");
      lastSettingPermit.find('img').attr("src", "");

      permitSettingLastAction();
    }

    $('#save_license').on('click', function () {
      $.post($form.data('saveUrl'), $form.serialize(), function (data) {
        Notify.success(data.message);
      })
    });

    permitSettingAction();
  };
});