import notify from 'common/notify';

var $form = $('#video-play-form');
$('.js-setting-submit').click(function () {
  $.post($form.data('url'), $form.serialize())
    .success(function(response) {
      notify('success', Translator.trans('site.save_success_hint'));
    }).fail(function (xhr, status, error){
      notify('danger', xhr.responseJSON.error.message);
    });
});

$('input:radio[name="different_video_multiple"]').click(function(){
  isEffectShow();
});

$('input:radio[name="same_video_multiple"]').click(function(){
  isEffectShow();
});

function isEffectShow() {
  var value = $('input:radio[name="different_video_multiple"]:checked').val();
  var open = $('input:radio[name="same_video_multiple"]:checked').val();
  if(value == 0 || open ==0) {
    $('.js-effect-show').removeClass('hidden');
  } else {
    $('.js-effect-show').addClass('hidden');
  }
}