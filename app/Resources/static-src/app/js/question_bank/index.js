$('#questionBankModal').modal('show');

$('.question-bank-wrap').each(function(){
  const $content = $(this).find('.question-bank-text');
  const str = $content.text();
  if ($content.height() > $(this).height()) {
    $(this).addClass('question-bank-eslipise');
  }
});

$('.js-model-close').click(()=>{
  $('#questionBankModal').modal('hide');
});