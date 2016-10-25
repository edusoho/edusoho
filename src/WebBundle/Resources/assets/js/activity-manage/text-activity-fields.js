var editor = CKEDITOR.replace('text-content-field', {
    toolbar: 'Full',
    filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
    allowedContent: true,
    height: 300
});



var $step2_form = $('#task-type').data('step2_form');
console.log( $step2_form );
var validator2 = $step2_form.validate({
  onkeyup: false,
  focusCleanup: true,
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

var validatorArray2 =new Array();
validatorArray2.push($step2_form.find('input[name=title]'),$step2_form.find('textarea[name=content]'));
$('#task-type').data('validatorArray2',validatorArray2);
$('#task-type').data('validator2',validator2);

var $step3_form = $('#task-type').data('step3_form');
console.log($step3_form);
var validator3 = $step3_form.validate({
  onkeyup: false,
  focusCleanup: true,
  rules: {
    condition: {
      required: true,
    },
  },
  messages: {
    condition: "请输完成条件",
  }
});

var validatorArray3 =new Array();
validatorArray3.push($step3_form.find('input[name=condition]'));
$('#task-type').data('validatorArray3',validatorArray3);
$('#task-type').data('validator3',validator3);










 







