export let questionSubjectiveRemask = ($element) => {
  let hasSubjective = false;
   let html = '';
  let $subjectiveRemask = $(".js-subjective-remask");

  $element.find('tbody tr').each(function() {
    let type = $(this).data('type');
    console.log(type);
    if (type == 'essay') {
      hasSubjective = true;
    }
  });
  console.log(hasSubjective);
  if(hasSubjective) {
    $subjectiveRemask.html('');
    return;
  }

  console.log($subjectiveRemask);

  if($subjectiveRemask.data('type') == 'homework') {
    html = '这是一份纯客观题的作业，正确率达到为' +
    '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini ph5 text-center correctPercent1" value="60" />％合格，'+
    '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini ph5 text-center correctPercent2" value="80" />％良好，'+
    '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini ph5 text-center correctPercent3" value="100" />％优秀';
  }
  else {
    html = '这是一份纯客观题的试卷, 达到'+
    '<input type="text" name="passedScore" class="form-control width-150 mhs" value="0" data-score-total="0" />'+
    '分（含）可以自动审阅通过考试。'
  }
  $subjectiveRemask.html(html).removeClass('hidden');
}