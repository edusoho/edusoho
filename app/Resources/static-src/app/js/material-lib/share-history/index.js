// import '../share-form';
import notify from 'common/notify';
const $table = $('#share-history-table');
const $container = $('#share-history-table-container');


// tab 切换
$('.js-share-tab').on('click', function() {
  let $this = $(this);

  // if ($this.hasClass('active')) {
  //   return;
  // }

  $.get($this.data('url'), function(html) {
    $table.html(html);
  });
  
  $this.parent().addClass('active')
    .siblings().removeClass('active');
});

// 取消分享
$table.on('click', '.cancel-share-btn', function(e) {
  var $btn = $(e.currentTarget);
  let $this = $(this);

  $.post($this.data('url'), {
    targetUserId: $this.attr('targetUserId') 
    }, function(response) {
    $btn.closest('.share-history-record').remove();
    notify('success', Translator.trans('material.cancel_share.tips'));
  }, 'json');
  
});


$('.modal').off('click.modal-pagination');
$table.on('click', '.pagination li', function() {
  let $this = $(this);
  let url = $this.data('url');
  
  $.get(url, function(html) {
    $container.html(html);
  });
});