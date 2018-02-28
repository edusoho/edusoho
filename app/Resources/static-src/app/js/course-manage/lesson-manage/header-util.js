import notify from 'common/notify';

export const hiddenUnpublishTask = () => {
  cd.onoff({
    el: '.js-switch'
  }).on('change', (value) => {
    let $ele = $('#isShowPublish');
    const url = $ele.data('url');
    const status = $ele.parent().hasClass('checked') ? 1 : 0;
    $.post(url, { status: status })
    .success((response) => {
      notify('success', Translator.trans('site.save_success_hint'));
    })
    .error((response) => {
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