webpackJsonp(["app/js/classroom-manage/students-create/index"],[
/* 0 */
/***/ (function(module, exports) {

	import notify from 'common/notify';
	
	var $modal = $('#student-create-form').parents('.modal');
	var $form = $('#student-create-form');
	var $table = $('#course-student-list');
	var $btn = $("#student-create-form-submit");
	var validator = $form.validate({
	  onkeyup: false,
	  rules: {
	    queryfield: {
	      required: true,
	      remote: {
	        url: $('#student-nickname').data('url'),
	        type: 'get',
	        data: {
	          'value': function value() {
	            return $('#student-nickname').val();
	          }
	        }
	      }
	    },
	    remark: {
	      maxlength: 80
	    },
	    price: {
	      currency: true
	    }
	  },
	  messages: {
	    queryfield: {
	      remote: Translator.trans('请输入学员邮箱/手机号/用户名')
	    }
	  }
	});
	
	$btn.click(function () {
	  if (validator.form()) {
	    $btn.button('submiting').addClass('disabled');
	    $.post($form.attr('action'), $form.serialize(), function (html) {
	      $table.find('tr.empty').remove();
	      $(html).prependTo($table.find('tbody'));
	      $modal.modal('hide');
	      notify('success', Translator.trans('添加成功!'));
	      window.location.reload();
	    }).error(function () {
	      notify('danger', Translator.trans('添加失败!'));
	      $btn.button('reset').removeClass('disabled');
	    });
	  }
	});

/***/ })
]);