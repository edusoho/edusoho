$('.js-switch-lesson').on('change', function() {
  reload('');
});

$('.js-sort-btn').on('click', function(){
  let order = 'desc';
  let $activeIcon = $(this).find('.es-icon.active ');
  if ($activeIcon.length > 0) {
    $activeIcon.removeClass('active').siblings().addClass('active');
    order = $activeIcon.siblings().data('val');
  } else {
    $(this).find('[data-val="desc"]').addClass('active').siblings().removeClass('active');
  }
  reload(order);
});

function reload(order){
  let url = window.location.origin+window.location.pathname+'?',
    taskId = $('.js-switch-lesson').val();
  window.location = url+'taskId='+taskId+ '&order=' +order;
}