import FileChooser from '../../file-chooser/file-choose';
import notify from 'common/notify';
import { chooserUiOpen } from '../widget/chooser-ui.js';

export default class DownLoad {
  constructor() {
    this.$form = $('#step2-form');
    this.validator2 = null;
    this.firstName = $('#title').val();
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
    this.$form.data('validator', validator2);
  }

  bindEvent() {
    this.$form.on('click', '.js-btn-delete', (event) => this.itemDelete(event));
    this.$form.on('click', '.js-video-import', () => this.videoImport(false));
    this.$form.on('click', '.js-add-file-list', () => this.addFileBtn(true));
    this.$form.on('blur', '#title', (event) => this.titleChange(event));
  }

  itemDelete(event) {
    let $parent = $(event.currentTarget).closest('li');
    let mediaId = $parent.data('id');
    let items = this.isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());
    if (items && items[mediaId]) {
      delete items[mediaId];
      $("#materials").val(JSON.stringify(items));
    }
    if ($parent.siblings('li').length <= 0) {
      $("#materials").val(null);
    }
    $parent.remove();
  }

  videoImport(state) {
    this.addFile(state);
  }

  addFileBtn(state) {
    this.addFile(state);
  }

  initFileChooser() {
    const fileSelect = file => {
      $("input[name=media]").val(JSON.stringify(file));
      chooserUiOpen();
      this.addFile(false);
      console.log(this.firstName);
      if (this.firstName) {
        $('#title').val(this.firstName);
      } else {
        $('#title').val('');
      }
      $('.js-current-file').text(file.name);
    }

    const fileChooser = new FileChooser();

    fileChooser.on('select', fileSelect);
  }

  titleChange(event) {
  
    let $this = $(event.currentTarget);
    this.firstName = $this.val();
    console.log(this.firstName);
  }

  isEmpty(obj) {
    return obj == null || obj == "" || obj == undefined || Object.keys(obj).length == 0;
  }

  addFile(addToList) {
    //@TODO重构代码
    $('.js-success-redmine').hide();
    if (this.isEmpty($("#media").val()) && $("#step2-form").data('validator') && $("#step2-form").data('validator').valid() && $("#link").val().length > 0) {
      if (!addToList) {
        $("#verifyLink").val($("#link").val());
      }
      let data = {
        source: 'link',
        id: $("#verifyLink").val(),
        name: $("#verifyLink").val(),
        link: $("#verifyLink").val(),
        summary: $("#file-summary").val(),
        size: 0
      };
      $('.js-current-file').text($("#verifyLink").val());
      $("#media").val(JSON.stringify(data));
    }


    let media = this.isEmpty($("#media").val()) ? {} : JSON.parse($("#media").val());
    let items = this.isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());

    if (!this.isEmpty(items) && items[media.id]) {
      $('.js-danger-redmine').text(Translator.trans('activity.download_manage.materials_exist_error_hint')).show();
      setTimeout(function () {
        $('.js-danger-redmine').slideUp();
      }, 3000);
      $('.js-current-file').text('');
      $("#media").val(null);
      media = null;
      return;
    }

    if (!addToList) {
      return;
    }

    if (addToList && this.isEmpty(media)) {
      $('.js-danger-redmine').text(Translator.trans('activity.download_manage.materials_error_hint')).show();
      $('.js-current-file').text('');
      setTimeout(function () {
        $('.js-danger-redmine').slideUp();
      }, 3000);
      return;
    }


    $('.js-current-file').text('');
    media.summary = $("#file-summary").val();
    items[media.id] = media;
    $("#materials").val(JSON.stringify(items));

    $("#media").val(null);
    $('#link').val(null);
    $("#file-summary").val(null);

    if (!this.firstName) {
      this.firstName = media.name;
      $('#title').val(media.name);
    }


    let item_tpl = '';
    if (media.link) {
      item_tpl = `
    <li class="download-item " data-id="${media.link}">
        <a class="gray-primary" href="${ media.link}" target="_blank">${media.name}</a>
        <a class="gray-primary phm btn-delete  js-btn-delete"  href="javascript:;"  data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="es-icon es-icon-delete"></i></a>
        <span class="glyphicon glyphicon-new-window text-muted text-sm" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"></span>
    </li>
  `;
    } else {
      item_tpl = `
    <li class="download-item " data-id="${media.id}">
      <a class="gray-primary" href="/materiallib/${ media.id}/download">${media.name}</a>
      <a class="gray-primary phm  btn-delete js-btn-delete" href="javascript:;"  data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="es-icon es-icon-delete"></i></a>
    </li>
  `;
    }
    $("#material-list").append(item_tpl);
    $('[data-toggle="tooltip"]').tooltip();
    $('.file-browser-item').removeClass('active');
    $('.js-danger-redmine').hide();
    $('.js-success-redmine').text(Translator.trans('activity.download_manage.materials_add_success_hint')).show();
    setTimeout(function () {
      $('.js-success-redmine').slideUp();
    }, 3000);
    if ($('.jq-validate-error:visible').length > 0) {
      $("#step2-form").data('validator').form();
    }
  }
}