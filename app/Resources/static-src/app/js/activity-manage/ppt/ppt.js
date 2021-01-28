import FileChooser from 'app/js/file-chooser/file-choose';
import { chooserUiOpen, chooserUiClose, showChooserType } from '../widget/chooser-ui.js';

export default class PPT {
  constructor() {
    this.$mediaId = $('[name="mediaId"]');
    this.validator3 = null;
    this.init();
  }
  init() {
    showChooserType(this.$mediaId);
    this.initStep2Form();
    this.initSelect();
    this.initFileChooser();
  }

  initStep2Form() {
    var $step2_form = $('#step2-form');
    var validator = $step2_form.data('validator');
    validator = $step2_form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        mediaId: 'required',
      },
      messages: {
        mediaId: {
          required: Translator.trans('activity.ppt_manage.media_error_hint')
        }
      }
    });
  }

  initStep3Form() {
    var $step3_form = $('#step3-form');
    this.validator3 = $step3_form.validate({
      rules: {
        finishDetail: {
          required: ()=> {
            return $('#condition-select').children('option:selected').val() === 'time';
          },
          positive_integer: true,
          max: 300,
          min: 1,
        },
      },
      messages: {
        finishDetail: {
          required: Translator.trans('activity.ppt_manage.finish_detail_required_error_hint'),
        },
      }
    });
    $step3_form.data('validator', this.validator3);
  }

  initFileChooser() {
    let fileChooser = new FileChooser();

    fileChooser.on('select', (file) => {
      chooserUiClose();
      this.$mediaId.val(file.id);
      $('#step2-form').valid();
      $('[name="media"]').val(JSON.stringify(file));
    });
  }

  initSelect() {
    let $select = $('#condition-select');
    if ($select.children('option:selected').val() === 'time') {
      this.initStep3Form();
    }

    $select.on('change', event => {
      let conditionsType = $(event.currentTarget).children('option:selected').val();
      let $conditionsDetail = $('#condition-group');
      if (conditionsType !== 'time') {
        $conditionsDetail.addClass('hidden');
        return;
      }else {
        $conditionsDetail.removeClass('hidden');
      }
      if(!this.validator3) {
        this.initStep3Form();
      }
    });
  }
}