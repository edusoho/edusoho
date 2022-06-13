import { initEditor } from 'app/js/activity-manage/editor.js';
import { isEmpty , arrayIndex } from 'common/utils';
import FileChooser from 'app/js/file-chooser/file-choose';
import { chooserUiOpen } from 'app/js/activity-manage/widget/chooser-ui';

export default class Live {
  constructor() {
    this.$form = $('#step2-form');
    this.$startTime = $('#startTime');
    this.media = {};
    this.materials = {};
    this._init();
  }

  _init() {
    this.initStep2Form();
    this._timePickerHide();
    this.initEvent();
    this.initFileChooser();
  }

  initStep2Form() {
    jQuery.validator.addMethod('show_overlap_time_error', function(value, element) {
      return this.optional(element) || !$(element).data('showError');
    }, '所选时间已经有直播了，请换个时间');
    let $step2_form = $('#step2-form');
    this.validator2 = $step2_form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          open_live_course_title: true,
        },
        startTime: {
          required: true,
          DateAndTime: true,
          after_now: true,
          es_remote: {
            type: 'post',
            data: {
              clientTime: function () {
                return $('[name=startTime]').val();
              }
            }
          }
        },
        length: {
          required: true,
          digits: true,
          max: 300,
          min: 1,
          show_overlap_time_error: true
        },
        remark: {
          maxlength: 1000
        },
      },
      messages: {
        startTime: {
          es_remote: Translator.trans('validate.after_now.message')
        }
      }
    });
    initEditor($('[name="remark"]'), this.validator2);
    $step2_form.data('validator', this.validator2);
    this.dateTimePicker(this.validator2);
    let that = this;
    $step2_form.find('#startTime').change(function () {
      that.checkOverlapTime($step2_form);
    });

    $step2_form.find('#length').change(function () {
      that.checkOverlapTime($step2_form);
    });
  }

  checkOverlapTime($step2_form) {
    if ($step2_form.find('#startTime').val() && $step2_form.find('#length').val()) {
      let showError = 1;
      let params = {
        startTime: $step2_form.find('#startTime').val(),
        length: $step2_form.find('#length').val(),
        mediaType: 'live'
      };
      $.ajax({
        url: $step2_form.find('#length').data('url'),
        async: false,
        type: 'POST',
        data: params,
        dataType: 'json',
        success: function (resp) {
          showError = resp.success === 0;
        }
      });

      $step2_form.find('#length').data('showError', showError);

    }
  }

  dateTimePicker(validator) {
    let $starttime = this.$startTime;
    $starttime.datetimepicker({
      format: 'yyyy-mm-dd hh:ii',
      language: document.documentElement.lang,
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    }).on('hide', () => {
      validator.form();
    });
    $starttime.datetimepicker('setStartDate', new Date());
  }

  _timePickerHide() {
    let $starttime = this.$startTime;
    parent.$('#modal', window.parent.document).on('afterNext', function() {
      $starttime.datetimepicker('hide');
    });
  }

  initEvent() {
    this.$form.on('click', '.js-btn-delete', (event) => this.deleteItem(event));

    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {
        valid: this.validator2.form(),
        data: window.ltc.getFormSerializeObject($('#step2-form'))
      });
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', {valid: this.validator2.form()});
    });
  }

  deleteItem(event) {
    let $parent = $(event.currentTarget).closest('li');
    let mediaId = $parent.data('id');
    const $materials = $('#materials');
    this.materials = isEmpty($materials.val()) ? {} : arrayIndex(JSON.parse($materials.val()), 'fileId');
    if (this.materials && this.materials[mediaId]) {
      delete this.materials[mediaId];
      $materials.val(JSON.stringify(this.materials));
    }
    if (!$parent.siblings('li').length) {
      $materials.val('');
    }
    $parent.remove();
  }

  addFile() {
    const $media = $('#media');
    const $materials = $('#materials');
    const $successTipDom = $('.js-success-redmine');
    const $errorTipDom = $('.js-danger-redmine');

    const errorTip = 'activity.download_manage.materials_error_hint';
    const successTip = 'activity.download_manage.materials_add_success_hint';
    const existTip = 'activity.download_manage.materials_exist_error_hint';

    let media = {};
    if (!isEmpty($media.val())) {
      media = JSON.parse($media.val());
      media.fileId = media.id;
      media.title = media.name;
    }

    this.media = media;
    console.log(this.media);
    this.materials = isEmpty($materials.val()) ? {} : arrayIndex(JSON.parse($materials.val()), 'fileId');

    if (isEmpty(this.media)) {
      this.showTip($successTipDom, $errorTipDom, errorTip);
      return;
    }

    if (!isEmpty(this.materials) && this.checkExisted()) {
      this.showTip($successTipDom, $errorTipDom, existTip);
      return;
    }

    this.media.summary = $('#file-summary').val();
    this.materials[this.media.id] = this.media;
    $materials.val(JSON.stringify(this.materials));

    this.showFile();

    this.showTip($errorTipDom, $successTipDom, successTip);

    if ($('.jq-validate-error:visible').length) {
      this.$form.data('validator').form();
    }
  }

  checkExisted() {
    for (let item in this.materials) {
      const materialsItem = this.materials[item];
      const checkFile = materialsItem.title === this.media.title;

      if (checkFile) {
        return true;
      }
    }
    return false;
  }

  showFile() {
    let item_tpl = '';
    item_tpl = `
      <li class="live-resource-item clearfix" data-id="${this.media.id}">
        <div class="live-resource-item-left pull-left text-overflow">
          <a class="gray-primary" href="/materiallib/${this.media.id}/download">${this.media.name}</a>
        </div>
        <a class="js-btn-delete" href="javascript:;" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="cd-icon cd-icon-close"></i></a>
      </li>
    `;

    $('#material-list').append(item_tpl);
    $('[data-toggle="tooltip"]').tooltip();
  }

  showTip($hideDom, $showDom, trans) {
    $hideDom.hide();
    $('.js-current-file').text('');
    $('#file-summary').val('');
    $('#media').val('');
    $showDom.text(Translator.trans(trans)).show();
    setTimeout(function() {
      $showDom.slideUp();
    }, 3000);
  }

  initFileChooser() {
    const fileSelect = (file) => {
      $('#media').val(JSON.stringify(file));
      this.addFile();
      chooserUiOpen();
      $('.js-current-file').text(file.name);
    };

    const fileChooser = new FileChooser();
    fileChooser.on('select', fileSelect);
  }
}

new Live();