$('body').on('click', '.showQuestion', function () {
  $(this).parent().find('.panel').toggle();
});

$('body').on('click', '.unfavorite-btn', function () {
  $btn = $(this);

  $.post($(this).data('url'), function () {
    $btn.parents('tr').hide();
  });
});