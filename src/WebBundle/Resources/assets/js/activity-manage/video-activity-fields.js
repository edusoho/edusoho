//import  FileChooser from '../../common/file-choose';
import  {materialLibChoose, videoImport, courseFileChoose} from '../../common/file-choose';
jQuery.validator.addMethod("unsigned_integer", function (value, element) {
    return this.optional(element) || /^([1-9]\d*|0)$/.test(value);
}, "时长必须为非负整数");

jQuery.validator.addMethod("second_range", function (value, element) {
    return this.optional(element) || /^([0-9]|[012345][0-9]|59)$/.test(value);
}, "秒数只能在0-59之间");

function _inItStep2form() {
    var $step1_form = $('#step2-form');
    var validator = $step1_form.validate({
        onkeyup: false,
        ignore: "",
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
    $step1_form.data('validator', validator);
}

_inItStep2form();
//
// const fileChooser = new FileChooser();
// fileChooser.initFileChooser();
// fileChooser.on('fileChooser:select1', action);
// fileChooser.on('fileChooser:select', action);


const action = data => {
    console.log('action triggered', data);
}



materialLibChoose.on('materialLibChoose:select', action);
videoImport.on('videoImportChoose:select', action);
courseFileChoose.on('courseFileChoose:select', action);
/*


 const materialLibChoose = new MaterialLibChoose($('#chooser-course-panel'));

 var fileImport = new FileImport($('#chooser-import-panel'));

 var courseFileChoose = new CourseFileChoose($('#chooser-material-panel'));

 */
