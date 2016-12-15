let $content = $('#note-content-field');

let editor = CKEDITOR.replace('note-content-field', {
  toolbar: 'Simple',
  filebrowserImageUploadUrl: $content.data('imageUploadUrl'),
  allowedContent: true,
  height: 300
});

editor.on( 'change', () => {
  $content.val(editor.getData());
});


$('#note-save-btn').click(function (event) {
  let $btn = $(this);
  $btn.attr('disabled', 'disabled');
  event.preventDefault();
  let $form = $('#task-note-plugin-form');
  let data = $form.serializeArray();
  $.post($form.attr('action'), data)
      .then((response) => {
        $btn.removeAttr('disabled');
      });
});