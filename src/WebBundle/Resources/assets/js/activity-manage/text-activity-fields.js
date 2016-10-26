// var editor = CKEDITOR.replace('text-content-field', {
//     toolbar: 'Full',
//     filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
//     filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
//     allowedContent: true,
//     height: 300
// });

window.onload = ()=> {
  taskValidator();
}

function taskValidator() {
  var windowjQuery = parent.$;
  var $taskcontent = $(window.parent.document).find('#task-manage-content');
  var $step2_form = windowjQuery.data($taskcontent[0], 'step2_form');
  var $step3_form = windowjQuery.data($taskcontent[0], 'step3_form');

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

  windowjQuery.data($taskcontent[0], 'validator2',validator2);
  windowjQuery.data($taskcontent[0], 'validator3',validator3);
}










 







