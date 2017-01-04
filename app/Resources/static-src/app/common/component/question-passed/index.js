export let passedDivShow = ($element) => {
  console.log('ok');
  let hasEssay = false;
  $element.find('tbody tr').each(function() {
    console.log($(this));
    if ($(this).data('type') == 'essay' || $(this).data('type') == 'material') {
      hasEssay = true;
    }
  });

  let $passedScoreDiv = $(".js-passedScoreDiv");
  let html = '';
  console.log(hasEssay);
  if(hasEssay) {
    $passedScoreDiv.html('');
    return;
  }

  if($passedScoreDiv.data('type') == 'homework') {
    html = '这是一份纯客观题的作业，正确率达到为' +
    '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini correctPercent1" value="60" />％合格，'+
    '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini correctPercent2" value="80" />％良好，'+
    '<input type="text" name="passedCondition[]" class="form-control width-input width-input-mini correctPercent3" value="100" />％优秀';
  }
  else {
    html = '这是一份纯客观题的试卷, 达到'+
    '<input type="text" name="passedScore" class="form-control width-150 mhs" value="0" data-score-total="0" />'+
    '分（含）可以自动审阅通过考试。'
  }
  $passedScoreDiv.html(html).removeClass('hidden');
}