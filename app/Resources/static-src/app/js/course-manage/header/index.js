import notify from 'common/notify';

export const publishCourse = () => {
  $('body').on('click', '.course-publish-btn', function(evt) {
    if (!confirm(Translator.trans(Translator.trans('course.manage.publish_hint')))) {
      return;
    }
    $.post($(evt.target).data('url'), function(data) {
      if (data.success) {
        notify('success', Translator.trans('course.manage.publish_success_hint'));
        location.reload();
      } else {
        notify('danger',Translator.trans('course.manage.publish_fail_hint') + ':'+ data.message, {delay:5000});
      }
    });
  });
};

publishCourse();
