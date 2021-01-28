$('.js-course-publish-btn').click((event) => {
  const $target = $(event.target);
  cd.confirm({
    title: Translator.trans('course_set.manage.publish_title'),
    content: Translator.trans('course_set.manage.publish_hint'),
    okText: Translator.trans('site.confirm'),
    cancelText: Translator.trans('site.close')
  }).on('ok', () => {
    $.post($target.data('url'), (response) => {
      if (response.result) {
        cd.message({ type: 'success', message: Translator.trans('course_set.manage.publish_success_hint') });
        window.location.reload();
      } else {
        cd.message({ type: 'danger', message: response.message });
      }
    });
  });
});

