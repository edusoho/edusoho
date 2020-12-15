import notify from 'common/notify';

let $form = $('#video-play-form');
$('.js-setting-submit').click(function () {
  $.post($form.data('url'), $form.serialize())
    .success(function(response) {
      notify('success', Translator.trans('site.save_success_hint'));
    }).fail(function (xhr, status, error){
      notify('danger', xhr.responseJSON.error.message);
    });
});

$('input:radio[name="multiple_learn_enable"]').click(function(){
  let value = $('input:radio[name="multiple_learn_enable"]:checked').val();
  if(value == 0) {
    $('.js-effect-show').removeClass('hidden');
    $('.js-allow-tips').addClass('hidden');
    $('.js-forbidden-tips').removeClass('hidden');
  } else {
    $('.js-effect-show').addClass('hidden');
    $('.js-allow-tips').removeClass('hidden');
    $('.js-forbidden-tips').addClass('hidden');
  }
});