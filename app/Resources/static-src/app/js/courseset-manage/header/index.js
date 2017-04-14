import notify from 'common/notify';

export const publishCourseSet = () => {
  $('body').on('click', '.course-publish-btn', function(evt) {
    if (!confirm(Translator.trans('是否确定发布该课程？'))) {
      return;
    }
    $.post($(evt.target).data('url'), function(data) {
      if (data.success) {
        notify('success', Translator.trans('课程发布成功'));
        location.reload();
      } else {
        notify('danger',Translator.trans('课程发布失败') + ':' + data.message,5000);
      }
    });
  });
}

publishCourseSet();