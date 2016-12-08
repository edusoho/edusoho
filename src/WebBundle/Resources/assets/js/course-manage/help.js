import notify from 'common/notify';
import 'jquery-sortable';

export const closeCourse = () => {
  $('body').on('click', '.js-close-course', function (evt) {
    if (!confirm(Translator.trans('是否确定关闭该教学计划？'))) {
      return;
    }
    $.post($(evt.currentTarget).data('url'), function (data) {
      if (data.success) {
        notify('success', '关闭成功');
        location.reload();
      } else {
        notify('danger', '关闭失败：' + data.message);
      }
    });
  });
}

export const sortList = (element = '#sortable-list') => {
  let data = $(element).sortable("serialize").get();
  $.post($(element).data('sortUrl'), {ids: data}, (response) => {
    if (response) {
        document.location.reload();
    }
  });
}
export const deleteCourse = () => {
  $('body').on('click', '.js-delete-course', function (evt) {
    if (!confirm(Translator.trans('是否确定删除该教学计划？'))) {
      return;
    }
    $.post($(evt.currentTarget).data('url'), function (data) {
      if (data.success) {
        notify('success', '删除成功');
        location.reload();
      } else {
        notify('danger', '删除失败：' + data.message);
      }
    });
  });
}

export const publishCourse = () => {
  $('body').on('click', '.js-publish-course', function (evt) {
    if (!confirm(Translator.trans('是否确定发布该教学计划？'))) {
      return;
    }
    $.post($(evt.target).data('url'), function (data) {
      if (data.success) {
        notify('success', '发布成功');
        location.reload();
      } else {
        notify('danger', '发布失败：' + data.message);
      }
    });
  });
}

export const deleteTask = () => {
  $('body').on('click', '.delete-item', function (evt) {
    if ($(evt.currentTarget).data('type') == 'task') {
      if (!confirm(Translator.trans('是否确定删除该任务吗？'))) {
        return;
      }
    } else if ($(evt.currentTarget).data('type') == 'chapter') {
      if (!confirm(Translator.trans('是否确定删除该章节吗？'))) {
        return;
      }
    }
    $.post($(evt.currentTarget).data('url'), function (data) {
      if (data.success) {
        notify('success', '删除成功');
        $(evt.target).parents('.task-manage-item').remove();
        sortList();
      } else {
        notify('danger', '删除失败：' + data.message);
      }
    });
  });
}

export const publishTask = () => {
  $('body').on('click', '.publish-item', (event) => {
    $.post($(event.target).data('url'), function (data) {
      if (data.success) {
        notify('success', '发布成功');
        location.reload();
      } else {
        notify('danger', '发布失败：' + data.message);
      }
    });
  })
}

export const unpublishTask = () => {
  $('body').on('click', '.unpublish-item', (event) => {
    $.post($(event.target).data('url'), function (data) {
      if (data.success) {
        notify('success', '取消发布成功');
        location.reload();
      } else {
        notify('danger', '取消发布失败：' + data.message);
      }
    });
  })
}

export const showSettings = () => {
  $("#sortable-list").on('click', '.js-item-content', event => {
    var $this = $(event.currentTarget).closest('.js-task-manage-item');
    $this.siblings(".js-task-manage-item.active").removeClass('active').find('.js-settings-list').slideToggle();
    $this.addClass('active').find('.js-settings-list').slideToggle();
  });
  // $("#sortable-list").on('click','.js-settings-item.active',event=>{
  //   return false;
  // });
}

export const TabChange = () => {
  $('[data-role="tab"]').click(function (event) {
    console.log('tag change : ', event);
    let $this = $(this);
    $($this.data('tab-content')).removeClass("hidden").siblings('[data-role="tab-content"]').addClass('hidden');
  });
}
