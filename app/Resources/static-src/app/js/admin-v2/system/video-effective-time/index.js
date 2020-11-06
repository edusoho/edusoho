import notify from 'common/notify';

var $form = $('#video-effective-time-form');
$('.js-setting-submit').click(function () {
  if (!confirm(Translator.trans('admin_v2.video_effective_time_setting.cancel.hint'))) {
    return;
  }
  $.post($form.data('url'), $form.serialize())
    .success(function(response) {
      notify('success', Translator.trans('site.save_success_hint'));
    }).fail(function (xhr, status, error){
      notify('danger', xhr.responseJSON.error.message);
    });
});