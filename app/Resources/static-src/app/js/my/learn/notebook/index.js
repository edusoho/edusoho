$('#notebook-list').on('click', '.media', function(){
  window.location.href = $(this).find('.notebook-go').attr('href');
});

const $notebook = $('#notebook');

$notebook.on('click', '.notebook-note-collapsed', function(){
  $(this).removeClass('notebook-note-collapsed');
});

$notebook.on('click', '.notebook-note-collapse-bar', function(){
  $(this).parents('.notebook-note').addClass('notebook-note-collapsed');
});

$notebook.on('click', '.notebook-note-delete', function(){
  let $btn = $(this);
  if (!confirm(Translator.trans('course.notebook.delete_hint'))) {
    return false;
  }

  $.post($btn.data('url'), function(){
    $btn.parents('.notebook-note').remove();
  });

});