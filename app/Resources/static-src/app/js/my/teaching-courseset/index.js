cd.select({
  el: '#select-single',
  type: 'single'
}).on('change', (value, text) => {
  if ($('.select-options li').hasClass('checked')) {
    window.location.href = $('.select-options li.checked').data('url');
  }
});