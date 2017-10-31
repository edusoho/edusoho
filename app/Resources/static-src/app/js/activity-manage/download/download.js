import FileChooser from '../../file-chooser/file-choose';
import notify from 'common/notify';
import {
  isEmpty
} from 'common/utils';
import {
  chooserUiOpen
} from '../widget/chooser-ui';

export default class DownLoad {
  constructor() {
    this.$form = $('#step2-form');
    this.validator2 = null;
    this.firstName = $('#title').val();
    this.media = {};
    this.materials = {};
    this.initStep2Form();
    this.bindEvent();
    this.initFileChooser();
  }

  initStep2Form() {
    let validator2 = this.$form.validate({
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
        link: Translator.trans("activity.download_manage.link_error_hint"),
        materials: Translator.trans('activity.download_manage.materials_error_hint')
      }
    });
  }

  bindEvent() {
    this.$form.on('click', '.js-btn-delete', (event) => this.deleteItem(event));
    this.$form.on('click', '.js-video-import', () => this.importLink());
    this.$form.on('click', '.js-add-file-list', () => this.addFile());
    this.$form.on('blur', '#title', (event) => this.changeTitle(event));
  }

  deleteItem(event) {
    let $parent = $(event.currentTarget).closest('li');
    let mediaId = $parent.data('id');
    let $materials = $('#materials');
    this.materials = isEmpty($materials.val()) ? {} : JSON.parse($materials.val());
    if (this.materials && this.materials[mediaId]) {
      delete this.materials[mediaId];
      $materials.val(JSON.stringify(this.materials));
    }
    if ($parent.siblings('li').length <= 0) {
      $materials.val('');
    }
    $parent.remove();
  }

  initFileChooser() {
    const fileSelect = (file) => {
      // 赋值给media上传的文件信息
      $('#media').val(JSON.stringify(file));
      chooserUiOpen();
      // 重置上传文件导致标题名称的改变
      $('#title').val(this.firstName);
      $('.js-current-file').text(file.name);
    }

    const fileChooser = new FileChooser();
    fileChooser.on('select', fileSelect);
  }

  changeTitle(event) {
    let $this = $(event.currentTarget);
    this.firstName = $this.val();
  }

  importLink() {
    let $link = $('#link');
    let $verifyLink = $('#verifyLink');
    if (this.$form.data('validator').valid() && $link.val().length > 0) {
      $verifyLink.val($link.val());
      $('.js-current-file').text($verifyLink.val());
    }
  }

  addLink() {
    let verifyLinkVal = $('#verifyLink').val();
    let data = {
      source: 'link',
      id: verifyLinkVal,
      name: verifyLinkVal,
      link: verifyLinkVal,
      summary: $('#file-summary').val(),
      size: 0
    }

    $('#media').val(JSON.stringify(data));
  }

  addFile() {
    let $media = $('#media');
    let $materials = $('#materials');
    let $successTipDom = $('.js-success-redmine');
    let $errorTipDom = $('.js-danger-redmine');

    const errorTip = 'activity.download_manage.materials_error_hint';
    const successTip = 'activity.download_manage.materials_add_success_hint';
    const existTip = 'activity.download_manage.materials_exist_error_hint';

    if ($('#verifyLink').val().length > 0) {
      this.addLink();
    }

    this.media = isEmpty($media.val()) ? {} : JSON.parse($media.val());
    this.materials = isEmpty($materials.val()) ? {} : JSON.parse($materials.val());

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

    if ($('.jq-validate-error:visible').length > 0) {
      this.$form.data('validator').form();
    }
  }

  checkExisted() {
    let flag = false;
    for (let item in this.materials) {
      if (this.materials[item].hasOwnProperty('link') && this.materials[item].link.length > 0) {
        if (this.materials[item].link === this.media.id) {
          flag = true;
        }
      } else {
        if (this.materials[item].name === this.media.name) {
          flag = true;
        }
      }

    }
    return flag;
  }

  showFile() {
    let item_tpl = '';
    if (this.media.link) {
      item_tpl = `
        <li class="download-item" data-id="${ this.media.link }">
          <a class="gray-primary" href="${ this.media.link }" target="_blank">${ this.media.summary ? this.media.summary : this.media.name }<span class="glyphicon glyphicon-new-window text-muted text-sm mlm" title="${ Translator.trans('activity.download_manage.materials_delete_btn')}"></span></a>
          <a class="gray-primary phm btn-delete js-btn-delete" href="javascript:;" data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="es-icon es-icon-delete"></i></a>
        </li>
      `;
    } else {
      item_tpl = `
        <li class="download-item" data-id="${ this.media.id }">
          <a class="gray-primary" href="/materiallib/${ this.media.id }/download">${ this.media.name }</a>
          <a class="gray-primary phm btn-delete js-btn-delete" href="javascript:;" data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="es-icon es-icon-delete"></i></a>
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
    this.media = {};
    $showDom.text(Translator.trans(trans)).show();
    setTimeout(function() {
      $showDom.slideUp();
    }, 3000);
  }

}