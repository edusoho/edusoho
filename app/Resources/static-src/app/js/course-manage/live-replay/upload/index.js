import FileChooser from 'app/js/file-chooser/file-choose';

const fileChooser = new FileChooser();
const $fileId = $('#material-file-chooser').find('[name=fileId]');
fileChooser.on('select', file => {
  $fileId.val(file.id);
  FileChooser.closeUI();
  $('.jq-validate-error').remove();
});

$('.js-choose-trigger').click(event => {
  FileChooser.openUI();
  $fileId.val('');
});

const $form = $('#replay-material-form');

$form.validate({
  rules: {
    fileId: {
      required: true
    }
  },
  messages: {
    fileId: Translator.trans('course.manage.live_replay_upload_error_hint')
  }
});