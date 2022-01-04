define(function (require, exports, module) {
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  var Notify = require('common/bootstrap-notify');
  var WebUploader = require('edusoho.webuploader');

  exports.run = function () {
    var phoneNextIndex = parseInt($('[data-role=phone-item-add]').attr('data-length'));

    var validator = new Validator({
      element: '#consult-setting-form',
    });

    Validator.addRule("phoneValidate", function (options) {
      var value = $(options.element).val();
      value = value.replace(/[^\d]/g, '');
      return !(4008041114 == value);
    }, Translator.trans('admin.setting.consult_setting.setting_consult_phone.error_hint'));

    var $consultEnable = parseInt($('.js-consult-enable:checked').val());
    if ($consultEnable) {
      $('.js-add-phone').removeClass('hidden');
      for (var i = 0; i < phoneNextIndex; i++) {
        validator.addItem({
          element: '[name="phone[' + i + '][name]"]',
          required: true,
          display: Translator.trans('admin.setting.consult_setting.setting_consult_name.empty_hint'),
          rule: 'phoneValidate',
        });
        validator.addItem({
          element: '[name="phone[' + i + '][number]"]',
          required: true,
          display: Translator.trans('admin.setting.consult_setting.setting_consult_phone.empty_hint'),
          rule: 'phoneValidate',
        });
      }
    }
    $('.js-consult-enable').on('click', function () {
      $consultEnable = parseInt($(this).val());
      if ($consultEnable) {
        $('.js-add-phone').removeClass('hidden');
      } else {
        $('.js-add-phone').addClass('hidden');
      }
      for (var i = 0; i < phoneNextIndex; i++) {
        if ($consultEnable) {
          validator.addItem({
            element: '[name="phone[' + i + '][name]"]',
            required: true,
            display: Translator.trans('admin.setting.consult_setting.setting_consult_name.empty_hint'),
            rule: 'phoneValidate',
          });
          validator.addItem({
            element: '[name="phone[' + i + '][number]"]',
            required: true,
            display: Translator.trans('admin.setting.consult_setting.setting_consult_phone.empty_hint'),
            rule: 'phoneValidate',
          });
        } else {
          validator.removeItem('[name="phone[' + i + '][name]"]');
          validator.removeItem('[name="phone[' + i + '][number]"]');
        }
      }
    });

    $("#qq-property-tips").popover({
      html: true,
      trigger: 'click',//'hover','click'
      placement: 'left',//'bottom',
      content: $("#qq-property-tips-html").html()
    });

    $("#qq-group-property-tips").popover({
      html: true,
      trigger: 'click',//'hover','click'
      placement: 'left',//'bottom',
      content: $("#qq-group-property-tips-html").html()
    });

    var $form = $("#consult-setting-form");
    var uploader = new WebUploader({
      element: '#consult-upload'
    });

    uploader.on('uploadSuccess', function (file, response) {
      var url = $("#consult-upload").data("gotoUrl");

      $.post(url, response, function (data) {
        $("#consult-container").html('<img src="' + data.url + '">');
        $form.find('[name=webchatURI]').val(data.path);
        $("#consult-webchat-del").show();
        Notify.success(Translator.trans('admin.setting.consult_setting.upload_qrcode_success_hint'));
      });
    });

    $('[data-role=item-add]').on('click', function () {
      var nextIndex = $(this).attr('data-length');
      nextIndex = parseInt(nextIndex);
      if (nextIndex > 9) {
        Notify.danger(Translator.trans('admin.setting.consult_setting.setting_max_num_hint'));
        return;
      }
      var $parent = $('#' + $(this).attr('data-parentId'));
      var $first = $parent.children(':first');
      var $template = $('[data-role=template]');

      var fisrtplaceholder = $first.find('input:first').attr('placeholder');
      var middleplaceholder = $first.find('input:eq(1)').attr('placeholder');
      var thirdplaceholder = $first.find('input:eq(2)').attr('placeholder');
      var firstname = $first.find('input:first').attr('name');
      var middlename = $first.find('input:eq(1)').attr('name');
      var thirdname = $first.find('input:eq(2)').attr('name');
      firstname = firstname.replace(/\d/, nextIndex);
      middlename = middlename.replace(/\d/, nextIndex);
      thirdname = thirdname.replace(/\d/, nextIndex);
      $template.find('input:first').attr('placeholder', fisrtplaceholder);
      $template.find('input:eq(1)').attr('placeholder', middleplaceholder);
      $template.find('input:eq(2)').attr('placeholder', thirdplaceholder);
      $template.find('input:first').attr('name', firstname);
      $template.find('input:eq(1)').attr('name', middlename);
      $template.find('input:eq(2)').attr('name', thirdname);

      $parent.append($template.html());

      $('[data-role=item-delete]').on('click', function () {
        $(this).parent().parent().remove();
      });

      nextIndex = nextIndex + 1;
      $(this).attr('data-length', nextIndex);
    });

    $('#consult-phone').on('click', '[data-role=phone-item-delete]',function () {
      validator.removeItem($(this).prev());
      validator.removeItem($(this).parent().prev().children(0));
      $(this).closest('.has-feedback').remove();
    });

    $('[data-role=phone-item-add]').on('click', function () {
      if (phoneNextIndex > 9) {
        Notify.danger(Translator.trans('admin.setting.consult_setting.setting_max_num_hint'));
        return;
      }
      var $parent = $('#' + $(this).attr('data-parentId'));
      var $first = $parent.children(':first');
      var $template = $('[data-role=phone-template]');
      var fisrtplaceholder = $first.find('input:first').attr('placeholder');
      var middleplaceholder = $first.find('input:eq(1)').attr('placeholder');
      var firstname = $first.find('input:first').attr('name');
      var middlename = $first.find('input:eq(1)').attr('name');
      firstname = firstname.replace(/\d/, phoneNextIndex);
      middlename = middlename.replace(/\d/, phoneNextIndex);
      $template.find('input:first').attr('placeholder', fisrtplaceholder);
      $template.find('input:eq(1)').attr('placeholder', middleplaceholder);
      $template.find('input:first').attr('name', firstname);
      $template.find('input:eq(1)').attr('name', middlename);
      $parent.append($template.html());
      $template.find('input:first').attr('name', '');
      $template.find('input:eq(1)').attr('name', '');
      validator.addItem({
        element: '[name="phone[' + phoneNextIndex + '][name]"]',
        required: true,
        display: Translator.trans('admin.setting.consult_setting.setting_consult_name.empty_hint'),
        rule: 'phoneValidate',
      });
      validator.addItem({
        element: '[name="phone[' + phoneNextIndex + '][number]"]',
        required: true,
        display: Translator.trans('admin.setting.consult_setting.setting_consult_phone.empty_hint'),
        rule: 'phoneValidate',
      });
      phoneNextIndex = phoneNextIndex + 1;
      $(this).attr('data-length', phoneNextIndex);
    });

    $('[data-parentId=consult-qqgroup]').on('click', function () {
      var nextIndex = $(this).attr('data-length');
      nextIndex = parseInt(nextIndex);
      if (nextIndex > 9) {
        Notify.danger(Translator.trans('admin.setting.consult_setting.setting_max_num_hint'));
        return;
      }
      var $parent = $('#' + $(this).attr('data-parentId'));
      var $first = $parent.children(':first');
      var $template = $('[data-role=qqGroupTemplate]');

      var firstPlaceholder = $first.find('input:eq(0)').attr('placeholder');
      var midPlaceholder = $first.find('input:eq(1)').attr('placeholder');
      var lastPlaceholder = $first.find('input:eq(2)').attr('placeholder');
      var firstName = $first.find('input:eq(0)').attr('name');
      var midName = $first.find('input:eq(1)').attr('name');
      var lastName = $first.find('input:eq(2)').attr('name');
      firstName = firstName.replace(/\d/, nextIndex);
      midName = midName.replace(/\d/, nextIndex);
      lastName = lastName.replace(/\d/, nextIndex);
      $template.find('input:eq(0)').attr('placeholder', firstPlaceholder);
      $template.find('input:eq(1)').attr('placeholder', midPlaceholder);
      $template.find('input:eq(2)').attr('placeholder', lastPlaceholder);

      $template.find('input:eq(0)').attr('name', firstName);
      $template.find('input:eq(1)').attr('name', midName);
      $template.find('input:eq(2)').attr('name', lastName);

      $parent.append($template.html());

      $('[data-role=item-delete]').on('click', function () {
        $(this).parent().parent().remove();
      });

      nextIndex = nextIndex + 1;
      $(this).attr('data-length', nextIndex);
    });

    $('[data-role=item-delete]').on('click', function () {
      $(this).parent().parent().remove();
    });

    $('#consult-webchat-del').on('click', function () {
      if (!confirm(Translator.trans('admin.setting.consult_setting.delete_hint'))) return false;
      $.post($(this).data('url'), function (response) {
        $("#consult-container").html('');
        $('[name=webchatURI]').val('');
        $("#consult-webchat-del").hide();
      });
    });
  }
});