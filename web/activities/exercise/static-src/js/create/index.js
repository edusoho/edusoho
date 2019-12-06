import QuestionBankSelectLink from 'app/js/question-bank/common/select-link.js';
import Exercise from './exercise';

new Exercise($('#step2-form'));

checkQuestionNum();

$('#questionBankSelect').change(function () {
  checkQuestionNum();
});

$('[name="range[categoryId]"]').change(function () {
  checkQuestionNum();
});

$('[name="difficulty"]').change(function () {
  checkQuestionNum();
});

function checkQuestionNum() {
  let url = $('#questionBankSelect').data('checkNumUrl');
  let bankId = $('#questionBankSelect').val();
  let categoryIds = $('[name="range[categoryId]"]').val();
  let difficulty = $('[name="difficulty"]').val();

  $.post(url, { bankId: bankId, categoryIds: categoryIds, difficulty: difficulty }, function (data) {
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

let treeObject = new window.$.CheckTreeviewInput({
  $elem: $('#questionCategorySelect'),
  disableNodeCheck: true,
  saveColumn: 'id',
  transportChildren: true,
});

new QuestionBankSelectLink($('#questionBankSelect'), $('#questionCategorySelect'), treeObject);