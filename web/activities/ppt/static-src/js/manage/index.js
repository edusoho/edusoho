import FileChooser from 'app/js/file-chooser/file-choose';
import { chooserUiClose, showChooserType } from 'app/js/activity-manage/widget/chooser-ui.js';
class PPT {
  constructor() {
    this.mediaId = $('#step2-form').data('mediaId');
    this.init();
  }
  init() {
    showChooserType(this.mediaId);
    this.initStep2Form();
    this.initSelect();
    this.initFileChooser();

    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validator.form(), data: this._serializeArray($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validator.form() });
    });
  }

  initStep2Form() {
    var $step2_form = $('#step2-form');
    this.validator = $step2_form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        media: 'required',
      },
      messages: {
        media: {
          required: Translator.trans('activity.ppt_manage.media_error_hint')
        }
      }
    });
  }

  initFileChooser() {
    let fileChooser = new FileChooser();

    fileChooser.on('select', (file) => {
      chooserUiClose();
      $('[name="media"]').val(JSON.stringify(file));
      $('#step2-form').valid();
    });
  }

  initSelect() {
    let $select = $('#condition-select');

    $select.on('change', event => {
      let conditionsType = $(event.currentTarget).children('option:selected').val();
      let $conditionsDetail = $('#condition-group');
      if (conditionsType !== 'time') {
        $conditionsDetail.addClass('hidden');
        return;
      }else {
        $conditionsDetail.removeClass('hidden');
      }
    });
  }

  _serializeArray($e) {
    let o = {};
    let a = $e.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  }
}

new PPT();