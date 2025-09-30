import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import EsWebUploader from 'common/es-webuploader';
import Captcha from 'app/common/captcha';
let captcha = new Captcha({drag:{limitType:"thread", bar:'#drag-btn', target: '.js-jigsaw'}});

let $form = $('#thread-form');

var captchaProp = null;
if($("input[name=enable_anti_brush_captcha]").val() == 1){
  captchaProp = {
    captchaClass: captcha,
    // isShowCaptcha: $(captcha.params.maskClass).length ? 1 : 0,
    isShowCaptcha: 1
  };
}
let $btn = $form.find(".js-btn-thread-save");
let validator = $form.validate({
  captcha: captchaProp,
  rules: {
    'title': {
      required: true,
      trim: true,
    },
    'content': {
      required: true,
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
  }
});

let editor = CKEDITOR.replace('thread_content', {
  toolbar: 'Thread',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#thread_content').data('imageUploadUrl')
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
editor.on('change', () => {
  $('#thread_content').val(editor.getData());
  validator.form();
});
editor.on('blur', () => {
  $('#thread_content').val(editor.getData());
  validator.form();
});

let threadType = $form.find('[name="type"]').val();

if (threadType == 'event') {
  $form.find('[name="maxUsers"]').rules('add', {
    positive_integer: true
  });
  $form.find('[name="location"]').rules('add', {
    visible_character: true
  });
  $form.find('[name="startTime"]').rules('add', {
    required: true,
    DateAndTime: true
  });

  $form.find('[name="startTime"]').datetimepicker({
    language: document.documentElement.lang,
    autoclose: true,
    format: 'yyyy-mm-dd hh:ii',
    minView: 'hour',
  }).on('hide', function (ev) {
    $form.validate('[name=startTime]');
  });
  $form.find('[name="startTime"]').datetimepicker('setStartDate', new Date);

  new EsWebUploader({
    element: '#js-activity-uploader',
    onUploadSuccess: function(file, response) {
      $form.find('[name=actvityPicture]').val(response.url);
      cd.message({type: 'success', message: Translator.trans('site.upload_success_hint')});
    }
  });
}


new AttachmentActions($form);

