import FileChooser from 'app/js/file-chooser/file-choose';
import SubtitleDialog from 'app/js/activity-manage/video/subtitle/dialog';
import UAParser from 'ua-parser-js';

export default class Video {
  constructor() {
    this.showChooseContent();
    this.initStep2form();
    this.autoValidatorLength();
    this.initfileChooser();
    this.hideSubtitleWidget();
    this.initEvent();
  }

  hideSubtitleWidget() {
    let subtitleWidget = $('#video-subtitle-form-group');
    $('[role="presentation"] a[href!="#import-video-panel"]').click(function () {
      subtitleWidget.show();
    });
    $('a[href="#import-video-panel"]').click(function () {
      subtitleWidget.hide();
    });
  }

  showChooseContent() {
    $('#iframe-content').on('click', '.js-choose-trigger', (event) => {
      FileChooser.openUI();
    });
  }

  initStep2form() {
    this.validate = $('#step2-form').validate({
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
        media: 'required',
      },
      messages: {
        minute: {
          required: Translator.trans('activity.video_manage.length_required_error_hint'),
        },
        second: {
          required: Translator.trans('activity.video_manage.length_required_error_hint'),
          second_range: Translator.trans('activity.video_manage.length_required_error_hint'),
        },
        media: Translator.trans('activity.video_manage.media_error_hint'),
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

  initEvent() {
    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validate.form() });
    });

    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validate.form(), data:window.ltc.getFormSerializeObject($('#step2-form'))});
    });
  }

  initfileChooser() {
    const fileChooser = new FileChooser();
    //字幕组件
    const subtitleDialog = new SubtitleDialog('.js-subtitle-list');
    const onSelectFile = file => {
      FileChooser.closeUI();
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

      $('[name="ext[mediaSource]"]').val(file.source);
      $('#step2-form').valid();
      //渲染字幕
      subtitleDialog.render(file);
    };

    fileChooser.on('select', onSelectFile);

    fileChooser.on('start', file => {
      const ua = new UAParser();
      const FILE_UNIT = ua.getOS().name === 'Mac OS' ? 1000 : 1024;
      const maxFileSizeDesc = $('#maxFileSize').val() || '';
      let maxFileSize = parseInt(maxFileSizeDesc);

      if (maxFileSizeDesc.indexOf('GB') > -1) {
        maxFileSize *= FILE_UNIT * FILE_UNIT * FILE_UNIT;
      } else if (maxFileSizeDesc.indexOf('MB') > -1) {
        maxFileSize *= FILE_UNIT * FILE_UNIT;
      } else {
        return;
      }

      if (file.size > maxFileSize) {
        alert(Translator.trans('activity.video.file_limit_size', { size: maxFileSizeDesc }));
        fileChooser.uploader._sdk.uploader.engine.cancelFile(file);
        fileChooser.uploader._sdk.uploaderUi.resetUI();

        return false;
      }
    });
  }
}
