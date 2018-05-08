import notify from 'common/notify';

var $list = $('#course-student-list');

$list.on('click', '.student-remove', function () {
  var $tr = $(this).parents('tr');
  var user_name = $('.student-remove').data('user');
  if (!confirm(Translator.trans('classroom_manage.student_manage_remove_hint', { username: user_name }))) {
    return;
  }

  $.post($(this).data('url'), function (response) {
    var user_name = $('.student-remove').data('user');
    if (response.code == 'error') {
      notify('danger',Translator.trans(response.message, {username: user_name}));
    } else {
      notify('success',Translator.trans('classroom_manage.student_manage_remove_success_hint', { username: user_name }));
      $tr.remove();
    }
  }).error(function () {
    var user_name = $('.student-remove').data('user');
    notify('danger',Translator.trans('classroom_manage.student_manage_remove_failed_hint', { username: user_name }));
  });
});

$('#refund-coin-tips').popover({
  html: true,
  trigger: 'hover',//'hover','click'
  placement: 'left',//'bottom',
  content: $('#refund-coin-tips-html').html()
});

$('#course-student-list').on('click', '.follow-student-btn, .unfollow-student-btn', function () {

  var $this = $(this);

  $.post($this.data('url'), function () {
    $this.hide();
    if ($this.hasClass('follow-student-btn')) {
      $this.parent().find('.unfollow-student-btn').show();
    } else {
      $this.parent().find('.follow-student-btn').show();
    }
  });

});

$('#export-students-btn').on('click', function () {
  $('#export-students-btn').button('loading');
  $.get($('#export-students-btn').data('datasUrl'), { start: 0 }, function (response) {
    if (response.status === 'getData') {
      exportStudents(response.start, response.fileName);
    } else {
      $('#export-students-btn').button('reset');
      location.href = $('#export-students-btn').data('url') + '&fileName=' + response.fileName;
    }
  });
});

function exportStudents(start, fileName) {
  var start = start || 0,
    fileName = fileName || '';

  $.get($('#export-students-btn').data('datasUrl'), { start: start, fileName: fileName }, function (response) {
    if (response.status === 'getData') {
      exportStudents(response.start, response.fileName);
    } else {
      $('#export-students-btn').button('reset');
      location.href = $('#export-students-btn').data('url') + '&fileName=' + response.fileName;
    }
  });
}