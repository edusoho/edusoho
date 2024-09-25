$('[data-toggle=switch]').on('click', event => {
  const $target = $(event.currentTarget);
  const $parent = $target.parent();
  if ($parent.hasClass('checked')) {
    $parent.removeClass('checked');
  } else {
    $parent.addClass('checked');
  }
  const $value = $target.next();
  const value = $value.val() == 1 ? 0 : 1;
  $value.val(value);
  const $form = $('#form');
  $.post($form.data('url'), $form.serialize(), resp => {
  });
});
