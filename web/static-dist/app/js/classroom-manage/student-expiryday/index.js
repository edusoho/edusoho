webpackJsonp(["app/js/classroom-manage/student-expiryday/index"],[
/* 0 */
/***/ (function(module, exports) {

	import notify from 'common/notify';
	
	var $form = $('#expiryday-set-form');
	var validator = $form.validate({
	  rules: {
	    deadline: {
	      required: true,
	      date: true
	    }
	  }
	});
	
	$('#student-save').click(function (event) {
	  if (validator.form()) {
	    $(event.currentTarget).button('loadding');
	    $.post($form.attr('action'), $form.serialize(), function (response) {
	      if (response == true) {
	        notify('success', Translator.trans('修改成功'));
	      } else {
	        notify('danger', Translator.trans('修改失败'));
	      }
	      window.location.reload();
	    });
	  }
	});
	
	$("#student_deadline").datetimepicker({
	  language: 'zh-CN',
	  autoclose: true,
	  format: 'yyyy-mm-dd',
	  minView: 'month'
	});
	
	$("#student_deadline").datetimepicker('setStartDate', new Date());

/***/ })
]);