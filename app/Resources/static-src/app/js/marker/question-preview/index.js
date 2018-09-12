$('.js-show-resolve').on('click', function() {
  let $this = $(this);
  $('.js-topic-content').toggleClass('hidden');
  $('.js-topic-resolve').toggleClass('hidden').is(':visible') ? $this.text(Translator.trans('course.question_marker.back_to_questions')) : $this.text(Translator.trans('course.question_marker.view_analysis'));
});