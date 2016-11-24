import FileChooser from '../file-chooser/file-choose';
jQuery.validator.addMethod("url", function (value, element) {
    return this.optional(element) || /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/.test(value);
}, "URL的格式不正确");

function _inItStep2form() {
    var $form = $('#step2-form');
    var validator = $form.validate({
        onkeyup: false,
        ignore: "",
        rules: {
            link: 'url'

        },
        messages: {
            link: "链接地址不正确"
        }
    });

    $form.data('validator', validator);
}

_inItStep2form();


$('#step2-form').on('click', '.close.delete-btn', function () {
    let $parent = $(this).parents('.list-group-item');
    let mediaId = $parent.data('id');
    let items = isEmpty($("#materials").val()) ? {} : JSON.parse($("#materials").val());
    if (items && items[mediaId]) {
        delete items[mediaId];
        $("#materials").val(JSON.stringify(items));
    }
    $parent.remove();
})

$('#step2-form').on('click', '.js-download-material-add', function () {
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
        return;
    }

    if (!isEmpty(items) && items[media.id]) {
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
        <li class="list-group-item clearfix" data-id="${media.link}">
          <button class="close delete-btn" type="button" title="{{'删除'|trans}}" data-url="">&times;</button>
            <a href="${ media.link }" target="_blank">${ media.name }</a>
            <span class="glyphicon glyphicon-new-window text-muted text-sm" title="{{'网络链接资料'|trans}}"></span>
        </li>
    `;
    } else {
        item_tpl = `
        <li class="list-group-item clearfix" data-id="${media.id}">
          <button class="close delete-btn" type="button" title="{{'删除'|trans}}" data-url="">&times;</button>
            <a href="${ media.id }">${ media.name }</a>
        </li>
    `;
    }
    $(".js-empty-list").addClass('hidden');
    $("#material-list").append(item_tpl);
    open();

});
setTimeout(function () {
    open();

}, 500)
function open() {
    var $parentiframe = $(window.parent.document).find('#task-manage-content-iframe');
    $('[data-role=placeholder]').empty();
    $('.file-chooser-bar').addClass('hidden');
    $('.file-chooser-main').removeClass('hidden');
    $parentiframe.height($parentiframe.contents().find('body').height());
}
function isEmpty(obj) {
    return obj == null || obj == "" || obj == undefined || Object.keys(obj).length == 0;
}
const fileSelect = file => {
    $("input[name=media]").val(JSON.stringify(file));
    console.log('action triggered', file);
}

const fileChooser = new FileChooser();

fileChooser.on('select', fileSelect);
