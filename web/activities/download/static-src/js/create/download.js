import { isEmpty , arrayIndex } from 'common/utils';
import FileChooser from 'app/js/file-chooser/file-choose';
import { chooserUiOpen } from 'app/js/activity-manage/widget/chooser-ui';

export default class DownLoad {
  constructor() {
    this.$form = $('#step2-form');
    this.firstName = $('#title').val();
    this.media = {};
    this.materials = {};
    this.initStep2Form();
    this.initEvent();
    this.initFileChooser();
  }

  initStep2Form() {
    this.validator2 = this.$form.validate({
      rules: {
        title: {
          required: true,
          maxlength: 50,
          trim: true,
          course_title: true,
        },
        link: 'url',
        materials: 'required',
      },
      messages: {
        link: Translator.trans('activity.download_manage.link_error_hint'),
        materials: Translator.trans('activity.download_manage.materials_error_hint')
      }
    });
  }

  initEvent() {
    this.$form.on('click', '.js-btn-delete', (event) => this.deleteItem(event));
    this.$form.on('click', '.js-video-import', () => this.importLink());
    this.$form.on('click', '.js-add-file-list', () => this.addFile());
    this.$form.on('blur', '#title', (event) => this.changeTitle(event));
    this.$form.on('blur', '.js-summary-value', (event) => this.changeSummary(event));

    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {valid:this.validator2.form(), data:window.ltc.getFormSerializeObject($('#step2-form'))});
    });
      
    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validator2.form() });
    });
  }

  deleteItem(event) {
    let $parent = $(event.currentTarget).closest('li');
    let mediaId = $parent.data('id');
    const $materials = $('#materials');
    this.materials = isEmpty($materials.val()) ? {} :  arrayIndex(JSON.parse($materials.val()), 'fileId');
    if (this.materials && this.materials[mediaId]) {
      delete this.materials[mediaId];
      $materials.val(JSON.stringify(this.materials));
    }
    if (!$parent.siblings('li').length) {
      $materials.val('');
    }
    $parent.remove();
  }

  initFileChooser() {
    let self = this;
    $('#media').on('click', function (){
      self.addFile();
    });
    const fileSelect = (file) => {
      $('#media').val(JSON.stringify(file));
      $('#media').click();
      chooserUiOpen();
      $('#title').val(this.firstName);
      $('.js-current-file').text(file.name);

    };

    const fileChooser = new FileChooser();
    fileChooser.on('select', fileSelect);
  }

  changeTitle(event) {
    let $this = $(event.currentTarget);
    this.firstName = $this.val();
  }

  importLink() {
    const $link = $('#link');
    const $verifyLink = $('#verifyLink');
    if (this.$form.data('validator').valid() && $link.val()) {
      $verifyLink.val($link.val());
    } else {
      $link.val('');
      $verifyLink.val('');
    }
    $('.js-current-file').text($verifyLink.val());
  }

  addLink() {
    let verifyLinkVal = $('#verifyLink').val();
    const data = {
      source: 'link',
      id: verifyLinkVal,
      name: verifyLinkVal,
      link: verifyLinkVal,
      summary: $('#file-summary').val(),
      size: 0
    };

    $('#media').val(JSON.stringify(data));
  }

  addFile() {
    const $media = $('#media');
    const $materials = $('#materials');
    const $successTipDom = $('.js-success-redmine');
    const $errorTipDom = $('.js-danger-redmine');

    const errorTip = 'activity.download_manage.materials_error_hint';
    const successTip = 'activity.download_manage.materials_add_success_hint';
    const existTip = 'activity.download_manage.materials_exist_error_hint';

    if ($('#verifyLink').val()) {
      this.addLink();
    }
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

    if (!this.firstName) {
      this.firstName = this.media.name;
      $('#title').val(this.firstName);
    }

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
      const checkLink = materialsItem.link && (materialsItem.link === this.media.id);

      if (checkFile || checkLink) {
        return true;
      }
    }
    return false;
  }

  showFile() {
    let item_tpl = '';
    if (this.media.link) {
      item_tpl = `
        <li class="download-item clearfix" data-id="${ this.media.link }">
          <div class="download-item-left pull-left text-overflow">
            <a href="${ this.media.link }" target="_blank">${ this.media.summary ? this.media.summary : this.media.name }</a>
            <span class="glyphicon glyphicon-new-window color-gray text-sm"></span>
          </div>
          <div class="download-item-right pull-left">
            <label>${Translator.trans('activity.summary')}：</label>
            <input class="js-summary-value" data-id="${this.media.id}" placeholder="${Translator.trans('activity.summary.placeholder')}" value="">
          </div>
          <a class="js-btn-delete" href="javascript:;" data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="cd-icon cd-icon-close"></i></a>
        </li>
      `;
    } else {
      item_tpl = `
        <li class="download-item clearfix" data-id="${ this.media.id }">
          <div class="download-item-left pull-left text-overflow">
            <a class="gray-primary" href="/materiallib/${ this.media.id }/download">${ this.media.name }</a>
          </div>
          <div class="download-item-right pull-left">
            <label>${Translator.trans('activity.summary')}：</label>
            <input class="js-summary-value" data-id="${this.media.id}" placeholder="${Translator.trans('activity.summary.placeholder')}" value="">
          </div>
          <a class="js-btn-delete" href="javascript:;" data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="cd-icon cd-icon-close"></i></a>
        </li>
      `;
    }

    $('#material-list').append(item_tpl);
    $('[data-toggle="tooltip"]').tooltip();
  }

  showTip($hideDom, $showDom, trans) {
    $hideDom.hide();
    $('.js-current-file').text('');
    $('#link').val('');
    $('#verifyLink').val('');
    $('#file-summary').val('');
    $('#media').val('');
    $showDom.text(Translator.trans(trans)).show();
    setTimeout(function() {
      $showDom.slideUp();
    }, 3000);
  }

  changeSummary(event) {
    let $this = $(event.currentTarget);
    const $materials = $('#materials');
    const materials = isEmpty($materials.val()) ? {} : arrayIndex(JSON.parse($materials.val()), 'fileId');
    materials[$this.data('id')].summary = $this.val();
    $materials.val(JSON.stringify(materials));
  }
}