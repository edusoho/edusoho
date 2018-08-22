import loadAnimation from 'common/load-animation';
import FileChooser from 'app/js/file-chooser/file-choose';
import SubtitleDialog from 'app/js/activity-manage/video/subtitle/dialog';

class LessonModal {
  constructor(options) {
    this.$element = $(options.element);
    this.$form = $(options.form);
    this.validator();
    this.initfileChooser();
  }

  validator() {
    let validator = this.$form.validate({
      currentDom: '#form-submit',
      ajax: true,
      groups: {
        date: 'minute second'
      },
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        minute: 'required unsigned_integer',
        second: 'required second_range',
        'mediaSource': 'required',
      },
      messages: {
        minute: {
          required: Translator.trans('activity.video_manage.length_required_error_hint'),
        },
        second: {
          required: Translator.trans('activity.video_manage.length_required_error_hint'),
          second_range: Translator.trans('validate.second_range.message'),
        },
        'mediaSource': Translator.trans('activity.video_manage.media_error_hint'),
      },
      submitSuccess(res) {
        cd.message({ type: 'success', message: Translator.trans('open_course.lesson.create_success')});
        document.location.reload();
      },
      submitError(res) {
        let msg = '';
        let errorRes = JSON.parse(res.responseText);
        if (errorRes.error && errorRes.error.message) {
          msg = errorRes.error.message;
        }
        cd.message({ type: 'warning', message: Translator.trans('open_course.lesson.create_error') + ':' + msg });
      }
    });

    $('#form-submit').click((event) => {
      if(validator.form()) {
        this.$form.submit();
      }
    });

    $('.js-length').blur(function () {
      if (validator && validator.form()) {
        const minute = parseInt($('#minute').val()) | 0;
        const second = parseInt($('#second').val()) | 0;
        $('#length').val(minute * 60 + second);
      }
    });
  }

  initfileChooser() {
    const fileChooser = new FileChooser();
    //字幕组件
    const subtitleDialog = new SubtitleDialog('.js-subtitle-list');
    const onSelectFile = file => {
      FileChooser.closeUI();
      if (file.length && file.length > 0) {
        let minute = parseInt(file.length / 60);
        let second = Math.round(file.length % 60);
        $('#minute').val(minute);
        $('#second').val(second);
        $('#length').val(minute * 60 + second);
      }
      $('#mediaSource').val(file.source);
      if (file.source == 'self') {
        $('#mediaId').val(file.id);
        $('#mediaUri').val('');
        $('#mediaName').val(file.name);
      } else {
        $('#mediaUri').val(file.uri);
        $('#mediaId').val(0);
        $('#mediaName').val(file.name);
      }
      //渲染字幕
      subtitleDialog.render(file);
    };

    this.$element.on('click', '.js-choose-trigger', (event) => {
      FileChooser.openUI();
      $('[name="mediaSource').val(null);
    });

    fileChooser.on('select', onSelectFile);
  }
}

export default LessonModal;