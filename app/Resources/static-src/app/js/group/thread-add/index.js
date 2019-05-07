import AttachmentActions from 'app/js/attachment/widget/attachment-actions';

let $userThreadForm = $('#user-thread-form');
let groupThreadAddBtn = '#groupthread-save-btn';
let threadContent = 'thread_content';

new AttachmentActions($userThreadForm);
let editor = CKEDITOR.replace(threadContent, {
  toolbar: 'Full',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#' + threadContent).data('imageUploadUrl'),
  allowedContent: true,
  height: 300
});
editor.on('change', () => {
  $('#' + threadContent).val(editor.getData());
});
editor.on('blur', () => {
  $('#' + threadContent).val(editor.getData());
});

let formValidator = $userThreadForm.validate({
  currentDom: groupThreadAddBtn,
  rules: {
    'thread[title]': {
      required: true,
      minlength: 2,
      maxlength: 100
    },
    'thread[content]': {
      required: true,
      minlength: 2,
    }
  },
});

$(groupThreadAddBtn).click(function(){
  if(formValidator.form()) {
    $userThreadForm.submit();
  }
});



