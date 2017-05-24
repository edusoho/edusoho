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
  event.preventDefault();
  saveNote($btn);
});

setInterval(saveNote,30000);

function saveNote($btn = null) {
  if(!$.trim($content.val())) {
    return;
  }
  let $form = $('#task-note-plugin-form');
  let data = $form.serializeArray();
  saveRedmineLoading();
  $btn ? $btn.attr('disabled', 'disabled'): "";
  $.post($form.attr('action'), data)
    .then((response) => {
      saveRedmineSuccess();
      if ($btn) {
        $btn.removeAttr('disabled');
      }
    });
}
