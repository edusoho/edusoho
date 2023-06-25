window.addEventListener('load', (event) => {
  setTimeout(() => {
    $('[data-toggle="tooltip"]').tooltip({
      html: true,
      });
  }, 1500)  
});