let select = 1;
$('.ant-modal-body').on('click','.js-img-radio', function (){
  $('.ant-modal-body').find('.js-active').removeClass('ant-radio-checked');
  $(this).find('.js-active').addClass('ant-radio-checked');
  select = $(this).find('.js-input').val();
});

$('.js-submit-button').on('click',function (){
  $.post($(this).data('url'),{type:'recordReplay'}, function(data) {
    $('#record-replay').parent('.modal').modal('hide');
    window.open(data.url+'&recordLayout='+select);
  });
});