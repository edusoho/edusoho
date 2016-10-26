// var editor = CKEDITOR.replace('text-content-field', {
//     toolbar: 'Full',
//     filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
//     filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
//     allowedContent: true,
//     height: 300
// });

parent.taskValidatorInit = function() {
    var $tabcontent = $('#tab-content');
    
    var $step2_form = $tabcontent.data('step2_form');
    var validator2 = $step2_form.validate({
        onkeyup: false,
        rules: {
          title: {
            required: true,
          },
          content: 'required',
        },
        messages: {
          title: "请输入标题",
          content:"请输入内容"
        }
    });
    $tabcontent.data('validator2',validator2);

    var $step3_form = $tabcontent.data('step2_form');
    var validator3 = $step3_form.validate({
        onkeyup: false,
        rules: {
          condition: {
            required: true,
          },
        },
        messages: {
          condition: "请输完成条件",
        }
    });
    $tabcontent.data('validator3',validator3);
}










 







