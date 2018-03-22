export const publishCourseSet = () => {
  $('body').on('click', '.course-publish-btn', function(evt) {
    if (!confirm(Translator.trans('course_set.manage.publish_hint'))) {
      return;
    }
    $.post($(evt.target).data('url'), function(data) {
      if (data.success) {
        cd.message({ type: 'success', message: Translator.trans('course_set.manage.publish_success_hint') });
        location.reload();
      } else {
        cd.message({ type: 'danger', message: Translator.trans('course_set.manage.publish_fail_hint') + ':' + data.message, delay: '5000' });
      }
    });
  });
};

publishCourseSet();
