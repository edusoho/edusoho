let editor = CKEDITOR.replace('profile_about', {
  toolbar: 'Simple',
  filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
});

$(".date").datetimepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  minView: 'month'
});

$("#user-profile-form").validate({
  rules: {
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
})
