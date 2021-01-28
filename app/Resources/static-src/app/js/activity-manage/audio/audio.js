import FileChooser from 'app/js/file-chooser/file-choose';
import { chooserUiOpen, chooserUiClose, showChooserType } from 'app/js/activity-manage/widget/chooser-ui.js';
export default class Audio {
  constructor() {
    showChooserType($('[name="ext[mediaId]"]'));
    this.initStep2Form();
    this.autoValidatorLength();
    this.initFileChooser();
  }

  initStep2Form() {
    var $step2_form = $('#step2-form');
    var validator = $step2_form.data('validator');

    $step2_form.validate({
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
        'ext[mediaId]': 'required'
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
        'ext[mediaId]': Translator.trans('activity.audio_manage.media_error_hint')
      }
    });

  }

  autoValidatorLength() {
    $('.js-length').blur(function () {
      let validator = $('#step2-form').data('validator');
      if (validator && validator.form()) {
        const minute = parseInt($('#minute').val()) | 0;
        const second = parseInt($('#second').val()) | 0;
        $('#length').val(minute * 60 + second);
      }
    });
  }

  initFileChooser() {
    const fileChooser = new FileChooser();
    console.log(fileChooser);
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

      $('[name="ext[mediaId]"]').val(file.source);
      $('#step2-form').valid();
      if (file.source == 'self') {
        $('#ext_mediaId').val(file.id);
        $('#ext_mediaUri').val('');
      } else {
        $('#ext_mediaId').val('');
        $('#ext_mediaUri').val(file.uri);
      }
    };
    fileChooser.on('select', onSelectFile);
  }
}