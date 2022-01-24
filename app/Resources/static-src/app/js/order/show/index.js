import Order from './order';

new Order({
  element: '#order-create-form'
});

if($('.js-agreement-check').length){
  $('#order-create-btn').attr("disabled",true);
  if($('.js-agreement-check').data('type') == 'eject'){
    $('#check-modal').modal('show');
  }
  $('.js-preview-modal').on('click',function (){
    $('#check-modal').modal('show');
  });

  $('.js-agreement-check').on('click',function (){
    if($('.js-agreement-check').is(':checked')){
      $('#order-create-btn').attr("disabled",false);
    }else{
      $('#order-create-btn').attr("disabled",true);
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
    $('#order-create-btn').attr("disabled",false);
  });

  $('.js-purchase-content').scroll(function(event) {
    var scrollTop = event.currentTarget.scrollTop;
    var scrollHeight = event.currentTarget.scrollHeight;
    var clientHeight =event.currentTarget.clientHeight;
    if(scrollTop+clientHeight >=scrollHeight) {
      $('.js-purchase-btn').removeClass('disabled');
    }
  });
}



