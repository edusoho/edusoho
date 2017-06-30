$('.js-switch-lesson').on('change', function() {
  reload('');
});

$('.js-sort-btn i').on('click',function(){
  let self = $(this),
    order = self.data('val');
  self.addClass('active').siblings().removeClass('active');
  reload(order);
});

function reload(order){
  let url = window.location.origin+window.location.pathname+'?',
    taskId = $('.js-switch-lesson').val();
  window.location = url+'taskId='+taskId+ '&order=' +order;
}