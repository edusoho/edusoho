let $content = $('#live-lesson-content-field');
let $form = $('#live-open-course-form');
let now = new Date();
let $btn = $('#live-open-course-form-btn');
let thisTime = $('[name=startTime]').val();
thisTime = thisTime.replace(/-/g, '/');
thisTime = Date.parse(thisTime) / 1000;
let nowTime = Date.parse(new Date()) / 1000;

if (!$form.data('update')) {
  $('[name=startTime]').attr('disabled', true);
  $('#live-length-field').attr('disabled', true);
  $('#live-open-course-form-btn').attr('disabled', true);

  $('#starttime-help-block').html('直播已经开始或者结束,无法编辑');
  $('#starttime-help-block').css('color', '#a94442');
  $('#timelength-help-block').html('直播已经开始或者结束,无法编辑');
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
      es_remote: '开始时间不能小于服务器时间'
    }
  }
});

$('[name=startTime]').datetimepicker({
  autoclose: true,
  language: document.documentElement.lang
}).on('hide', function (ev) {
  validator.form();
});
$('[name=startTime]').datetimepicker('setStartDate', now);

$btn.click(() => {
  if (validator.form()) {
    $btn.button('loading');
    $form.submit();
  }
});
