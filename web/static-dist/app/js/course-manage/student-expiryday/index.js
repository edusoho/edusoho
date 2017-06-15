webpackJsonp(["app/js/course-manage/student-expiryday/index"],[
/* 0 */
/***/ (function(module, exports) {

	import notify from 'common/notify';
	
	var $modal = $('#expiryday-set-form').parents('.modal');
	var $form = $('#expiryday-set-form');
	
	var validator = $form.validate({
	  rules: {
	    expiryDay: {
	      positive_integer: true
	    }
	  }
	});
	
	$('.js-save-expiryday-set-form').click(function () {
	  if (validator.form()) {
	    $.post($form.attr('action'), $form.serialize(), function () {
	      var user_name = $('#submit').data('user');
	      notify('success', Translator.trans('增加%name%有效期操作成功!', { name: user_name }));
	      $modal.modal('hide');
	      window.location.reload();
	    }).error(function () {
	      var user_name = $('#submit').data('user');
	      notify('danger', Translator.trans('增加%name%有效期操作失败!', { name: user_name }));
	    });
	  }
	});

/***/ })
]);