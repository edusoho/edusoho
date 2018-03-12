cd.select({
  el: '#select-single',
  type: 'single'
}).on('change', (value, text) => {
  window.location.href = $('.js-select-options li.checked').data('url');
});