let $content = $('#live-lesson-content-field');
let $form = $('#live-open-course-form');
let now = new Date();
let $btn = $('#live-open-course-form-btn');

if (!$form.data('update')) {
  $('[name=startTime]').attr('disabled', true);
  $('#live-length-field').attr('disabled', true);
  $('#live-open-course-form-btn').attr('disabled', true);

  $('#starttime-help-block').html(Translator.trans('activity.live.started_or_ended_notice'));
  $('#starttime-help-block').css('color', '#a94442');
  $('#timelength-help-block').html(Translator.trans('activity.live.started_or_ended_notice'));
  $('#timelength-help-block').css('color', '#a94442');
} else {
  $('[name=startTime]').attr('disabled', false);
  $('#live-open-course-form-btn').attr('disabled', false);
}

let validator = $form.validate({
  rules: {
    startTime: {
      required: true,
      after_now: true,
      es_remote: {
        type: 'post',
        data: {
          clientTime: function () {
            return $('[name=startTime]').val();
          }
        }
      }
    },
    timeLength: {
      required: true,
      positive_integer: true,
      es_remote: {
        type: 'get',
        data: {                     //要传递的数据
          startTime: function () {
            return $('[name=startTime]').val();
          },
          length: function () {
            return $('[name=timeLength]').val();
          },
        }
      }
    }
  },
  messages: {
    startTime: {
      es_remote: Translator.trans('validate.after_now.message')
    }
  }
});

$('[name=startTime]').datetimepicker({
  autoclose: true,
  language: document.documentElement.lang
}).on('hide', function (ev) {
  validator.element('[name=startTime]');
});
$('[name=startTime]').datetimepicker('setStartDate', now);

$btn.click(() => {
  if (validator.form()) {
    $btn.button('loading');
    $form.submit();
  }
});
