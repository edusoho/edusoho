$('.js-choice-course').change(() => {
  location.href = $('.js-choice-course option:selected').val();
});