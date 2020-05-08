import notify from 'common/notify';
import BatchSelect from 'app/common/widget/batch-select';

let $list = $('#course-student-list');
new BatchSelect($list);

$list.on('click', '.student-remove', function () {
  let $tr = $(this).parents('tr');
  let user_name = $('.student-remove').data('user');
  if (!confirm(Translator.trans('classroom_manage.student_manage_remove_hint', { username: user_name }))) {
    return;
  }

  $.post($(this).data('url'), function (response) {
    if (response.code === 'error') {
      notify('danger', Translator.trans(response.message, {username: user_name}));
    } else {
      notify('success', Translator.trans('classroom_manage.student_manage_remove_success_hint', { username: user_name }));
      $tr.remove();
    }
  }).error(function () {
    notify('danger', Translator.trans('classroom_manage.student_manage_remove_failed_hint', { username: user_name }));
  });
});

let getSelectIds = function () {
  let ids = [];
  $list.find('[data-role="batch-item"]:checked').each(function () {
    ids.push(this.value);
  });

  return ids;
};

$('#batch-update-expiry-day').on('click', function () {
  let ids = getSelectIds();
  if (ids.length === 0) {
    cd.message({type: 'danger', message: Translator.trans('course.manage.student.add_expiry_day.select_tips')});
    return;
  }
  $.get($(this).data('url'), {userIds: ids}, function (html) {
    $('#modal').html(html).modal('show');
  });
});

$('#batch-remove').on('click', function () {
  let ids = getSelectIds();
  if (ids.length === 0) {
    cd.message({type: 'danger', message: Translator.trans('course.manage.student.batch_remove.select_tips')});
    return;
  }
  if (!confirm(Translator.trans('course.manage.students_delete_hint'))) {
    return;
  }
  $.post($(this).data('url'), {studentIds: ids}, function (resp) {
    if (resp.success) {
      cd.message({ type: 'success', message: Translator.trans('member.delete_success_hint') });
      location.reload();
    } else {
      cd.message({ type: 'danger', message: Translator.trans('member.delete_fail_hint') + ':' + resp.message });
    }
  });
});

$('#refund-coin-tips').popover({
  html: true,
  trigger: 'hover',//'hover','click'
  placement: 'left',//'bottom',
  content: $('#refund-coin-tips-html').html()
});

$list.on('click', '.follow-student-btn, .unfollow-student-btn', function () {
  let $this = $(this);

  $.post($this.data('url'), function () {
    $this.hide();
    if ($this.hasClass('follow-student-btn')) {
      $this.parent().find('.unfollow-student-btn').show();
    } else {
      $this.parent().find('.follow-student-btn').show();
    }
  });
});

let $exportBtn = $('#export-students-btn');

$exportBtn.on('click', function () {
  $exportBtn.button('loading');

  exportStudents();
});

function exportStudents(start, fileName) {
  start = start || 0;
  let query = fileName ? {start: start, fileName: fileName} : {start: start};

  $.get($exportBtn.data('datasUrl'), query, function (response) {
    if (response.status === 'getData') {
      exportStudents(response.start, response.fileName);
    } else {
      $exportBtn.button('reset');
      location.href = $exportBtn.data('url') + '&fileName=' + response.fileName;
    }
  });
}