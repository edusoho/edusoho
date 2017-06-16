import notify from 'common/notify';

export const publishCourse = () => {
  $('body').on('click', '.course-publish-btn', function(evt) {
    if (!confirm(Translator.trans('是否确定发布该教学计划？'))) {
      return;
    }
    $.post($(evt.target).data('url'), function(data) {
      if (data.success) {
        notify('success', '教学计划发布成功');
        location.reload();
      } else {
        notify('danger','教学计划发布失败：' + data.message, {delay:5000});
      }
    });
  });
}

publishCourse();