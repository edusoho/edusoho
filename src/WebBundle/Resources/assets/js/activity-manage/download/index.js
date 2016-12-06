import FileChooser from '../../file-chooser/file-choose';
import notify from 'common/notify';
import {chooserUiOpen,chooserUiClose,showChooserType} from '../widget/chooser-ui.js';
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
    if($parent.siblings('li').length <= 0) {
      $('[name="mediaId"]').val(null);
    }
    $parent.remove();
})

$('#step2-form').on('click', '.js-video-import', function () {
  addfile();
})



function addfile() {
  if (isEmpty($("#media").val()) && $("#step2-form").data('validator') && $("#step2-form").data('validator').valid() && $("#link").val().length > 0) {
    let data = {
      source: 'link',
      id: $("#link").val(),
      name: $("#link").val(),
      link: $("#link").val(),
      size: 0
    };
    $("#media").val(JSON.stringify(data));
  }

  let media = isEmpty($("#media").val()) ? {} : JSON.parse($("#media").val());

  let items = isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());

  if (isEmpty(media)) {
    alert('add file first')
     notify('success', 'add file first');
    return;
  }

  if (!isEmpty(items) && items[media.id]) {
    notify('danger', '选择重复');
    $("#media").val(null);
    return;
  }
  items[media.id] = media;
  $("#materials").val(JSON.stringify(items));

  $("#media").val(null);
  $('#link').val(null);

  let item_tpl = '';
  if (media.link) {
    item_tpl = `
    <li class="download-item " data-id="${media.link}">
        <a href="${ media.link }" target="_blank">${ media.name }</a>
        <a class="btn btn-xs js-btn-delete"  title="{{'删除'|trans}}" data-url="">&times;</a>
        <span class="glyphicon glyphicon-new-window text-muted text-sm" title="{{'网络链接资料'|trans}}"></span>
    </li>
  `;
  } else {
    item_tpl = `
    <li class="download-item " data-id="${media.id}">
      <a href="${ media.id }">${ media.name }</a>
      <a class="btn btn-xs js-btn-delete" title="{{'删除'|trans}}" data-url="">&times;</a>
    </li>
  `;
  }
  $("#material-list").append(item_tpl);
  $("#step2-form").data('validator').valid();
}

_inItStep2form();


function isEmpty(obj) {
  return obj == null || obj == "" || obj == undefined || Object.keys(obj).length == 0;
}
const fileSelect = file => {
  $("input[name=media]").val(JSON.stringify(file));
  chooserUiOpen();
  addfile();
  console.log('action triggered', file);
}

const fileChooser = new FileChooser();

fileChooser.on('select', fileSelect);

// setTimeout(function () {
//     open();

// }, 500)
// function open() {
//     var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');
//     $('[data-role=placeholder]').empty();
//     $('.file-chooser-bar').addClass('hidden');
//     $('.file-chooser-main').removeClass('hidden');
//     $parentiframe.height($parentiframe.contents().find('body').height());
// }
// $('#step2-form').on('click', '.js-download-material-add', function () {
//     if (isEmpty($("#media").val()) && $("#step2-form").data('validator') && $("#step2-form").data('validator').valid() && $("#link").val().length > 0) {
//         let data = {
//             source: 'link',
//             id: $("#link").val(),
//             name: $("#link").val(),
//             link: $("#link").val(),
//             size: 0
//         };
//         $("#media").val(JSON.stringify(data));
//     }

//     let media = isEmpty($("#media").val()) ? {} : JSON.parse($("#media").val());

//     let items = isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());

//     if (isEmpty(media)) {
//         console.log('add file last');
//         alert('add file first')
//         return;
//     }

//     if (!isEmpty(items) && items[media.id]) {
//         console.log('ok');
//         notify('success', '选择重复');
//         $("#media").val(null);
//         return;
//     }

//     items[media.id] = media;
//     $("#materials").val(JSON.stringify(items));

//     $("#media").val(null);
//     $('#link').val(null);

//     let item_tpl = '';
//     if (media.link) {
//         item_tpl = `
//         <li class="list-group-item clearfix" data-id="${media.link}">
//           <button class="close js-btn-delete" type="button" title="{{'删除'|trans}}" data-url="">&times;</button>
//             <a href="${ media.link }" target="_blank">${ media.name }</a>
//             <span class="glyphicon glyphicon-new-window text-muted text-sm" title="{{'网络链接资料'|trans}}"></span>
//         </li>
//     `;
//     } else {
//         item_tpl = `
//         <li class="list-group-item clearfix" data-id="${media.id}">
//           <button class="close js-btn-delete" type="button" title="{{'删除'|trans}}" data-url="">&times;</button>
//             <a href="${ media.id }">${ media.name }</a>
//         </li>
//     `;
//     }
//     $(".js-empty-list").addClass('hidden');
//     $("#material-list").append(item_tpl);
//     open();

// });
