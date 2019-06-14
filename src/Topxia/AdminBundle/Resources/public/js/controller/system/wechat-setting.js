define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    var WebUploader = require('edusoho.webuploader');

    exports.run = function() {

        var uploader = new WebUploader({
          element: '#qrcode-upload'
        });

        uploader.on('uploadSuccess', function(file, response ) {
          $('.js-code-img').attr('src', response.url);
          $('#account_code').val(response.url);
          if ($('.es-qrcode').hasClass('hidden')) {
            $('.es-qrcode').removeClass('hidden');
            $('.code-help-block').addClass('hidden');
          }
        });

        var validator = new Validator({
          element: '#wechat-setting-form',
        });

        $('[data-toggle="switch"]').on('click', function() {
          var $this = $(this);
          var $parent = $this.parent();
          var isEnable = $this.val();
          var reverseEnable = isEnable == 1 ? 0 : 1;

          if ($this.context.id == 'wechat_notification_enabled' && isEnable == '0') {
            var weixinmobChecked = $('#weixinmob_enabled').val();
            var weixinwebChecked = $('#weixinweb_enabled').val();
            if (weixinmobChecked == '0' || weixinwebChecked == '0') {
              Notify.danger(Translator.trans('admin.system.wechat.notification_open'), 3);
              return;
            }
            var uploader = new WebUploader({
              element: '#qrcode-upload'
            });

            uploader.on('uploadSuccess', function(file, response ) {
              $('.js-code-img').attr('src', response.url);
              $('#account_code').val(response.url);
              if ($('.es-qrcode').hasClass('hidden')) {
                $('.es-qrcode').removeClass('hidden');
                $('.code-help-block').addClass('hidden');
              }
            });
          }

          if ($this.context.id == 'weixinweb_enabled' || $this.context.id == 'weixinmob_enabled') {
            var notificationItem = $('#wechat_notification_enabled');
            if (isEnable == '1' && notificationItem.val() == '1') {
              switchCheck('#wechat_notification_enabled', 0);
              $('input[name="wechatSetting[wechat_notification_enabled]"]').change();
            }
          }

          switchCheck(this, reverseEnable);
        });

        var switchCheck = function(target, reverseEnable) {
          var $this = $(target);
          var $parent = $this.parent();

          if ($parent.hasClass('checked')) {
            $parent.removeClass('checked');
          } else {
            $parent.addClass('checked');
          }
          $this.val(reverseEnable);
          $this.next().val(reverseEnable);
        }

        $('[name="loginConnect[weixinweb_enabled]"]').change(function(e) {
          var checked = e.target.value;
          var subItem = $(this).parents('form').children().children('[data-sub="weixinweb"]');

          if (checked == '1') {
            subItem.removeClass('hidden');
            validator.addItem({
              element: '[name="loginConnect[weixinweb_key]"]',
              required: true,
            });
            validator.addItem({
              element: '[name="loginConnect[weixinweb_secret]"]',
              required: true,
            });
          } else {
            subItem.addClass('hidden');
            validator.removeItem('[name="loginConnect[weixinweb_key]"]');
            validator.removeItem('[name="loginConnect[weixinweb_secret]"]');
          }
        });

        $('[name="loginConnect[weixinmob_enabled]"]').change(function(e) {
          var checked = e.target.value;
          var wxpayChecked = $('#wxpay_enabled').val();
          var subItem = $(this).parents('form').children().children('[data-sub="weixinmob"]');

          if (checked == '1' || wxpayChecked == '1') {
            subItem.removeClass('hidden');
          } else {
            subItem.addClass('hidden');
          }

          if (checked == '1') {
            validator.addItem({
              element: '[name="loginConnect[weixinmob_key]"]',
              required: true,
            });
            validator.addItem({
              element: '[name="loginConnect[weixinmob_secret]"]',
              required: true,
            });
            validator.addItem({
              element: '[name="payment[wxpay_mp_secret]"]',
              required: true,
            });
          } else {
            validator.removeItem('[name="loginConnect[weixinmob_key]"]');
            validator.removeItem('[name="loginConnect[weixinmob_secret]"]');
            validator.removeItem('[name="payment[wxpay_mp_secret]"]');
          }
        });

        $('[name="wechatSetting[wechat_notification_enabled]"]').change(function(e) {
          var checked = e.target.value;
          var subItem = $(this).parents('form').children().children('[data-sub="account"]');

          if (checked == '1') {
            subItem.removeClass('hidden');
            validator.addItem({
              element: '[name="wechatSetting[account_code]"]',
              required: true,
            });
          } else {
            subItem.addClass('hidden');
            validator.removeItem('[name="wechatSetting[account_code]"]');
          }
        });

        $('[name="payment[wxpay_enabled]"]').change(function(e) {
          var checked = e.target.value;
          var weixinmobChecked = $('#weixinmob_enabled').val();
          var subItem = $(this).parents('form').children().children('[data-sub="wxpay"]');
          var mobItem = $(this).parents('form').children().children('[data-sub="weixinmob"]');

          if (checked == '1') {
            subItem.removeClass('hidden');
            validator.addItem({
              element: '[name="payment[wxpay_account]"]',
              required: true,
            });
            validator.addItem({
              element: '[name="payment[wxpay_key]"]',
              required: true,
            });
          } else {
            subItem.addClass('hidden');
            validator.removeItem('[name="payment[wxpay_account]"]');
            validator.removeItem('[name="payment[wxpay_key]"]');
          }

          if (checked == '1' || weixinmobChecked == '1') {
            mobItem.removeClass('hidden');
          } else {
            mobItem.addClass('hidden');
          }

          if (checked == '1') {
            validator.addItem({
              element: '[name="loginConnect[weixinmob_key]"]',
              required: true,
            });
            validator.addItem({
              element: '[name="loginConnect[weixinmob_secret]"]',
              required: true,
            });
            validator.addItem({
              element: '[name="payment[wxpay_mp_secret]"]',
              required: true,
            });
          } else {
            validator.removeItem('[name="loginConnect[weixinmob_key]"]');
            validator.removeItem('[name="loginConnect[weixinmob_secret]"]');
            validator.removeItem('[name="payment[wxpay_mp_secret]"]');
          }
        });

        $('input[name="loginConnect[weixinweb_enabled]"][type="checkbox"][value="1"]').change();
        $('input[name="loginConnect[weixinmob_enabled]"][type="checkbox"][value="1"]').change();
        $('input[name="wechatSetting[wechat_notification_enabled]"][type="checkbox"][value="1"]').change();
        $('input[name="payment[wxpay_enabled]"][type="checkbox"][value="1"]').change();

        $('#wechat-setting-form').on('click', '.js-code-view', (event) => {
          var $target = $('.js-code-img');
          var $codeItem = $('.es-icon-qrcode');
          if ($target.hasClass('hidden')) {
            $target.removeClass('hidden');
          } else {
            $target.addClass('hidden');
          }
          event.stopPropagation();
        });

        $('body').on('click', () => {
          var $target = $('.js-code-img');
          if (!$target.hasClass('hidden')) {
            $target.addClass('hidden');
          }
        });
    };

});