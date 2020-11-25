import notify from 'common/notify';

let $form = $('#video-effective-time-form');
$('.js-setting-submit').click(function () {
  cd.confirm({
    content: '<div style="text-align:center;">' + Translator.trans('admin_v2.video_effective_time_setting.cancel.hint') + '</div>',
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.cancel'),
    className: '',
  }).on('ok', () => {
    $.post($form.data('url'), $form.serialize())
      .success(function(response) {
        notify('success', Translator.trans('site.save_success_hint'));
      }).fail(function (xhr, status, error){
        notify('danger', xhr.responseJSON.error.message);
      });
  }).on('cancel', () => {
    window.location.reload();
  });
});

$('input:radio[name="play_rule"]').click(function(){
  let value = $('input:radio[name="play_rule"]:checked').val();
  if(value == 'auto_pause') {
    $('.js-play-role-help').html(Translator.trans('admin_v2.video_effective_time_setting.play_rule.auto_pause.help_block'));
  } else {
    $('.js-play-role-help').html(Translator.trans('admin_v2.video_effective_time_setting.play_rule.no_action.help_block'));
  }
});

$('input:radio[name="statistical_dimension"]').click(function(){
  let value = $('input:radio[name="statistical_dimension"]:checked').val();
  if(value == 'playing') {
    $('.js-statistical-dimension-help').html(Translator.trans('admin_v2.video_effective_time_setting.statistical_dimension.playing.help_block'));
  } else {
    $('.js-statistical-dimension-help').html(Translator.trans('admin_v2.video_effective_time_setting.statistical_dimension.page.help_block'));
  }
});