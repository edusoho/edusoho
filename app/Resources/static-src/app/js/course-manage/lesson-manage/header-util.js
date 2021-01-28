export const hiddenUnpublishTask = () => {
  cd.onoff({
    el: '.js-switch'
  }).on('change', (value) => {
    let $ele = $('.js-switch');
    let url = $ele.data('url');
    let status = $ele.parent().hasClass('checked') ? 1 : 0;
    let statusStr = $ele.parent().hasClass('checked') ? 'on' : 'off';

    cd.confirm({
      title: Translator.trans('confirm.oper.tip'),
      content: Translator.trans('confirm.lesson.hidden.tip.' + statusStr),
      okText: Translator.trans('site.yes'),
      cancelText: Translator.trans('site.no'),
    }).on('ok', () => {
      $.post(url, { 'status': status })
        .success((response) => {
          cd.message({ type: 'success', message: Translator.trans('site.save_success_hint') });
          location.reload();
        })
        .error((response) => {
          cd.message({ type: 'danger', message: response.responseJSON.error.message });
        });
    }).on('cancel', ($modal, modal) => {
      $ele[0].checked = !$ele[0].checked;
      $ele.parent().toggleClass('checked');
    });
  });
};

export const addLesson = () => {
  $('body').on('click', '.js-lesson-create-btn', (event) => {
    const url = $(event.currentTarget).data('url');
    $.get(url, {})
      .success((response) => {
        $('#modal').html('');
        $('#modal').append(response.html);
        $('#modal').modal({ 'backdrop': 'static', 'show': true });
      })
      .error((response) => {
        cd.message({ type: 'danger', message: Translator.trans(response.responseJSON.error.message) });
      });
  });
};