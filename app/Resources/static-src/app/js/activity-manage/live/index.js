import { initEditor } from '../editor'

class Live {
  constructor(props) {
    this._init();
  }
  _init() {
    this._extendValidator();
    this._dateTimePicker();
    this._initStep2Form();
  }

  _extendValidator() {
    $.validator.addMethod(
      "after",
      function(value, element, params) {
        var now = new Date();
        return value && now < new Date(value);
      },
      Translator.trans('开始时间应晚于当前时间')
    );
  }

  _initEditorContent() {
    var editor = CKEDITOR.replace('text-content-field', {
      toolbar: 'Full',
      filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
      filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
      allowedContent: true,
      height: 300
    });
    editor.on('change', () => {
      $('[name="remark"]').val(editor.getData());
    });
  }
  _initStep2Form() {
    var $step2_form = $("#step2-form");
    var validator = $step2_form.data('validator', validator);
    validator = $step2_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          maxlength: 50,
        },
        startTime: {
          required: true,
          date: true,
          after: true
        },
        length: {
          required: true,
          digits: true,
          max: 300,
          min: 1
        },
        remark: {
          maxlength: 1000
        },
      },
    });
    initEditor($('[name="remark"]'),validator);
  }

  _dateTimePicker() {
    let $starttime = $('#startTime');

    $starttime.datetimepicker({
      format: 'yyyy-mm-dd hh:ii',
      language: "zh",
      autoclose: true,
      endDate: new Date(Date.now() + 86400*365*100*1000)
    });
    $starttime.datetimepicker('setStartDate', new Date());
  }
}

new Live();
