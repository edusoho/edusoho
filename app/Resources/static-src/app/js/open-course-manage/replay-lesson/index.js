import notify from 'common/notify';

// 再点一次就报错
$('.js-generate-replay').on('click', (event) => {
  const $this = $(event.currentTarget);
  const message = Translator.trans('confirm.replay_lesson.message');
  const url = $this.data('url');
  if (!message) {
    return;
  }
  $.post(url, (html) => {
    if (html.error) {
      if (html.code == 10019) {
        notify('danger', Translator.trans('notify.not_record.message'));
      } else if (html.code == 1403) {
        notify('danger', Translator.trans('notify.no_replay_file.message'));
      } else {
        notify('danger', Translator.trans('notify.record_error.message'));
      }
    } else {
      let id = '#' + $(html).attr('id');
      $(id).replaceWith(html);
      notify('success', Translator.trans('notify.lesson_recorded.message'));
    }
  });
});

$(".js-tip-show").tooltip();


