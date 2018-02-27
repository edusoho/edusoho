import notify from 'common/notify';

export const hiddenUnpublishTask = () => {
  $('input[name="isShowPublish"]').change(function(){
    const url = $(this).data('url');
    const status = $(this).parent('.js-switch').hasClass('checked') ? 0 : 1;

    $.post(url, {status:status})
    .success(function(response) {
      notify('success', Translator.trans('site.save_success_hint'));
    })
    .error(function(response){
      notify('error', response.error.message);
    })
  })
}

export const addLesson = () => {
  $('.js-lesson-create-btn').click(function(){
    const url = $(this).data('url');

    $.post(url, {})
    .success(function(response) {
      if (response.code) {
        $('#modal').html('');
        $('#modal').append(response.html);
        $('#modal').modal({'backdrop':'static','show':true});
      } else {
        notify('danger', Translator.trans(response.message));
      }
    })
    .error(function(response){
      notify('error', response.error.message);
    })
  })
}