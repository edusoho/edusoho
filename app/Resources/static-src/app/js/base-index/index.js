window.addEventListener('load', (event) => {
  $('[data-toggle="tooltip"]').tooltip({
  html: true,
  });
  $(".cd-notification")[0].css('overflow', 'none');
});