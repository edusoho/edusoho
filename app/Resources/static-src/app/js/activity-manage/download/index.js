import FileChooser from '../../file-chooser/file-choose';
import notify from 'common/notify';
import { chooserUiOpen, chooserUiClose, showChooserType } from '../widget/chooser-ui.js';
jQuery.validator.addMethod("url", function (value, element) {
  return this.optional(element) || /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/.test(value);
}, "URL的格式不正确");

// showChooserType($("input[name=mediaId]"));

_inItStep2form();

function _inItStep2form() {
  var $form = $('#step2-form');
  var validator = $form.validate({
    rules: {
      title: {
        required: true,
        maxlength: 50,
        trim: true,
      },
      link: 'url',
      materials: 'required',
    },
    messages: {
      link: "链接地址不正确",
      materials: '请上传或选择%display%'
    }
  });

  $form.data('validator', validator);
}

$('#step2-form').on('click', '.js-btn-delete', function () {
  let $parent = $(this).parents('li');
  let mediaId = $parent.data('id');
  let items = isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());
  if (items && items[mediaId]) {
    delete items[mediaId];
    $("#materials").val(JSON.stringify(items));
  }
  if ($parent.siblings('li').length <= 0) {
    $("#materials").val(null);
  }
  $parent.remove();
})

$('#step2-form').on('click', '.js-video-import', function () {
  addFile(false);
})

$('#step2-form').on('click', '.js-add-file-list', function () {
  addFile(true);
})

function addFile(addToList) {
  $('.js-success-redmine').hide();
  if (isEmpty($("#media").val()) && $("#step2-form").data('validator') && $("#step2-form").data('validator').valid() && $("#link").val().length > 0) {
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


  let media = isEmpty($("#media").val()) ? {} : JSON.parse($("#media").val());
  let items = isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());

  if (!isEmpty(items) && items[media.id]) {
    $('.js-danger-redmine').text(Translator.trans('该文件已添加，请重新选择')).show();
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

  if (addToList && isEmpty(media)) {
    $('.js-danger-redmine').text(Translator.trans('请上传或选择要添加的资料')).show();
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


  let item_tpl = '';
  if (media.link) {
    item_tpl = `
    <li class="download-item " data-id="${media.link}">
        <a class="gray-primary" href="${ media.link}" target="_blank">${media.name}</a>
        <a class="gray-primary phm btn-delete  js-btn-delete"  href="javascript:;"  data-url="" data-toggle="tooltip" data-placement="top" title="{{ '删除'|trans }}"><i class="es-icon es-icon-delete"></i></a>
        <span class="glyphicon glyphicon-new-window text-muted text-sm" title="${Translator.trans('删除')}"></span>
    </li>
  `;
  } else {
    item_tpl = `
    <li class="download-item " data-id="${media.id}">
      <a class="gray-primary" href="/materiallib/${ media.id}/download">${media.name}</a>
      <a class="gray-primary phm  btn-delete js-btn-delete" href="javascript:;"  data-url="" data-toggle="tooltip" data-placement="top" title="${Translator.trans('删除')}"><i class="es-icon es-icon-delete"></i></a>
    </li>
  `;
  }
  $("#material-list").append(item_tpl);
  $('[data-toggle="tooltip"]').tooltip();
  $('.file-browser-item').removeClass('active');
  $('.js-danger-redmine').hide();
  $('.js-success-redmine').text(Translator.trans('添加成功，可继续选择资料添加或点击下一步！')).show();
  setTimeout(function () {
    $('.js-success-redmine').slideUp();
  }, 3000);
  if ($('.jq-validate-error:visible').length > 0) {
    $("#step2-form").data('validator').form();
  }
}

_inItStep2form();


function isEmpty(obj) {
  return obj == null || obj == "" || obj == undefined || Object.keys(obj).length == 0;
}
const fileSelect = file => {
  $("input[name=media]").val(JSON.stringify(file));
  chooserUiOpen();
  addFile(false);
  $('.js-current-file').text(file.name);
}

const fileChooser = new FileChooser();

fileChooser.on('select', fileSelect);