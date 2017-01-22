import FileChooser from 'app/js/file-chooser/file-choose';

const fileChooser = new FileChooser();
const $fileId = $('#material-file-chooser').find('[name=fileId]');
fileChooser.on('select', file => {
  $fileId.val(file.id);
  FileChooser.closeUI();
});

$('.js-choose-trigger').click(event => {
  FileChooser.openUI();
  $fileId.val('');
});