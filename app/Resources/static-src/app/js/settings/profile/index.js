import notify from 'common/notify';

let editor = CKEDITOR.replace('profile_about', {
  toolbar: 'Simple',
  filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
});

$(".js-date").datetimepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  minView: 'month',
  language: document.documentElement.lang
});

$("#user-profile-form").validate({
  rules: {
    'nickname': {
			required: true,
			chinese_alphanumeric: true,
			byte_minlength: 4,
			byte_maxlength: 18,
			nickname: true,
			chinese_alphanumeric: true,
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
    'profile[dateField5]': 'date',
    'profile[dateField5]': 'date',
    'profile[dateField5]': 'date',
    'profile[dateField5]': 'date',
    'profile[dateField5]': 'date'
  }
});

$('#form-nickname-submit').on('click', function() {
  let $this = $(this);
  let url = $this.data('url');
  let nickname = $('#nickname').val();
  let data = {
    nickname: nickname
  };

  $.post(url, data).done(function(data) {
    notify('success', Translator.trans(data.message));
    $this.closest('.cd-form-group').find('[data-target="form-static-text"] span').text(nickname);
    $('#nickname').data('save-value', $('#nickname').val());
    $this.siblings('[data-dismiss="form-editable-cancel"]').click();

  }).fail(function(data) {
    if (data.responseJSON.message) {
      notify('danger', Translator.trans(data.responseJSON.message));
    } else {
      notify('danger', Translator.trans('user.settings.basic_info.nickname_change_fail'));
    }
  })
})