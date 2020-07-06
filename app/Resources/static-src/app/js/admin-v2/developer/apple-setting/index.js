import notify from 'common/notify';

let $form = $('#apple-form');
$('.js-setting-submit').click(function () {
  $.post($form.data('url'), $form.serialize())
    .success(function(response) {
      notify('success', Translator.trans('site.save_success_hint'));
    }).fail(function (xhr, status, error){
      notify('danger', xhr.responseJSON.error.message);
    });
});