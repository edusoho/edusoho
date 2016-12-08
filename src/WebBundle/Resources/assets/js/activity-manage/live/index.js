import { initEditor } from '../editor'

class Live {
  constructor(props) {
    this._init();
  }
  _init() {
    initEditor($('[name="remark"]'));
    this._dateTimePicker();
    this._initStep2Form();
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
          date: true
        },
        length: {
          required: true,
          digits: true,
          max: 300
        },
        remark: {
          maxlength: 1000
        },
      },
    });
  }

  _dateTimePicker() {
    let $starttime = $('#startTime');
    $starttime.datetimepicker({
      format: 'yyyy-mm-dd hh:ii',
      language: "zh",
      autoclose: true
    });
    $starttime.datetimepicker('setStartDate', new Date());
  }
}

new Live();
