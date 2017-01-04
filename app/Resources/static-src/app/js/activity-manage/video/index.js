import FileChooser from '../../file-chooser/file-choose';
import {chooserUiOpen, chooserUiClose, showChooserType} from '../widget/chooser-ui.js';
jQuery.validator.addMethod("unsigned_integer", function (value, element) {
  return this.optional(element) || /^([1-9]\d*|0)$/.test(value);
}, "时长必须为非负整数");

jQuery.validator.addMethod("second_range", function(value, element) {
  return this.optional(element) || /^([0-9]|[012345][0-9]|59)$/.test(value);
}, "秒数只能在0-59之间");

jQuery.validator.addMethod("time_length", function (value, element) {
  return parseInt($("#minute").val()) + parseInt($("#second").val()) > 0
}, "时长不能等于0");


showChooserType($('[name="ext[mediaSource]"]'));

function _inItStep2form() {
  var $step2_form = $('#step2-form');
  var validator = $step2_form.data('validator');
  $step2_form.validate({
    groups: {
      date: 'minute second'
    },
    rules: {
      title: {
        required: true,
        maxlength: 50,
      },
      minute: 'required unsigned_integer time_length',
      second: 'required second_range time_length',
      'ext[mediaSource]': 'required'
    },
    messages: {
      minute: {
        required: '请输入时长',
        time_length: '时长必须大于0'
      },
      second: {
        required: '请输入时长',
        second_range: '秒数只能在0-59之间',
        time_length: '时长必须大于0'
      },
      'ext[mediaSource]': "请上传或选择%display%"
    }
  });
  $step2_form.data('validator', validator);
}

_inItStep2form();


$(".js-length").blur(function() {
  let validator = $("#step2-form").data('validator');
  if (validator && validator.form()) {
    const minute = parseInt($('#minute').val()) | 0;
    const second = parseInt($('#second').val()) | 0;
    $("#length").val(minute * 60 + second);
  }
});

const fileChooser = new FileChooser();

const onSelectFile = file => {
  chooserUiClose();
  if (file.length && file.length > 0) {
    let minute = parseInt(file.length / 60);
    let second = Math.round(file.length % 60);
    $("#minute").val(minute);
    $("#second").val(second);
    $("#length").val(minute * 60 + second);
  }
  $('[name="ext[mediaSource]"]').val(file.source);
  if (file.source == 'self') {
    $("#ext_mediaId").val(file.id);
  } else {
    $("#ext_mediaUri").val(file.uri)
  }

};

fileChooser.on('select', onSelectFile);
