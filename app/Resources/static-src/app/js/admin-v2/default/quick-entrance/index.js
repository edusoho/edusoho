$('.js-entrance-list').on('click', '.js-function-choose', (event) => {
  const $target = $(event.currentTarget);

  if (!$target.hasClass('active') && $('.js-function-choose.active').length >= 7) {
    cd.message({type: 'danger', message: Translator.trans('admin_v2.homepage.quick_entrance.hint')});
    return;
  }
  $target.toggleClass('active');
});

$('.js-save-btn').on('click', (event) => {
  if ($('.js-function-choose.active').length > 7) {
    cd.message({type: 'danger', message: Translator.trans('admin_v2.homepage.quick_entrance.hint')});
    return;
  }
  const $target = $(event.target);

  $target.button('loading');
  const entranceCodes = [];

  $('.js-function-choose.active').each((index, item) => {
    entranceCodes.push($(item).data('code'));
  });

  $.post($('#quick-entrance-form').attr('action'), {
    '_csrf_token': $('meta[name=csrf-token]').attr('content'),
    data: entranceCodes
  }, function (quickEntrances) {
    $target.button('reset');
    if (quickEntrances) {
      $('.quick-entrance').html(quickEntrances);
      $('#modal').modal('hide');
    }
  });

});