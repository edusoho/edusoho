import Captcha from 'app/common/captcha';
let captcha = new Captcha({drag:{limitType:"thread", bar:'#drag-btn', target: '.js-jigsaw'}});

let $form = $('#thread-post-form');

var captchaProp = null;
if($("input[name=enable_anti_brush_captcha]").val() == 1){
  captchaProp = {
    captchaClass: captcha,
    // isShowCaptcha: $(captcha.params.maskClass).length ? 1 : 0,
    isShowCaptcha: 0
  };
}
let $btn = $form.find(".js-btn-thread-save");

let validator = $form.validate({
  captcha: captchaProp,
  rules: {
    'post[content]': {
      required: true
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
  captcha.setType("replyEdit");
})

captcha.on('success',function(data){
  if(data.type == 'replyEdit'){
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

let editor = CKEDITOR.replace('post_content', {
  toolbar: 'Thread',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#post_content').data('imageUploadUrl'),
  height: 300
});

editor.on('change', () => {
  $('#post_content').val(editor.getData());
  validator.form();
});

editor.on('blur', () => {
  $('#post_content').val(editor.getData());
  validator.form();
});

