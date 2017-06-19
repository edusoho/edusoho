import notify from 'common/notify';
import sortList from 'common/sortable';

export const sortablelist = (list) => {
  let $list = $(list);
  var data = $list.sortable("serialize").get();
  $.post($list.data('sortUrl'), { ids: data }, (response) => {
    let lessonNum = 0,
      chapterNum = 0,
      unitNum = 0;
    $list.find('.task-manage-item').each(function () {
      var $item = $(this);
      if ($item.hasClass('js-task-manage-item')) {
        if ($item.find('.number').length > 0) {
          lessonNum++;
          $item.find('.number').text(lessonNum);
        }
      } else if ($item.hasClass('task-manage-unit')) {
        unitNum++;
        $item.find('.number').text(unitNum);
      } else if ($item.hasClass('task-manage-chapter')) {
        chapterNum++;
        unitNum = 0;
        $item.find('.number').text(chapterNum);
      }
    });
    $list.trigger('finished');
  });
}

export const taskSortable = (list) => {
  if ($(list).length) {
    sortList({
      element: list,
      ajax: false,
    }, (data) => {
      sortablelist(list);
    });
  }
}

export const closeCourse = () => {
  $('body').on('click', '.js-close-course', function (evt) {
    let $target = $(evt.currentTarget);
    if (!confirm(Translator.trans('是否确定关闭该教学计划？'))) {
      return;
    }

    $.post($target.data('check-url'), function (data) {

      if (data.warn) {
        if (!confirm(Translator.trans(data.message))) {
          return;
        }
      }

      $.post($target.data('url'), function (data) {
        if (data.success) {
          notify('success', '关闭成功');
          location.reload();
        } else {
          notify('danger', '关闭失败：' + data.message);
        }
      });
    });
  });
};

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
};

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
        notify('danger', '发布失败：' + data.message, {delay:5000});
      }
    });
  });
};

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
        sortablelist('#sortable-list');
        console.log($('#sortable-list').children('li').length);
        if($('#sortable-list').children('li').length < 1 && $('.js-task-empty').hasClass('hidden') ) {
            $('.js-task-empty').removeClass('hidden');
        }
        document.location.reload();
      } else {
        notify('danger', '删除失败：' + data.message);
      }
    });
  });
};

export const publishTask = () => {
  $('body').on('click', '.publish-item', (event) => {
    $.post($(event.target).data('url'), function (data) {
      if (data.success) {
        var parentLi = $(event.target).closest('.task-manage-item');
        notify('success', '发布成功');
        $(parentLi).find('.publish-item').addClass('hidden')
        $(parentLi).find('.delete-item').addClass('hidden')
        $(parentLi).find('.unpublish-item').removeClass('hidden')
        $(parentLi).find('.publish-status').addClass('hidden')
      } else {
        notify('danger', '发布失败：' + data.message);
      }
    });
  })
};

export const unpublishTask = () => {
  $('body').on('click', '.unpublish-item', (event) => {
    $.post($(event.target).data('url'), function (data) {
      if (data.success) {
        var parentLi = $(event.target).closest('.task-manage-item');
        notify('success', '取消发布成功');
        $(parentLi).find('.publish-item').removeClass('hidden')
        $(parentLi).find('.delete-item').removeClass('hidden')
        $(parentLi).find('.unpublish-item').addClass('hidden')
        $(parentLi).find('.publish-status').removeClass('hidden')
      } else {
        notify('danger', '取消发布失败：' + data.message);
      }
    });
  })
};

export const showSettings = () => {
  $("#sortable-list").on('click', '.js-item-content', (event) => {
    console.log('click');
    let $this = $(event.currentTarget);
    let $li = $this.closest('.js-task-manage-item');
    if ($li.hasClass('active')) {
      $li.removeClass('active').find('.js-settings-list').stop().slideUp(500);
    }
    else {
      $li.addClass('active').find('.js-settings-list').stop().slideDown(500);
      $li.siblings(".js-task-manage-item.active").removeClass('active').find('.js-settings-list').hide();
    }
  });
};

export const TabChange = () => {
  $('[data-role="tab"]').click(function (event) {
    let $this = $(this);
    $($this.data('tab-content')).removeClass("hidden").siblings('[data-role="tab-content"]').addClass('hidden');
  });
};

export const updateTaskNum = (container) => {
  // let $container = $(container);
  // $container.on('finished',function(){
  //   $('#task-num').text($(container).find('i[data-role="task"]').length);
  // })
}

export const TaskListHeaderFixed = () => {
  let $header = $('.js-task-list-header');
  if(!$header.length){
    return;
  }
  let headerTop = $header.offset().top;
	$(window).scroll(function(event) {
			if ($(window).scrollTop() >= headerTop) {
				$header.addClass('fixed')
			} else {
				$header.removeClass('fixed');
			}
	});
}

