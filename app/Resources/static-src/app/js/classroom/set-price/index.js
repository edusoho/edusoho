let $form = $('#classroom-set-form');
let validator = $form.validate({
  rule: {
    price:'required',
  }
})

$('#classroom-save').click(()=>{
  validator.form();
})

$("#price").on('input',function(){
    var price = $("#price").val();
    var rate = $("#coinPrice").data('rate');
    var name = $("#coinPrice").data('name');
    $("#coinPrice").text(Translator.trans('相当于')+price*rate+name);

});