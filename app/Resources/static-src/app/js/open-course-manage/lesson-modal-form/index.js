import FileChooser from 'app/js/file-chooser/file-choose';
import SubtitleDialog from 'app/js/activity-manage/video/subtitle/dialog';

class LessonModalForm {
  constructor() {
    this.init();
  }

  init() {
    this.initForm();
    this.initfileChooser();
  }

  initForm() {
    let $form = $('#lesson-create-form');

    let validator = $form.data('validator');

    $form.validate({
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
          required: '请输入时长',
        },
        second: {
          required: '请输入时长',
          second_range: '秒数只能在0-59之间',
        },
        'mediaSource': "请上传或选择%display%",
      }
    })

    $(".js-length").blur(function () {
      if (validator && validator.form()) {
        const minute = parseInt($('#minute').val()) | 0;
        const second = parseInt($('#second').val()) | 0;
        $("#length").val(minute * 60 + second);
      }
    });

    $form.data('validator', validator);
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
        $("#minute").val(minute);
        $("#second").val(second);
        $("#length").val(minute * 60 + second);
      }
      $('#mediaSource').val(file.source);
      if (file.source == 'self') {
        $("#mediaId").val(file.id);
        $("#mediaUri").val('');
      } else {
        $("#mediaUri").val(file.uri);
        $("#mediaId").val(0);
      }
      //渲染字幕
      subtitleDialog.render(file);
    };

    fileChooser.on('select', onSelectFile);
  }
}

new LessonModalForm();