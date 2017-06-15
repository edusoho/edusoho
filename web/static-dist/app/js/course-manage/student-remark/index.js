webpackJsonp(["app/js/course-manage/student-remark/index"],[
/* 0 */
/***/ (function(module, exports) {

	import notify from 'common/notify';
	var $modal = $('#student-remark-form').parents('.modal');
	var $form = $('#student-remark-form');
	
	var validator = $form.validate({
	  rules: {
	    remark: {
	      required: false,
	      maxlength: 80
	    }
	  },
	  messages: {
	    remark: {
	      maxlength: Translator.trans('备注字数不超过80')
	    }
	  }
	});
	
	$('.js-student-remark-save-btn').click(function (event) {
	  if (validator.form()) {
	    $(event.currentTarget).button('loadding');
	    $.post($form.attr('action'), $form.serialize(), function (html) {
	      var $html = $(html);
	      $('#' + $html.attr('id')).replaceWith($html);
	      $modal.modal('hide');
	      var user_name = $form.data('user');
	      notify('success', Translator.trans('备注%username%成功', { username: user_name }));
	    }).error(function () {
	      var user_name = $form.data('user');
	      notify('danger', Translator.trans('备注%username%失败，请重试！', { username: user_name }));
	    });
	  }
	});

/***/ })
]);