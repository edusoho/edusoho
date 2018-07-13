import Base from './base';
import EsWebUploader from 'common/es-webuploader.js';

new Base();

let uploader = new EsWebUploader({
  element: '#upload-picture-btn',
  onUploadSuccess: function(file, response) {
    let url = $('#upload-picture-btn').data('gotoUrl');
    $.get(url, function(html) {
      $('#modal').modal({'show':true, 'backdrop':'static'}).html(html);
    });
  }
});

let $form = $('#course-form');
let now = new Date();

if (!$form.data('update')) {

  $('[name=startTime]').attr('disabled', true);
  $('#live-length-field').attr('disabled', true);

  $('#starttime-help-block').html('直播已经开始或者结束,无法编辑');
  $('#starttime-help-block').css('color', '#a94442');
  $('#timelength-help-block').html('直播已经开始或者结束,无法编辑');
  $('#timelength-help-block').css('color', '#a94442');
} else {
  $('[name=startTime]').attr('disabled', false);
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


