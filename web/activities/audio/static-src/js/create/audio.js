import FileChooser from 'app/js/file-chooser/file-choose';
import { initEditor } from 'app/js/activity-manage/editor.js';
import { chooserUiClose, showChooserType } from 'app/js/activity-manage/widget/chooser-ui.js';
export default class Audio {
  constructor() {
    let mediaId = $('#step2-form').data('mediaId');
    showChooserType(mediaId);
    this.initStep2Form();
    this.autoValidatorLength();
    this.initFileChooser();
    this.initEvent();
    this.initCkeditor(this.validate);
  }

  initEvent() {
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validate.form(), data: window.ltc.getFormSerializeObject($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validate.form() });
    });

    $('input[name="hasText"]').on('change', event => {
      let $target = $(event.currentTarget);
      if ($target.val() == 0) {
        $('.js-content').hide();
      }
      if ($target.val() == 1) {
        $('.js-content').show();
      }
    });
  }
  initCkeditor(validator) {
    // group: 'course'
    var editor = CKEDITOR.replace('audio-content-field', {
      toolbar: 'Simple',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $('#audio-content-field').data('imageUploadUrl')
    });
  
    editor.on('change', () => {
      $('#audio-content-field').val(editor.getData());
      validator.form();
    });
    editor.on('blur', () => {
      $('#audio-content-field').val(editor.getData());
      validator.form();
    });
  }

  initStep2Form() {
    let $step2_form = $('#step2-form');
    this.validate = $step2_form.validate({
      groups: {
        nameGroup: 'minute second'
      },
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        minute: 'required unsigned_integer unsigned_integer',
        second: 'required second_range unsigned_integer',
      },
      messages: {
        minute: {
          required: Translator.trans('activity.audio_manage.length_required_error_hint'),
          unsigned_integer: Translator.trans('activity.audio_manage.length_unsigned_integer_error_hint')
        },
        second: {
          required: Translator.trans('activity.audio_manage.length_required_error_hint'),
          second_range: Translator.trans('activity.audio_manage.second_range_error_hint'),
          unsigned_integer: Translator.trans('activity.audio_manage.length_unsigned_integer_error_hint')
        },
      }
    });

  }

  autoValidatorLength() {
    $('.js-length').blur(() => {
      if (this.validate.form()) {
        const minute = parseInt($('#minute').val()) | 0;
        const second = parseInt($('#second').val()) | 0;
        $('#length').val(minute * 60 + second);
      }
    });
  }

  initFileChooser() {
    const fileChooser = new FileChooser();
    const onSelectFile = file => {
      chooserUiClose();
      let placeMediaAttr = (file) => {
        if (file.length !== 0 && file.length !== undefined) {
          let $minute = $('#minute');
          let $second = $('#second');
          let $length = $('#length');

          let length = parseInt(file.length);
          let minute = parseInt(length / 60);
          let second = length % 60;
          $minute.val(minute);
          $second.val(second);
          $length.val(length);
          file.minute = minute;
          file.second = second;
        }
        $('[name="media"]').val(JSON.stringify(file));
      };
      placeMediaAttr(file);

      $('#step2-form').valid();
    };
    fileChooser.on('select', onSelectFile);
  }
}