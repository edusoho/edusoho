import Captcha from 'app/common/captcha';
let captcha = new Captcha({drag:{limitType:"thread", bar:'#drag-btn', target: '.js-jigsaw'}});

let $form = $('#thread-form');

var captchaProp = null;
if($("input[name=enable_anti_brush_captcha]").val() == 1){
  captchaProp = {
    captchaClass: captcha,
    // isShowCaptcha: $(captcha.params.maskClass).length ? 1 : 0,
    isShowCaptcha: 0,
  };
}
let $btn = $form.find(".js-btn-thread-save");
let validator = $form.validate({
  captcha: captchaProp,
  rules: {
    'thread[title]': {
      required: true,
      trim: true,
      maxlength: 30,
    },
    'thread[content]': {
      required: true,
      maxlength: 10000,
    }
  },
  submitSuccess: function (response) {
    // validator.settings.captcha.isShowCaptcha = 1;
    captcha.hideDrag();
  },
  submitError: function (data) {
    // validator.settings.captcha.isShowCaptcha = 1;
    captcha.hideDrag();
    $btn.button('reset');
  }
});

$form.on("submitHandler", function(){
  captcha.setType("thread");
})

captcha.on('success',function(data){
  if(data.type == 'thread'){
    validator.settings.captcha.isShowCaptcha = 0;
    $form.find("input[name=_dragCaptchaToken]").val(data.token);
    $form.submit();
  }
})

$('.js-btn-thread-save').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loading');
    $form.submit();
  }
});

let editor = CKEDITOR.replace('thread_content', {
  toolbar: 'Thread',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#thread_content').data('imageUploadUrl')
});

editor.on('change', () => {
  $('#thread_content').val(editor.getData());
});
editor.on('blur', () => {
  $('#thread_content').val(editor.getData());
  validator.form();
});