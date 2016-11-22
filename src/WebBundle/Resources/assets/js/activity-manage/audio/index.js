import  FileChooser from '../../../common/file-choose';
jQuery.validator.addMethod("unsigned_integer", function (value, element) {
    return this.optional(element) || /^([1-9]\d*|0)$/.test(value);
}, "时长必须为非负整数");

jQuery.validator.addMethod("second_range", function (value, element) {
    return this.optional(element) || /^([0-9]|[012345][0-9]|59)$/.test(value);
}, "秒数只能在0-59之间");

function _inItStep2form() {
    var $step1_form = $('#step2-form');
    var validator = $step1_form.data('validator', validator);
    validator = $step1_form.validate({
        rules: {
            content: 'required',
            minute: 'required unsigned_integer',
            second: 'second_range',
            media: 'required'
        },
        messages: {
            content: "请输入简介",
            minute: {
                required: '请输入时长',
                unsigned_integer: '时长必须为非负整数',
            },
            second: {
                unsigned_integer: '时长必须为非负整数',
            },
            media: "请选择或者上传视频"
        }
    });
    
}

_inItStep2form();


$(".js-length").blur(function () {
    let validator = $("#step2-form").data('validator');
    if (validator && validator.form()) {
        const minute = parseInt($('#minute').val())|0;
        const second = parseInt($('#second').val())|0;
        $("#length").val(minute * 60 + second);
    }
});



const fileChooser = new FileChooser();

const onSelectFile = file => {
    if (file.length) {
        let minute = parseInt(file.length / 60);
        let second = Math.round(file.length % 60);
        $("#minute").val(minute);
        $("#second").val(second);
    }
    $("#ext_mediaSource").val(file.source);
    if (file.source == 'self') {
        $("#ext_mediaId").val(file.id);
        $("#ext_mediaUri").val('');
    } else {
        $("#ext_mediaId").val('');
        $("#ext_mediaUri").val(file.uri);
    }
}

fileChooser.on('select', onSelectFile);

