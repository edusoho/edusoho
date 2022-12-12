import Order from './order';

new Order({
  element: '#order-create-form'
});

if($('.js-agreement-check').length) {
  
  const $purchaseContent = $('.js-purchase-content');

  const judgeDistance = () => {
    const $purchaseContent = $('.js-purchase-content');
    var scrollTop = $purchaseContent[0].scrollTop;
    var scrollHeight = $purchaseContent[0].scrollHeight;
    var clientHeight = $purchaseContent[0].clientHeight;

    return scrollTop + clientHeight + 1 >= scrollHeight;
  };

  $('#check-modal').on('shown.bs.modal', () => {
    if (judgeDistance()) {
      $('.js-purchase-btn').removeClass('disabled');
    }
  });

  $purchaseContent.scroll(() => {
    if (judgeDistance()) {
      $('.js-purchase-btn').removeClass('disabled');
    }
  });
  
  $('#order-create-btn').attr('disabled',true);

  if ($('.js-agreement-check').data('type') == 'eject') {
    $('#check-modal').modal('show');
  }

  $('.js-preview-modal').on('click',function (){
    $('#check-modal').modal('show');
  });

  $('.js-agreement-check').on('click',function (){
    if($('.js-agreement-check').is(':checked')){
      $('#order-create-btn').attr('disabled',false);
    }else{
      $('#order-create-btn').attr('disabled',true);
    }
  });

  $('.js-purchase-btn').on('click',function (){
    if($(this).hasClass('disabled')){
      return;
    }
    if(!$('.js-agreement-check').is(':checked')) {
      $('.js-agreement-check').click();
    }
    $('#check-modal').modal('hide');
    $('#order-create-btn').attr('disabled',false);
  });
}



