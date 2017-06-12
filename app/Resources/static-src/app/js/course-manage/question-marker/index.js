$('.js-switch-lesson').on('change', function() {
  reload();
});

$('[data-val="correct_dot"] i').on('click',function(){
  let self = $(this),
    rulesVal = self.data('val');
  self.addClass('active').siblings().removeClass('active');
  reload(rulesVal)
});

function reload(value){
  let url = window.location.origin+window.location.pathname+'?',
    taskId = $('.js-switch-lesson').val() || '',
    val = value || '';
  window.location = url+'taskId='+taskId+ '&order=' +val;
}