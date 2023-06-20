$('.js-user-mobile').on('click', '.js-toggle', event => {
  const $target = $(event.currentTarget).parent('.js-user-mobile');
  $.post($target.data('url'), { encryptedMobile: $target.data('encryptedMobile') }).then(res => {
    if (res.mobile) {
      $target.find('.js-mobile').text(res.mobile);
      $target.find('.js-toggle').addClass('hidden');
    }
  });
});
$('[data-toggle="tooltip"]').tooltip();
