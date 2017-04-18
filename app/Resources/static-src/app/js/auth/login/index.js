let $form  = $('#login-form');
let validator = $form.validate({
  rules: {
    _username:{
      required: true,
    },
    _password: {
      required: true,
    }
  }
})


$('.js-btn-login').click((event)=>{
  if(validator.form()) {
    $(event.currentTarget).button('loadding');
     $form.submit();
  }
})

$('.receive-modal').click();