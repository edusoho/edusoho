import notify from 'common/notify';
import InputEdit from 'app/common/input-edit';

let editor = CKEDITOR.replace('profile_about', {
  toolbar: 'Simple',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
});

$('.js-date').datetimepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  minView: 'month',
  language: document.documentElement.lang
});

$('#user-profile-form').validate({
  rules: {
    'nickname': {
      required: true,
      chinese_alphanumeric: true,
      byte_minlength: 4,
      byte_maxlength: 18,
      nickname: true,
      es_remote: {
        type: 'get',
      }
    },
    'profile[truename]': {
      minlength: 2,
      maxlength: 18
    },
    'profile[title]': {
      maxlength: 24
    },
    'profile[qq]': 'qq',
    'profile[weixin]': 'weixin',
    'profile[weibo]': 'url',
    'profile[blog]': 'url',
    'profile[site]': 'url',
    'profile[mobile]': 'mobile',
    'profile[idcard]': 'idcardNumber',
    'profile[intField1]': {digits: true, maxlength: 9},
    'profile[intField2]': {digits: true, maxlength: 9},
    'profile[intField3]': {digits: true, maxlength: 9},
    'profile[intField4]': {digits: true, maxlength: 9},
    'profile[intField5]': {digits: true, maxlength: 9},
    'profile[floatField1]': 'float',
    'profile[floatField2]': 'float',
    'profile[floatField3]': 'float',
    'profile[floatField4]': 'float',
    'profile[floatField5]': 'float',
    'profile[dateField1]': 'date',
    'profile[dateField2]': 'date',
    'profile[dateField3]': 'date',
    'profile[dateField4]': 'date',
    'profile[dateField5]': 'date',
  }
});

new InputEdit({
  el: '#nickname-form-group',
  success(data) {
    notify('success', Translator.trans(data.message));
  },
  fail(data) {
    if (data.responseJSON.message) {
      notify('danger', Translator.trans(data.responseJSON.message));
    } else {
      notify('danger', Translator.trans('user.settings.basic_info.nickname_change_fail'));
    }
  }
});
