import FileChooser from 'app/js/file-chooser/replay-choose';

export default class Replay {
  constructor() {
    this.showChooseContent();
    this.initStep2form();
    this.autoValidatorLength();
    this.initFileChooser();
    this.categorySelect();
    this.initEvent();
  }

  initEvent() {
    if($('#origin_lesson_id').val() >0){
      $('#minute').attr('disabled',true);
      $('#second').attr('disabled',true);
    }
    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validate.form() });
    });

    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validate.form(), data:window.ltc.getFormSerializeObject($('#step2-form'))});
    });
  }

  categorySelect(){
    $('#categoryId').change(function(){
      let categoryId = $("#categoryId").find("option:selected").val();
      $("#categorySelect").val(categoryId);
    });
  }

  showChooseContent() {
    $('#iframe-content').on('click', '.js-choose-trigger', (event) => {
      FileChooser.openUI();
      $('#minute').attr('disabled',false);
      $('#second').attr('disabled',false);
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
        origin_lesson_id: 'required',
      },
      messages: {
        minute: {
          required: Translator.trans('activity.video_manage.length_required_error_hint'),
        },
        second: {
          required: Translator.trans('activity.video_manage.length_required_error_hint'),
          second_range: Translator.trans('activity.video_manage.length_required_error_hint'),
        },
        origin_lesson_id: Translator.trans('activity.replay_manage.replay_error_hint'),
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

        $('[name="origin_lesson_id"]').val(file.id);
      };
      placeMediaAttr(file);
      $('#step2-form').valid();
    };

    fileChooser.on('select', onSelectFile);
  }
}