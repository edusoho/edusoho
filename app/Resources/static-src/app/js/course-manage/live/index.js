import notify from 'common/notify';

if ($('#detail-data').length) {
  $.get($('#detail-data').data('url'), function (response) {
    if (!response.checkin.data.success || !response.visitor.data.success) {
      notify('warning', Translator.trans('course_manage.live_statistics.empty_tips'));
    }

    let checkin = response.checkin.data;
    let visitor = response.visitor.data;

    if ($('#checkin-time').length && checkin.time) {
      $('#checkin-time').html(checkin.time);
    }

    if($('#checkin-data').length && checkin.detail) {
      $('#checkin-data').html(checkin.detail.length);
    }

    if ($('#visitor-learn-time').length && visitor.totalLearnTime) {
      let studentNum = $('#course-student-number').html();
      $('#visitor-learn-time').html(studentNum > 0 ? Math.ceil(visitor.totalLearnTime/studentNum) : 0);
    }
  });
}