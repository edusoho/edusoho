import FileChooser from '../../file-chooser/file-choose';
import notify from 'common/notify';
import {
  chooserUiOpen
} from '../widget/chooser-ui.js';

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
    console.log('删除:' + items);
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
      // 赋值给media上传的文件信息
      $("input[name=media]").val(JSON.stringify(file));
      chooserUiOpen();
      this.loadFile();
      // 上传文件的时候
      if (this.firstName) {
        $('#title').val(this.firstName);
      } else {
        $('#title').val('');
      }
      console.log('1111');
      $('.js-current-file').text(file.name);
    }

    const fileChooser = new FileChooser();

    fileChooser.on('select', fileSelect);
  }

  titleChange(event) {
    let $this = $(event.currentTarget);
    this.firstName = $this.val();
  }

  isEmpty(obj) {
    return obj == null || obj == "" || obj == undefined || Object.keys(obj).length == 0;
  }

  addLink() {
    // 链接的时候，通过js手动把链接的信息设置成数组的形式
    $("#verifyLink").val($("#link").val());
    let data = {
      source: 'link',
      id: $("#verifyLink").val(),
      name: $("#verifyLink").val(),
      link: $("#verifyLink").val(),
      summary: $("#file-summary").val(),
      size: 0
    }
    console.log(JSON.stringify(data));
    $('.js-current-file').text($("#verifyLink").val());
    // media用来记录单个文件
    console.log('换个链接名字')
    $("#media").val(JSON.stringify(data));
    this.media = JSON.parse($('#media').val());
    console.log(this.media);
  }


  loadFile() {
    this.media = this.isEmpty($("#media").val()) ? {} : JSON.parse($("#media").val());
    console.log(this.media);
    this.materials = this.isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());
    console.log(this.materials);
  }

  addFile(addToList) {
    //@TODO重构代码



    // console.log(addToList);
    // if (this.isEmpty($("#media").val()) && $("#step2-form").data('validator') && $("#step2-form").data('validator').valid() && $("#link").val().length > 0) {
    if (this.$form.data('validator').valid() && $("#link").val().length > 0) {

      // 链接的时候，通过js手动把链接的信息设置成数组的形式
      if (!addToList) {
        this.addLink();
      }
      // $('.js-current-file').text($("#verifyLink").val());
    }



    // if (!this.isEmpty(items) && items[media.name]) {
    //   $('.js-danger-redmine').text(Translator.trans('activity.download_manage.materials_exist_error_hint')).show();
    //   setTimeout(function() {
    //     $('.js-danger-redmine').slideUp();
    //   }, 3000);
    //   $('.js-current-file').text('');
    //   $("#media").val(null);
    //   media = null;
    //   return;
    // }

    if (!addToList) {
      return;
    }


    // 失败提示：请上传或选择资料
    if (addToList && this.isEmpty(this.media)) {
      this.addFailTip();
      return;
    }

    // 点击添加按钮，清空资料名称，将资料简介添加到media对象中
    $('.js-current-file').text('');
    this.media.summary = $("#file-summary").val();
    // 将media对象插入到materials数组中
    console.log(this.media.id);
    this.materials[this.media.id] = this.media;
    console.log(JSON.stringify(this.materials));
    // 插入到$("#materials")元素中
    $("#materials").val(JSON.stringify(this.materials));

    this.showFile();



    if (!this.firstName) {
      this.firstName = this.media.name;
      $('#title').val(this.firstName);
    }


    // $('.file-browser-item').removeClass('active');

    //添加成功

    this.addSuccessTip();


    if ($('.jq-validate-error:visible').length > 0) {

      // 点击 添加资料，并没有提交表单，url验证失败，返回false;
      this.$form.data('validator').form();
    }
  }


  //显示上传的文件或者链接名
  showFile() {

    let item_tpl = '';

    if (this.media.link) {
      item_tpl = `
        <li class="download-item " data-id="${ this.media.link }">
          <a class="gray-primary" href="${ this.media.link}" target="_blank">${ this.media.name}<span class="glyphicon glyphicon-new-window text-muted text-sm mlm" title="${ Translator.trans('activity.download_manage.materials_delete_btn')}"></span></a>
          <a class="gray-primary phm btn-delete js-btn-delete" href="javascript:;"  data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="es-icon es-icon-delete"></i></a>
        </li>
      `;
    } else {
      item_tpl = `
        <li class="download-item " data-id="${ this.media.id }">
          <a class="gray-primary" href="/materiallib/${ this.media.id }/download">${ this.media.name }</a>
          <a class="gray-primary phm btn-delete js-btn-delete" href="javascript:;"  data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('activity.download_manage.materials_delete_btn')}"><i class="es-icon es-icon-delete"></i></a>
        </li>
      `;
    }

    $("#material-list").append(item_tpl);
    $('[data-toggle="tooltip"]').tooltip();

    this.media = {};
    $('#media').val(this.media);
    console.log($('#media').val());
    $('#link').val('');
    $("#file-summary").val('');

  }


  addFailTip() {
    $('.js-danger-redmine').text(Translator.trans('activity.download_manage.materials_error_hint')).show();
    $('.js-current-file').text('');
    setTimeout(function() {
      $('.js-danger-redmine').slideUp();
    }, 3000);
  }

  addSuccessTip() {
    $('.js-success-redmine').text(Translator.trans('activity.download_manage.materials_add_success_hint')).show();
    setTimeout(function() {
      $('.js-success-redmine').slideUp();
    }, 3000);
  }

}