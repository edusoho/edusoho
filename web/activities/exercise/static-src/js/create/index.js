import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';
import Exercise from './exercise';

new Exercise($('#step2-form'));
new SelectLinkage($('[name="range[courseId]"]'), $('[name="range[lessonId]"]'));

checkQuestionNum();

$('[name="range[courseId]"]').change(function () {
  checkQuestionNum();
});

$('[name="range[lessonId]"]').change(function () {
  checkQuestionNum();
});

$('[name="difficulty"]').change(function () {
  checkQuestionNum();
});

function checkQuestionNum() {
  let url = $('[name="range[courseId]"]').data('checkNumUrl');
  let courseId = $('[name="range[courseId]"]').val();
  let lessonId = $('[name="range[lessonId]"]').val();
  let difficulty = $('[name="difficulty"]').val();

  $.post(url, { courseId: courseId, lessonId: lessonId, difficulty: difficulty }, function (data) {
    $('[role="questionNum"]').text(0);

    $.each(data, function (i, n) {
      $('[type=\'' + i + '\']').text(n.questionNum);
    });
  });
}