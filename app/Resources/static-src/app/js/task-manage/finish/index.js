let $selectFinish = $('#finish-type');
if ($selectFinish.length) {
  $selectFinish.on('change', function () {
    $('#conditions').children().hide();
    $('#endConditions').addClass('hidden');
    let val = $(this).val();
    if ('time' == val) {
      $('#watchTime').rules('add', {
        required: true,
        positive_integer: true,
        messages: {
          required: Translator.trans('activity.video_manage.length_required_error_hint')
        }
      });
    } else {
      $('#watchTime').rules('remove');
    }
    $('#homeworkScore').rules('remove');
    switch (val) {
    case 'time':
      $('#conditions-time').show();
      if (!$('#watchTime').val()) {
        let $options = $('#finish-type option:selected');
        $('#watchTime').val($options.data('value'));
        $('#finish-data').val($options.data('value'));
      }
      $('#watchTimeLabel').hasClass('hidden') ? null : $('#watchTimeLabel').addClass('hidden');
      $('#timeLabel').hasClass('hidden') ? $('#timeLabel').removeClass('hidden') : null;
      break;
    case 'watchTime':
      $('#conditions-time').show();
      if (!$('#watchTime').val()) {
        let $options = $('#finish-type option:selected');
        $('#watchTime').val($options.data('value'));
        $('#finish-data').val($options.data('value'));
      }
      $('#watchTimeLabel').hasClass('hidden') ? $('#watchTimeLabel').removeClass('hidden') : null;
      $('#timeLabel').hasClass('hidden') ? null : $('#timeLabel').addClass('hidden');
      break;
    case 'score':
      if($('.js-homework-score').length >0){
        let val = $('#task-create-content-iframe', parent.document).contents().find('.js-homework-scores-input').val();
        $('.js-finish-score').html(val);
        $('#homeworkScore').rules('add', {
          required: true,
          es_score: true,
          homework_score: true,
          messages: {
            required: Translator.trans('course.homework.score.tip1')
          }
        });
        $('.js-homework-score').show();
      }
      break;
    case 'end':
      $('#endConditions').removeClass('hidden');
      break;
    default:
      $selectFinish.trigger('selectChange', val);
    }
  });
  $('#js-end-rule').on('change', function () {
    if ($(this).is(':checked')) {
      $('#finish-data').val(parseInt($('#watchTime').val()) ? $('#watchTime').val() : 1); // 禁止拖动
    } else {
      $('#finish-data').val(''); // 不禁止拖动
    }
  });
  
}

let validate = $('#step3-form').validate({
  groups: {
    nameGroup: 'minute second'
  },
  rules: {
    watchTime: {
      positive_integer: true,
    },
    homeworkScore: {
      es_score: true,
    }
  }
});

if (!$('#conditions-time').is(':hidden')) {
  $('#watchTime').rules('add', {
    required: true,
    positive_integer: true,
    messages: {
      required: Translator.trans('activity.video_manage.length_required_error_hint')
    }
  });
}

$('#homeworkScore').on('change', function() {
  $('#finish-data').val($(this).val());
});

$('#watchTime').on('change', function () {
  $('#finish-data').val($(this).val());
});

window.ltc.on('getCondition', function (msg) {
  if ($('#finish-type-select').length > 0) {
    window.ltc.emit('returnCondition', {
      valid: validate.form(),
      data: {finishType: $('#finish-type-select:checked').val()}
    });
  } else {
    window.ltc.emit('returnCondition', {
      valid: validate.form(),
      data: {finishType: $('#finish-type').val(), finishData: $('#finish-data').val()}
    });
  }
});

$.validator.addMethod('homework_score', function (value, element) {
  return this.optional(element) || value <= Number($('.js-finish-score').html());
}, $.validator.format(Translator.trans('course.homework.score.tip2')));
