define(function(require, exports, module) {
  let Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  let Notify = require('common/bootstrap-notify');
  require('jquery.sortable');
  require('/bundles/topxiaadmin/js/controller/system/common');

  exports.run = function() {

    Validator.addRule('name_max', function (options) {
      let maxLength = true;
      if($('.js-select-content').hasClass('hidden')){
        return maxLength;
      }
      let values = $(options.element).val().split("\n");
      values.map(function (value, index, array) {
        if (calculateByteLength(value) > 20) {
          maxLength = false;
        }
      });
      return maxLength;
    }, Translator.trans('user_field.select_type.tip'));

    var $form = $('#field-form');
    var validator = new Validator({
      element: $form,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }

        $('#add-btn').button('submiting').addClass('disabled');
      }
    });

    var titleArr = [Translator.trans('admin.system.user_fields.truename'),Translator.trans('admin.system.user_fields.mobile'),Translator.trans('QQ'),Translator.trans('admin.system.user_fields.company'),Translator.trans('admin.system.user_fields.idcard'),Translator.trans('admin.system.user_fields.gender'),Translator.trans('admin.system.user_fields.career'),Translator.trans('admin.system.user_fields.weibo'),Translator.trans('admin.system.user_fields.wechat')];
    $('#add-btn').on('click', function() {
      var field_title = $('input[name="field_title"]').val();
            
      if($.inArray(field_title, titleArr) >= 0 )
      {
        Notify.danger(Translator.trans('admin.system.user_fields.add_field_title_error_hint'));
        return false;
      }
    });

    function calculateByteLength(string) {
      let length = string.length;
      for (let i = 0; i < string.length; i++) {
        if (string.charCodeAt(i) > 127)
          length++;
      }
      return length;
    }

    validator.addItem({
      element: '[name="field_title"]',
      required: true,
      rule:'minlength{min:2} maxlength{max:20}'
    });

    validator.addItem({
      element: '[name="field_seq"]',
      required: true,
      rule:'positive_integer'
    });

    validator.addItem({
      element: '[name="field_type"]',
      required: true,
      errormessageRequired: Translator.trans('admin.system.user_fields.field_type_input.message')
    });


    $('.fill-userinfo-list').sortable({
      'distance': 20
    });

    $('#field_type').on('change',function(){
            
      $('#type_num').html($(this).children('option:selected').attr('num'));
      if($('#field_type').val() == 'select') {
        $('.js-select-content').removeClass('hidden');
        validator.addItem({
          element: '#select-list',
          required: true,
          rule: 'name_max'
        });
      }else{
        $('.js-select-content').addClass('hidden');
        validator.removeItem('#select-list');
      }
    });

    $('#show-fields-list-btn').on('click',function(){
      $('#show-fields-list').show();
      $('#show-checked-fields-list').hide();
    });

    $('#hide-fields-list-btn').on('click', function() {
      $('#show-fields-list').hide();

      var fieldNameHtml = '';
      $('.fill-userinfo-list input:checkbox:checked').each(function(){
        var fieldName = $(this).closest('li').text();
        fieldNameHtml += '<button type="button" class="btn btn-default btn-xs">'+$.trim(fieldName)+'</button>&nbsp;';
      });

      $('#show-checked-fields-list .pull-left').html(fieldNameHtml);
      $('#show-checked-fields-list').show();
    });

    $('*[data-sms-validate]').on('click',function(){
            
      var isSmsCodeValidate = $(this).data('smsValidate');
      var smsEnabled = $('input[name="_cloud_sms"]').val();
            
      if ($(this).is(':checked')) {
        $(this).closest('li').siblings().find('*[data-sms-validate]').attr('checked',false);
                
        if (isSmsCodeValidate && smsEnabled == '0') {
          Notify.danger(Translator.trans('admin.site.cloude_sms_enable_hint'));
          return false;
        }
        $(this).attr('checked',true);

        $('input[name="mobileSmsValidate"]').val(isSmsCodeValidate);
      } else {
        $('input[name="mobileSmsValidate"]').val(0);
      }
    });

    $('input[name=\'open_student_info\']').change(function(){
      if($(this).val()=='1') {
        $('.open_student_info_tip').addClass('hidden');
      }else{
        $('.open_student_info_tip').removeClass('hidden');
      }
    });

  };

});