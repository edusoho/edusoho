$('.js-user-mobile').on('click', '.js-toggle', event => {
  let encrypted = $(event.currentTarget).parent('.js-user-mobile').data('encrypted');
  $.post('xxxx', { encrypted: encrypted }).then(res => {
    $('.js-user-mobile .js-mobile').text(res.mobile);
    $('.js-user-mobile .js-toggle').removeClass('xxx').addClass('xxx');
  });
});
