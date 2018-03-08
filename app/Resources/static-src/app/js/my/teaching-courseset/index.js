cd.select({
  el: '#select-single',
  type: 'single'
}).on('change', (value, text) => {
  console.log('single', value, text);
  // if (value) {
  //   $('.js-course-set-item').not('.js-course-set-type-' + value).hide();
  //   $('.js-course-set-type-' + value).show();
  // } else {
  //   $('.js-course-set-item').show();
  // }

});