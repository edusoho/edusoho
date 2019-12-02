import QuestionBankSelectLink from 'app/js/question-bank/common/select-link.js';
import Exercise from './exercise';

new Exercise($('#step2-form'));
new QuestionBankSelectLink($('#questionBankSelect'), $('#questionCategorySelect'));

checkQuestionNum();

$('#questionBankSelect').change(function () {
  checkQuestionNum();
});

$('#questionCategorySelect').change(function () {
  checkQuestionNum();
});

$('[name="difficulty"]').change(function () {
  checkQuestionNum();
});

function checkQuestionNum() {
  let url = $('#questionBankSelect').data('checkNumUrl');
  let bankId = $('#questionBankSelect').val();
  let categoryId = $('#questionCategorySelect').val();
  let difficulty = $('[name="difficulty"]').val();

  $.post(url, { bankId: bankId, categoryId: categoryId, difficulty: difficulty }, function (data) {
    $('[role="questionNum"]').text(0);

    $.each(data, function (i, n) {
      $('[type=\'' + i + '\']').text(n.questionNum);
    });
  });
}

$('#questionBankSelect').select2({
  treeview: true,
  dropdownAutoWidth: true,
  treeviewInitState: 'collapsed',
  placeholderOption: 'first'
});

$('#questionCategorySelect').select2({
  treeview: true,
  dropdownAutoWidth: true,
  treeviewInitState: 'collapsed',
  placeholderOption: 'first'
});