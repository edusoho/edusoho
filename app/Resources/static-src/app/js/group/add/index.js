let $userGroupForm = $('#user-group-form');
let $groupIntroduce = $('#groupIntroduce');
let btn = '#group-save-btn';

var editor = CKEDITOR.replace('groupIntroduce', {
  toolbar: 'Full',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $groupIntroduce.data('imageUploadUrl'),
  allowedContent: true,
  height: 300
});

editor.on('change', () => {
  $groupIntroduce.val(editor.getData());
});
editor.on('blur', () => {
  $groupIntroduce.val(editor.getData());
});

let $groupCreateValidator = $userGroupForm.validate({
  currentDom: btn,
  rules: {
    'group[grouptitle]': {
      required: true,
      minlength: 2,
      maxlength: 100
    },
  },
});

$(btn).click(() => {
  if ($groupCreateValidator.form()) {
    $userGroupForm.submit();
  }
});
