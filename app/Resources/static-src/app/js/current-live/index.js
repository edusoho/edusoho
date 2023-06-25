console.log(1)
window.addEventListener('load', (event) => {
  console.log(2)
  $('[data-toggle="tooltip"]').tooltip({
  html: true,
  });
  console.log(3)
});
console.log(4)