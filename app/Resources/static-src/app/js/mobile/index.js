$('.js-mobile-item').waypoint(function() {
  $(this).addClass('active');
}, { offset:500 });

$('.js-btn-mobile').click((event) => {
  const $this = $(event.currentTarget);
  const $offsetTarget = $($this.attr('data-url'));
  $('html,body').animate({
    scrollTop: $offsetTarget.offset().top + 50
  }, 300);
});
