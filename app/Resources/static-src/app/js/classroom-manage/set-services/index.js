$('.js-service-item').click(function(){
  let $this = $(this);
  let $input = $this.find('input');
  if($input.is(':checked')){
    $input.prop('checked',false);
    $this.removeClass('label-primary').addClass('label-default');
  }else {
    $input.prop('checked',true);
    $this.removeClass('label-default').addClass('label-primary');  
  }
});

