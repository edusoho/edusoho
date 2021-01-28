$('html').on('shown.bs.modal', '#functionModal', (e) => {
  $('html').css('overflow', 'hidden');
}).on('hidden.bs.modal', '#functionModal', (e) => {
  $('html').css('overflow', 'scroll');
});

$('.js-quick-entrance').on('click', '.js-function-choose', (event) => {
  const $target = $(event.currentTarget);

  if (!$target.hasClass('active') && $('.js-function-choose.active').length >= 7) {
    cd.message({type: 'warning', message: Translator.trans('admin_v2.homepage.quick_entrance.hint')});
    return;
  }

  $target.toggleClass('active');
});

$('.js-quick-entrance').on('click', '.js-save-btn', (event) => {
  if ($('.js-function-choose.active').length > 7) {
    cd.message({type: 'warning', message: Translator.trans('admin_v2.homepage.quick_entrance.hint')});
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
    $('#functionModal').modal('hide');
    $('.js-quick-entrance').html(quickEntrances);
  });

});
