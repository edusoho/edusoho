cd.select({
  el: '#select-single',
  type: 'single'
}).on('change', (value, text) => {
  console.log('single', value, text);
});