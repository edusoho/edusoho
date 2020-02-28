import notify from 'common/notify';

if ($('#detail-data').length) {
  $.get($('#detail-data').data('url'), function (response) {
    console.log(response);
    if (!response.checkin.data.success || !response.visitor.data.success) {
      notify('warning', Translator.trans('course_manage.live_statistics.empty_tips'));
    }

    var checkin = response.checkin.data;
    var visitor = response.visitor.data;

    if ($('#checkin-time').length && checkin.time) {
      $('#checkin-time').html(checkin.time);
    }

    if($('#checkin-data').length && checkin.detail) {
      $('#checkin-data').html(checkin.detail.length);
    }

    if ($('#visitor-learn-time').length && visitor.totalLearnTime) {
      var studentNum = $('#course-student-number').html();
      $('#visitor-learn-time').html(Math.ceil(visitor.totalLearnTime/studentNum));
    }
  });
}