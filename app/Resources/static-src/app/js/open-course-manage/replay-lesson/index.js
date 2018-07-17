import sortList from 'common/sortable';
import notify from 'common/notify';

let sortListAfter = function (data, $list) {
  $.post($list.data('sortUrl'), { ids: data }, function (response) {
    let lessonNum = 0, chapterNum = 0, unitNum = 0;

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
};

let $list = $('#course-item-list');
sortList({
  element: '#course-item-list',
  itemSelector: '.item-lesson, .item-chapter',
}, (data) => {
  sortListAfter(data, $list);
});



$list.on('click', '.delete-lesson-btn', function (e) {
  if (!confirm(Translator.trans('confirm.delete_lesson.message'))) {
    return;
  }
  let $btn = $(e.currentTarget);
  let _isTestPaper = function () {
    return $btn.parents('.item-chapter')[0];
  };
  let _remove_item = function () {
    if (_isTestPaper()) {
      $btn.parents('.item-chapter').remove();
    } else {
      $btn.parents('.item-lesson').remove();
    }
  };
  $.post($(this).data('url'), function (response) {
    _remove_item();
    sortListAfter($list);
    notify('success', Translator.trans('notify.lesson_deleted.message'));
  }, 'json');
});

$list.on('click', '.delete-chapter-btn', function (e) {
  let chapter_name = $(this).data('chapter');
  let part_name = $(this).data('part');
  if (!confirm(Translator.trans('confirm.delete_chapter.message', { chapter_name: chapter_name, part_name: part_name }))) {
    return;
  }
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (response) {
    $btn.parents('.item-chapter').remove();
    sortListAfter($list);
    notify('success' + chapter_name + '' + part_name + Translator.trans('notify.chapter_deleted.message'));
  }, 'json');
});

$list.on('click', '.replay-lesson-btn', function (e) {
  if (!confirm(Translator.trans('confirm.replay_lesson.message'))) {
    return;
  }
  $.post($(this).data('url'), function (html) {
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

$list.on('click', '.publish-lesson-btn', function (e) {
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (html) {
    let id = '#' + $(html).attr('id');
    $(id).find('.item-content .unpublish-warning').remove();
    $(id).find('.item-actions .publish-lesson-btn').parent().addClass('hidden').removeClass('show');
    $(id).find('.item-actions .unpublish-lesson-btn').parent().addClass('show').removeClass('hidden');
    $(id).find('.item-actions .delete-lesson-btn').parent().addClass('hidden').removeClass('show');
    $(id).find('.btn-link').tooltip();
    notify('success', Translator.trans('notify.lesson_publish_success.message'));
  });
});

$list.on('click', '.unpublish-lesson-btn', function (e) {
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (html) {
    let id = '#' + $(html).attr('id');

    if ($(id).find('.item-content').find('.unpublish-warning').length == 0) {
      $(id).find('.item-content').append('<span class="unpublish-warning text-warning">' + Translator.trans('open_course.unpublish_hint') + '</span>');
      $(id).find('.item-actions .publish-lesson-btn').parent().addClass('show').removeClass('hidden');
      $(id).find('.item-actions .unpublish-lesson-btn').parent().addClass('hidden').removeClass('show');
      $(id).find('.item-actions .delete-lesson-btn').parent().addClass('show').removeClass('hidden');
      $(id).find('.btn-link').tooltip();
      notify('success', Translator.trans('notify.lesson_publish_cancel.message'));
    }
  });
});

$list.on('click', '.delete-exercise-btn', function (e) {
  if (!confirm(Translator.trans('confirm.delete_lesson_exercise.message'))) {
    return;
  }
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (response) {
    notify('success', Translator.trans('notify.lesson_exercise_deleted.message'));
    window.location.reload();
  }, 'json');
});

$list.on('click', '.delete-homework-btn', function (e) {
  if (!confirm(Translator.trans('confirm.delete_lesson_homework.message'))) {
    return;
  }
  let $btn = $(e.currentTarget);
  $.post($(this).data('url'), function (response) {
    notify('success', Translator.trans('notify.lesson_homework_deleted.message'));
    window.location.reload();
  }, 'json');
});

$('#course-item-list .item-actions .btn-link').tooltip();
$('#course-item-list .fileDeletedLesson').tooltip();

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

function asyncLoadFiles() {
  var url = $('.lesson-manage-panel').data('file-status-url');
  $.get(url, '', function (data) {
    if (!data || data.length == 0) {
      return;
    }

    for (var i = 0; i < data.length; i++) {
      var file = data[i];
      if (file.convertStatus == 'waiting' || file.convertStatus == 'doing') {
        $('li[data-file-id=' + file.id + ']').find('span[data-role="mediaStatus"]').append('<span class=\'text-warning\'>' + Translator.trans('page.file_converting.message') + '</span>');
      } else if (file.convertStatus == 'error') {
        $('li[data-file-id=' + file.id + ']').find('span[data-role="mediaStatus"]').append('<span class=\'text-danger\'>' + Translator.trans('page.file_convert_failed.message') + '</span>');
      } else if (file.convertStatus == 'success') {
        $('li[data-file-id=' + file.id + ']').find('.mark-manage').show();
        $('li[data-file-id=' + file.id + ']').find('.mark-manage-divider').show();
      }
    }
  });
}

$('.js-lesson-batch-btn-popover').popover({
  html: true,
  trigger: 'hover',
  delay: { 'show': 200, 'hide': 1000 },
  placement: 'top',
  template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
  content: function () {
    var html = $(this).find('.popover-content').html();
    return html;
  }
});
