let $form = $('#classroom-set-form');
let validator = $form.validate({
  currentDom:'#classroom-save',
  rules: {
    price:{
      required: true,
      currency: true
    },
  }
});

$('#classroom-save').click(()=>{
  validator.form();
});

$('#price').on('input',function(){
  var price = $('#price').val();
  var rate = $('#coinPrice').data('rate');
  var name = $('#coinPrice').data('name');
  $('#coinPrice').text(Translator.trans('classroom.manage.coin_price_hint', {'coinPrice': price*rate, 'coinName': name}));

});