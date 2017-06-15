webpackJsonp(["app/js/settings/setup/index"],[
/* 0 */
/***/ (function(module, exports) {

	import notify from 'common/notify';
	
	var $form = $('#setup-form');
	var $btn = $('.js-submit-setup-form');
	if ($form.length) {
	  var validator = $form.validate({
	    email: {
	      required: true,
	      es_email: true,
	      es_remote: {
	        type: 'POST'
	      }
	    },
	    nickname: {
	      required: true,
	      minlength: 4,
	      maxlength: 18,
	      nickname: true,
	      chinese_alphanumeric: true,
	      es_remote: {
	        type: 'get'
	      }
	    }
	  });
	
	  $btn.click(function () {
	    if (validator.form()) {
	      $btn.button('loadding');
	      $.post($form.attr('action'), $form.serialize(), function () {
	        notify('success', Translator.trans('设置帐号成功，正在跳转'));
	        window.location.href = $btn.data('goto');
	      }).error(function () {
	        $btn.button('reset');
	        notify('danger', Translator.trans('设置帐号失败，请重试'));
	      });
	    }
	  });
	}

/***/ })
]);