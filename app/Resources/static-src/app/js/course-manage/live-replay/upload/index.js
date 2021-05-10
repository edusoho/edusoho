import FileChooser from 'app/js/file-chooser/file-choose';

const $fileId = $('#material-file-chooser').find('[name=fileId]');

function initFileChooser() {
  const fileChooser = new FileChooser();
  fileChooser.on('select', file => {
    $fileId.val(file.id);
    FileChooser.closeUI();
    $('.jq-validate-error').remove();
  });
}

if ($(".file-chooser-main").hasClass('hidden')) {
  $('.js-choose-trigger').click(event => {
    initFileChooser();
    FileChooser.openUI();
    $fileId.val('');
  });
} else {
  initFileChooser();
}


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

$("[data-toggle='popover']").popover();
