$('.js-entrance-list').on('click', '.js-function-choose', (event) => {
  const $target = $(event.currentTarget);
  $target.toggleClass('active');
});

$('.js-save-btn').on('click', (event) => {
  if ($('.js-function-choose.active').length > 7) {
    cd.message({type: 'danger', message: '最多设置7个快捷入口位'});
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
    data: JSON.stringify(entranceCodes)
  }, function (quickEntrances) {
    $target.button('reset');
    if (quickEntrances) {
      $('.quick-entrance').html(quickEntrances);
      $('#modal').modal('hide');
    }
  });

});