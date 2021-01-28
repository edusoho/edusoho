import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import EsWebUploader from 'common/es-webuploader';

let $form = $('#thread-form');
let validator = $form.validate({
  rules: {
    'title': {
      required: true,
      trim: true,
    },
    'content': {
      required: true,
    }
  }
});

let editor = CKEDITOR.replace('thread_content', {
  toolbar: 'Thread',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#thread_content').data('imageUploadUrl')
});

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

