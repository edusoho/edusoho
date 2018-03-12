import notify from 'common/notify';

export const hiddenUnpublishTask = () => {
  cd.onoff({
    el: '.js-switch'
  }).on('change', (value) => {
    let $ele = $('.js-switch');
    const url = $ele.data('url');
    const status = $ele.parent().hasClass('checked') ? 0 : 1;
    
    $.post(url, { status: status })
    .success((response) => {
      notify('success', Translator.trans('site.save_success_hint'));
    })
    .error((response) => {
      notify('error', response.responseJSON.error.message);
    })
  })
}

export const addLesson = () => {
  $('.js-lesson-create-btn').click(function(){
    const url = $(this).data('url');

    $.get(url, {})
    .success(function(response) {
      $('#modal').html('');
      $('#modal').append(response.html);
      $('#modal').modal({'backdrop':'static','show':true});
    })
    .error(function(response){
      notify('error', Translator.trans(response.responseJSON.error.message));
    })
  })
}