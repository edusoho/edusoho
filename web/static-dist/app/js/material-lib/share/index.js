webpackJsonp(["app/js/material-lib/share/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $from = $('#share-materials-form');
	
	var $input = $('#target-teachers-input');
	
	var data = $('#target-teachers-data').data('value');
	
	$input.select2({
	  multiple: true,
	  data: data
	});
	
	$input.on('change', function (data) {
	  $('.jq-validate-error').hide();
	  $('.has-error').removeClass('has-error');
	});
	
	$from.validate({
	  ajax: true,
	  currentDom: '#form-submit',
	  rules: {
	    targetUserIds: {
	      required: true,
	      visible_character: true
	    }
	  },
	  messages: {
	    targetUserIds: {
	      required: Translator.trans('material.share.teacher_nickname_label')
	    }
	  },
	  submitSuccess: function submitSuccess() {
	    $from.closest('.modal').modal('hide');
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map