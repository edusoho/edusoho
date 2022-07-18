import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import Captcha from 'app/common/captcha';

let captcha = new Captcha({drag:{limitType:"groupThread", bar:'#drag-btn', target: '.js-jigsaw'}});

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

var captchaProp = null;
if($("input[name=enable_anti_brush_captcha]").val() == 1){
  captchaProp = {
    captchaClass: captcha,
    isShowCaptcha: $(captcha.params.maskClass).length ? 1 : 0,
  };
}

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
  captcha: captchaProp,
});

captcha.on('success',function(data){
  formValidator.settings.captcha.isShowCaptcha = 0;
  $("input[name=_dragCaptchaToken]").val(data.token);
  $userThreadForm.submit();
})


$(groupThreadAddBtn).click(function(){
  if(formValidator.form()) {
    $userThreadForm.submit();
  }
});



