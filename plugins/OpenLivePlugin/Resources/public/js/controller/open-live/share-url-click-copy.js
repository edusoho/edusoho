define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Uploader = require('upload');
  var Validator = require('bootstrap.validator');

  exports.run = function() {
    formSubmitInit();
    $('.js-copy-share-url').on('click', function() {
      let urlInput = document.getElementById('shareUrl');
      urlInput.select();
      if (true === document.execCommand('copy')) {
        Notify.success(Translator.trans('复制成功！'));
      } else {
        Notify.danger(Translator.trans('复制失败，请手动复制地址'));
      }
      if ('getSelection' in window) {
        window.getSelection().removeAllRanges();
      } else {
        document.selection.empty();
      }
    });
    var $form = $("#share-form");
    var uploader = new Uploader({
      trigger: '#wechat-share-img-upload',
      name: 'wechatShare',
      action: $('#wechat-share-img-upload').data('url'),
      data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
      accept: 'image/png,image/jpg,image/jpeg,imge/bmp,image/gif',
      error: function(file) {
        Notify.danger(Translator.trans('分享图片上传失败！'));
      },
      success: function(response) {
        if (response.url === '' && response.errorMsg !== 'undefined') {
          Notify.danger(Translator.trans(response.errorMsg));
          return;
        }
        $("#wechat-share-img-container").html('<img style="max-height: 80px;max-width: 80px;" src="' + response.url + '">');
        $form.find('[name=wechatShareImage]').val(response.url);
        Notify.success(Translator.trans('分享图片上传成功！'));
      }
    });

    $('.wechat-share-url-setting-show').on('click', function () {
      $('.js-wechat-share-urcode').hide();
      $('.js-wechat-share-url-setting').show();
    });
  }

  var formSubmitInit = function () {
    let saveBtn = $('#save-share-setting');
    let validator = new Validator({
      element: '#share-form',
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }
        saveBtn.button('submiting').addClass('disabled');
        $.post($form.data('saveUrl'), $form.serialize(), function(res) {
          if (res.success) {
            Notify.success(Translator.trans('公开课微信分享设置保存成功'));
            window.location.reload();
            return;
          }
          let errorMsg = '公开课微信分享设置保存失败';
          if (typeof res.errorMsg !== 'undefined') {
            errorMsg = res.errorMsg;
          }
          Notify.danger(Translator.trans(errorMsg));
          restSubmitBtn(saveBtn);
        }).error(function(){
          Notify.danger(Translator.trans('服务出错，操作失败！'));
          restSubmitBtn(saveBtn);
        });
      }
    });

    validator.addItem({
      element: '[name="shareTitle"]',
      required: true,
      rule: 'maxlength{max:20}'
    });

    validator.addItem({
      element: '[name="shareContent"]',
      required: true,
      rule: 'maxlength{max:50}'
    });

    validator.addItem({
      element: '[name="wechatShareImage"]',
      required: true,
      errormessageRequired: Translator.trans('请上传想要分享的图片'),
    });

    saveBtn.on('click', function () {
      $('#share-form').submit();
    });

    function restSubmitBtn(btn) {
      btn.html('提交');
      btn.removeClass('disabled');
    }
  };
});