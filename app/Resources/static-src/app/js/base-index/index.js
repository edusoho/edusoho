window.addEventListener('load', (event) => {
  $('[data-toggle="tooltip"]').tooltip({
  html: true,
  });
  document.getElementsByClassName("cd-notification")[0].css('overflow', 'none')
});