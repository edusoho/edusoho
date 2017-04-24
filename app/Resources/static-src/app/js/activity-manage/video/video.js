import FileChooser from 'app/js/file-chooser/file-choose';
import SubtitleDialog from 'app/js/activity-manage/video/subtitle/dialog';

export default class Video {
  constructor() {
    this.showChooseContent();
    this.initStep2form();
    this.isInitStep3from();
    this.autoValidatorLength();
    this.initfileChooser();
    this.hideSubtitleWidget();
  }

  hideSubtitleWidget() {
    var subtitleWidget = $('#video-subtitle-form-group');
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
      $('[name="ext[mediaSource]"]').val(null);
    });
  }

  displayFinishCondition(source) {
    console.log(source);
    if (source === 'self') {
      $("#finish-condition option[value=end]").removeAttr('disabled');
      $("#finish-condition option[value=end]").text('学习到最后');
    } else {
      $("#finish-condition option[value=end]").text('学习到最后(不支持网络视频)');
      $("#finish-condition option[value=end]").attr('disabled', 'disabled');
      $("#finish-condition option[value=time]").attr('selected', false);
      $("#finish-condition option[value=time]").attr('selected', true);
      $('.viewLength').removeClass('hidden');
      this.initStep3from();
      }
    }

    initStep2form() {
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
            trim: true,
            course_title: true,
          },
          minute: 'required unsigned_integer',
          second: 'required second_range',
          'ext[mediaSource]': 'required',
          'ext[finishDetail]': 'unsigned_integer'
        },
        messages: {
          minute: {
            required: '请输入时长',
          },
          second: {
            required: '请输入时长',
            second_range: '秒数只能在0-59之间',
          },
          'ext[mediaSource]': "请上传或选择%display%",
        }
      });
      $step2_form.data('validator', validator);
    }

    initStep3from() {
      var $step3_forom = $('#step3-form');
      var validator = $step3_forom.data('validator');
      $step3_forom.validate({
        rules: {
          'ext[finishDetail]': {
            required: true,
            positive_integer: true,
            max: 300,
            min: 1,
          }
        },
        messages: {
          'ext[finishDetail]': {
            required: '请输入时长',
          }
        }
      });
      $step3_forom.data('validator', validator);
    }

    autoValidatorLength() {
      $(".js-length").blur(function () {
        let validator = $("#step2-form").data('validator');
        if (validator && validator.form()) {
          const minute = parseInt($('#minute').val()) | 0;
          const second = parseInt($('#second').val()) | 0;
          $("#length").val(minute * 60 + second);
        }
      });
    }

    isInitStep3from() {
      // 完成条件是观看时长的情况
      if ($("#finish-condition").children('option:selected').val() === 'time') {
        $('.viewLength').removeClass('hidden');
        this.initStep3from();
      }

      $("#finish-condition").on('change', (event) => {
        if (event.target.value == 'time') {
          $('.viewLength').removeClass('hidden');
          this.initStep3from();
        } else {
          $('.viewLength').addClass('hidden');
          $('input[name="ext[finishDetail]"]').rules('remove')
        }
      })
    }

    initfileChooser() {
      const fileChooser = new FileChooser();
      //字幕组件
      const subtitleDialog = new SubtitleDialog('.js-subtitle-list');
      const onSelectFile = file => {
        this.displayFinishCondition(file.source);
        FileChooser.closeUI();
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
          $("#ext_mediaUri").val('');
        } else {
        $("#ext_mediaUri").val(file.uri);
        $("#ext_mediaId").val(0);
      }
      //渲染字幕
      subtitleDialog.render(file);
    };

    fileChooser.on('select', onSelectFile);
  }
}