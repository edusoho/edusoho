import { saveRedmineLoading, saveRedmineSuccess } from '../save-redmine';
let heigth = ($('.js-sidebar-pane').height() - 175);
let $content = $('#note-content-field');
let editor = CKEDITOR.replace('note-content-field', {
  toolbar: 'Simple',
  filebrowserImageUploadUrl: $content.data('imageUploadUrl'),
  allowedContent: true,
  height: heigth < 300 ? 200 : heigth,
});

editor.on('change', () => {
  $content.val(editor.getData());
});

$('#note-save-btn').click(function (event) {
  let $btn = $(this);
  $btn.attr('disabled', 'disabled');
  event.preventDefault();
  saveNote();
});

setInterval(saveNote,10000);

function saveNote($btn = null) {
  if(!$content.val()) {
    return;
  }
  let $form = $('#task-note-plugin-form');
  let data = $form.serializeArray();
  saveRedmineLoading();
  $.post($form.attr('action'), data)
    .then((response) => {
      saveRedmineSuccess();
      if($btn) {
        $btn.removeAttr('disabled');
      }
    });
}
