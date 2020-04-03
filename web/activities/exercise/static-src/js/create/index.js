import QuestionBankSelectLink from 'app/js/question-bank/common/select-link.js';
import { htmlEscape } from 'app/common/unit.js';
import Exercise from './exercise';

new Exercise($('#step2-form'));

checkQuestionNum();

$('#questionBankSelect').change(function () {
  checkQuestionNum();
});

$('[name="range[categoryIds]"]').change(function () {
  checkQuestionNum();
});

$('[name="difficulty"]').change(function () {
  checkQuestionNum();
});

function checkQuestionNum() {
  let url = $('#questionBankSelect').data('checkNumUrl');
  let bankId = $('#questionBankSelect').val();
  let categoryIds = $('[name="range[categoryIds]"]').val();
  let difficulty = $('[name="difficulty"]').val();

  $.post(url, { bankId: bankId, categoryIds: categoryIds, difficulty: difficulty }, function (data) {
    $('[role="questionNum"]').text(0);

    $.each(data, function (i, n) {
      $('[type=\'' + i + '\']').text(n.itemNum);
    });
  });
}

$('#questionBankSelect').select2({
  treeview: true,
  dropdownAutoWidth: true,
  treeviewInitState: 'collapsed',
  placeholderOption: 'first',
  formatResult: function(item) {
    let text = htmlEscape(item.text);
    if (!item.id) {
      return text;
    }
    return `<div class="select2-result-text"><span class="select2-match"></span><span><i class="es-icon es-icon-tiku"></i>${text}</span></div>`;
  },
  dropdownCss: {
    width: ''
  },
});

let treeObject = new window.$.CheckTreeviewInput({
  $elem: $('#questionCategorySelect'),
  disableNodeCheck: true,
  saveColumn: 'id',
  transportChildren: true,
});

new QuestionBankSelectLink($('#questionBankSelect'), $('#questionCategorySelect'), treeObject);

$('.task-iframe-body').find('.select2-results').css('max-height', '180px');