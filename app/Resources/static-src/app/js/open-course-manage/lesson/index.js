import sortList from 'common/sortable';
import notify from 'common/notify';

let $list = $('#course-item-list');

sortList({
  element: '#course-item-list',
  distance: 20,
  itemSelector: '.item-lesson, .item-chapter'
}, (data) => {
  sortListAfter(data, $('#course-item-list'));
})

$list.on('click', '.delete-lesson-btn', function (e) {
  if (!confirm('删除课时的同时会删除课时的资料、测验。\n您真的要删除该课时吗？')) {
    return;
  }
  let $btn = $(e.currentTarget);
  let _isTestPaper = function () {
    return $btn.parents('.item-chapter')[0];
  }
  let _remove_item = function () {
    if (_isTestPaper()) {
      $btn.parents('.item-chapter').remove();
    } else {
      $btn.parents('.item-lesson').remove();
    }
  }
  $.post($(this).data('url'), function (response) {
    _remove_item();
    sortListAfter($list);
    $('.lesson-manage-panel').find('.only-one-lesson-notify').show();
    $('.lesson-manage-panel').find('#lesson-create-btn').attr('disabled', false);
    notify('success','课时已删除！');
  }, 'json');
});

$list.on('click', '.delete-chapter-btn', function (e) {
  let chapter_name = $(this).data('chapter');
  let part_name = $(this).data('part');
  if (!confirm('您真的要删除该' + chapter_name + '' + part_name + '吗？')) {
    return;
  }
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (response) {
    $btn.parents('.item-chapter').remove();
    sortListAfter($list);
    notify('success' + chapter_name + '' + part_name + '已删除！');
  }, 'json');
});

$list.on('click', '.replay-lesson-btn', function (e) {
  if (!confirm('您真的要录制回放吗？')) {
    return;
  }
  $.post($(this).data('url'), function (html) {
    if (html.error) {
      if (html.error.code == 10019)
        notify('danger',"录制失败，直播时您没有进行录制！");
      else
        notify('danger',"录制失败！");
    } else {
      let id = '#' + $(html).attr('id');
      $(id).replaceWith(html);
      notify('success','课时已录制！');
    }
  });
});

$list.on('click', '.publish-lesson-btn', function (e) {
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (html) {
    let id = '#' + $(html).attr('id');
    $(id).find('.item-content .unpublish-warning').remove();
    $(id).find('.item-actions .publish-lesson-btn').parent().addClass('hidden').removeClass('show');
    $(id).find('.item-actions .unpublish-lesson-btn').parent().addClass('show').removeClass('hidden');
    $(id).find('.item-actions .delete-lesson-btn').parent().addClass('hidden').removeClass('show');
    $(id).find('.btn-link').tooltip();
    notify('success','课时发布成功！');
  });
});

$list.on('click', '.unpublish-lesson-btn', function (e) {
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (html) {
    let id = '#' + $(html).attr('id');
    $(id).find('.item-content').append('<span class="unpublish-warning text-warning">(未发布)</span>');
    $(id).find('.item-actions .publish-lesson-btn').parent().addClass('show').removeClass('hidden');
    $(id).find('.item-actions .unpublish-lesson-btn').parent().addClass('hidden').removeClass('show');
    $(id).find('.item-actions .delete-lesson-btn').parent().addClass('show').removeClass('hidden');
    $(id).find('.btn-link').tooltip();
    notify('success','课时已取消发布！');
  });
});

$list.on('click', '.delete-exercise-btn', function (e) {
  if (!confirm('您真的要删除该课时练习吗？')) {
    return;
  }
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (response) {
    notify('success','练习已删除！');
    window.location.reload();
  }, 'json');
});

$list.on('click', '.delete-homework-btn', function (e) {
  if (!confirm('您真的要删除该课时作业吗？')) {
    return;
  }
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (response) {
    notify('success','作业已删除！');
    window.location.reload();
  }, 'json');
});


$("#course-item-list .item-actions .btn-link").tooltip();
$("#course-item-list .fileDeletedLesson").tooltip();

$('.dropdown-menu').parent().on('shown.bs.dropdown', function () {
  if ($(this).find('.dropdown-menu-more').css('display') == 'block') {
    $(this).parent().find('.dropdown-menu-more').mouseout(function () {
      $(this).parent().find('.dropdown-menu-more').hide();
    });

    $(this).parent().find('.dropdown-menu-more').mouseover(function () {
      $(this).parent().find('.dropdown-menu-more').show();
    });

  } else {
    $(this).parent().find('.dropdown-menu-more').show();
  }
});

$('.dropdown-menu').parent().on('hide.bs.dropdown', function () {
  $(this).find('.dropdown-menu-more').show();
});

asyncLoadFiles();

$('#lesson-create-btn').click(function () {
  let url = $(this).data('url');
  $.get(url, function (data) {
    if (data['result']) {
      notify('warning',"公开课当前仅支持添加一个课时!");
    } else {
      $('#modal').html(data);
      $('#modal').modal('show');
    }
  })
})

function asyncLoadFiles() {
  let url = $('.lesson-manage-panel').data('file-status-url');

  let fileIds = new Array();
  $('.lesson-list .item-lesson').each(function () {
    if (!isNaN($(this).data('file-id'))) {
      fileIds.push($(this).data('file-id'));
    }
  });

  if (fileIds.length == 0) {
    return;
  }

  $.post(url, { 'ids': fileIds.join(",") }, function (data) {

    if (!data || data.length == 0) {
      return;
    }

    for (let i = 0; i < data.length; i++) {
      let file = data[i];

      if ($.inArray(file.type, ['video', 'ppt', 'document']) > -1) {
        if (file.convertStatus == 'waiting' || file.convertStatus == 'doing') {
          $("li[data-file-id=" + file.id + "]").find('span[data-role="mediaStatus"]').append("<span class='text-warning'>(正在文件格式转换)</span>");
        } else if (file.convertStatus == 'error') {
          $("li[data-file-id=" + file.id + "]").find('span[data-role="mediaStatus"]').append("<span class='text-danger'>(文件格式转换失败)</span>");
        } else if (file.convertStatus == 'success') {
          $("li[data-file-id=" + file.id + "]").find('.mark-manage').show();
          $("li[data-file-id=" + file.id + "]").find('.mark-manage-divider').show();
        }
      }
    }
  });
}

function sortListAfter(data, $list) {
  $.post($list.data('sortUrl'), { ids: data }, function (response) {
    let lessonNum = chapterNum = unitNum = 0;

    $list.find('.item-lesson, .item-chapter').each(function () {
      let $item = $(this);
      if ($item.hasClass('item-lesson')) {
        lessonNum++;
        $item.find('.number').text(lessonNum);
      } else if ($item.hasClass('item-chapter-unit')) {
        unitNum++;
        $item.find('.number').text(unitNum);
      } else if ($item.hasClass('item-chapter')) {
        chapterNum++;
        unitNum = 0;
        $item.find('.number').text(chapterNum);
      }

    });
  });
}