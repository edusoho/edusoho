define(function(require, exports, module) {

  let Validator = require('bootstrap.validator');
  require('es-ckeditor');
  require('common/validator-rules').inject(Validator);
  let Notify = require('common/bootstrap-notify');
  require('/bundles/topxiaadmin/js/controller/system/common');

  exports.run = function() {

    // group: 'default'
    CKEDITOR.replace('user_terms_body', {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: $('#user_terms_body').data('imageUploadUrl')
    });

    CKEDITOR.replace('privacy_policy_body', {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: $('#privacy_policy_body').data('imageUploadUrl')
    });

    $('.hiddenJsAction').click(function() {
      $('.dync_visible').hide();
      let protective = $('input[name=register_protective]:checked').val();
      let level = $('[name=password_level]:checked').val();
      let mode = $('input[name=register_mode]').val();

      $('.' + protective + '_protective_' + mode).show();

      if (mode !== 'closed') {
        $('.not_closed_mode' + '.' + protective + '_protective').show();
        $('.not_closed_mode' + '.' + level + '_password_level').show();
      }
    });

    $('input[name=register_protective]').change(function() {
      $('.hiddenJsAction').click();
    });

    $('[name=password_level]').change(function() {
      $('.hiddenJsAction').click();
    });

    let validator = new Validator({
      element: '#auth-form',
      onFormValidated: function() {
        $('input[name="email_enabled"]').trigger('change');
      }
    });

    if ($('input[name="email_activation_title"]').length > 0) {
      validator.addItem({
        element: '[name="email_activation_title"]',
        required: true
      });
    }

    validator.addItem({
      element: '[name="email_enabled"]',
      required: true,
      rule: 'isEmailVerified'
    });

    Validator.addRule('isEmailVerified', function(options) {
      let checked = false;
      options.element.each(function(i, item) {
        if ($(item).val() === 'opened' && $(item).prop('checked')) {
          checked = true;
          return false;
        }
      });

      if (!checked) {
        $('.js-email-send-check, .js-email-status').addClass('hidden');
        return true;
      }
      if (app.arguments.emailVerified == 1) {
        $('.js-email-send-check').removeClass('hidden').trigger('click');
      } else {
        $('.js-email-send-check').addClass('hidden');
      }
      return app.arguments.emailVerified == 1;
    }, Translator.trans('admin.setting.auth.email_verified_hint'));

    $('.js-email-send-check').on('click', function() {
      $('.js-email-status').removeClass().addClass('alert alert-info js-email-status').html(Translator.trans('正在检测.....'));

      $.ajax({
        url: $('.js-email-send-check').data('url'),
        timeout: 3500 // sets timeout to 3 seconds
      }).done(function(resp) {
        if (resp.status) {
          $('.js-email-status').removeClass('alert-info').addClass('alert-success').html('<span class="text-success">' + resp.message + '</span>');
        } else {
          $('input[name="email_enabled"][value="closed"]').prop('checked', true);
          $('.js-email-send-check').addClass('hidden');
          $('.js-email-status').removeClass('alert-info').addClass('alert-danger').html(Translator.trans('<span class="text-danger">邮件发送异常,请检查<a target="_blank" href="' + $('.js-email-status').data('url') + '">邮件服务器设置</a>是否正确</span>'));
        }
      })
        .fail(function(resp) {
          console.log('fail');
          $('input[name="email_enabled"][value="closed"]').prop('checked', true);
          $('.js-email-send-check').addClass('hidden');
          $('.js-email-status').removeClass('alert-info').addClass('alert-danger').html(Translator.trans('<span class="text-danger">邮件发送异常,请检查<a target="_blank" href="' + $('.js-email-status').data('url') + '">邮件服务器设置</a>是否正确</span>'));
        });
    });

    $('.hiddenJsAction').click();

    $('.model').on('click', function() {
      let old_modle_value = $('.model.btn-primary').data('modle');
      $('.model').removeClass('btn-primary');
      $(this).addClass('btn-primary');
      let modle = $(this).data('modle');

      if (modle !== 'email' || modle !== 'email_or_mobile') {
        if ($('input[name=email_enabled]').parents('.form-group').hasClass('has-error')) {
          $('input[name=email_enabled][value="closed"]').prop('checked', true);
          validator.query('[name="email_enabled"]').execute();
        }
      }
      if (modle === 'mobile' || modle === 'email_or_mobile') {
        if ($('input[name=_cloud_sms]').val() != 1) {
          $('.model').removeClass('btn-primary');
          $('[data-modle="' + old_modle_value + '"]').addClass("btn-primary");
          modle = old_modle_value;

          Notify.danger(Translator.trans('admin_v2.user.cloude_sms_enable_hint'));
        }
      }

      $('[name="register_mode"]').val(modle);
      if (modle === 'email' || modle === 'email_or_mobile') {
        $('.email-content').removeClass('hidden');
      } else {
        $('.email-content').addClass('hidden');
      }

      if (modle === 'mobile' || modle === 'email_or_mobile') {
        $('.js-mobile-tip').removeClass('hidden');
      } else {
        $('.js-mobile-tip').addClass('hidden');
      }

      $('.hiddenJsAction').click();
    });
  };
});