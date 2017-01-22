import FileChooser from '../../file-chooser/file-choose';
import {chooserUiOpen, chooserUiClose, showChooserType} from '../widget/chooser-ui.js';
import SubtitleDialog from './subtitle/dialog';


jQuery.validator.addMethod("unsigned_integer", function (value, element) {
  return this.optional(element) || /^([1-9]\d*|0)$/.test(value);
}, "时长必须为非负整数");

jQuery.validator.addMethod("second_range", function (value, element) {
  return this.optional(element) || /^([0-9]|[012345][0-9]|59)$/.test(value);
}, "秒数只能在0-59之间");

jQuery.validator.addMethod("time_length", function (value, element) {
  return parseInt($("#minute").val()) + parseInt($("#second").val()) > 0
}, "时长不能等于0");


showChooserType($('[name="ext[mediaSource]"]'));


function _inItStep3from() {

  var $step3_forom = $('#step3-form');
  var validator = $step3_forom.data('validator');

  $step3_forom.validate({
    rules: {
      'ext[finishDetail]': {
        required: true,
        unsigned_integer: true,
        max: 300,
        min: 1,
      }
    },
    messages: {
      'ext[finishDetail]': {
        required: '请输入时长',
        max: '时长不能大于300分钟',
        min: '时长不能小于1分钟'
      }
    }
  });
  $step3_forom.data('validator', validator);
}
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
      'ext[mediaSource]': 'required',
      'ext[finishDetail]': 'unsigned_integer'
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
      'ext[mediaSource]': "请上传或选择%display%",
      'ext[finishDetail]': 'dddd'
    }
  });
  $step2_form.data('validator', validator);
}

_inItStep2form();


$(".js-length").blur(function () {
  let validator = $("#step2-form").data('validator');
  if (validator && validator.form()) {
    const minute = parseInt($('#minute').val()) | 0;
    const second = parseInt($('#second').val()) | 0;
    $("#length").val(minute * 60 + second);
  }
});

const fileChooser = new FileChooser();
//字幕组件
const subtitleDialog = new SubtitleDialog('.js-subtitle-list');


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

  //渲染字幕
  subtitleDialog.render(file);
};

$("#finish-condition").on('change', function (event) {
  if (event.target.value == 'time') {
    $('.viewLength').removeClass('hidden');
    _inItStep3from();
  } else {
    $('.viewLength').addClass('hidden');
    $('input[name="ext[finishDetail]"]').rules('remove')
  }
})

fileChooser.on('select', onSelectFile);
