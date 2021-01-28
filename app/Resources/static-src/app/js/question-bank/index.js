$(document).ready(()=>{
  if(!localStorage.getItem('question-bank-hasvisit')){
    $('#questionBankModal').modal('show');
    localStorage.setItem('question-bank-hasvisit', true);
  }
});

$('.js-model-close').click(()=>{
  $('#questionBankModal').modal('hide');
});

$('.question-bank-wrap').each(function(){
  const $content = $(this).find('.question-bank-text');
  const str = $content.text();
  if ($content.height() > $(this).height()) {
    $(this).addClass('question-bank-eslipise');
  }
});

$('[name="categoryId"]').select2({
  treeview: true,
  dropdownAutoWidth: true,
  treeviewInitState: 'collapsed',
  placeholderOption: 'first'
});

