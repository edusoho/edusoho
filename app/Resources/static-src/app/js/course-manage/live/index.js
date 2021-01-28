import notify from 'common/notify';

initData();

function initData() {
  if (!$('#detail-data').length) {
    return;
  }
  if (!$('#detail-data').data('showable')) {
    notify('warning', Translator.trans('course_manage.live_statistics.live_not_end'));
    return;
  }

  $.get($('#detail-data').data('url'), function (response) {
    if (response.checkin.errorCode || response.visitor.errorCode) {
      if (response.checkin.errorCode == 4002) {
        $('#checkin-detail').html('<h4 class="col-md-12">'+Translator.trans('course_manage.live_statistics.checkin_not_support')+'</h4>');
      }else {
        notify('warning', Translator.trans('course_manage.live_statistics.empty_tips'));
      }
    }

    if (!response.checkin.errorCode && !response.visitor.errorCode && (!response.checkin.data.success || !response.visitor.data.success)) {
      notify('warning', Translator.trans('course_manage.live_statistics.data_not_valid'));
    }

    let checkin = response.checkin.data;
    let visitor = response.visitor.data;

    if ($('#checkin-time').length && checkin && checkin.time) {
      $('#checkin-time').html(checkin.time);
    }

    if ($('#checkin-data').length && checkin && checkin.detail) {
      $('#checkin-data').html(checkin.detail.length);
    }

    if ($('#visitor-learn-time').length && visitor && visitor.totalLearnTime) {
      let studentNum = $('#course-student-number').html();
      $('#visitor-learn-time').html(studentNum > 0 ? Math.ceil(visitor.totalLearnTime / studentNum) : 0);
    }
  });
}
