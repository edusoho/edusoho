$('.js-app-download-close').on('click', function() {
  $(this).parent().slideUp(300);
  $('body').removeClass('has-app');
});