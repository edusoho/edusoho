webpackJsonp(["app/js/settings/approval/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$('#approval-form').validate({
	  rules: {
	    idcard: 'required idcardNumber',
	    truename: {
	      required: true,
	      chinese: true,
	      maxlength: 25,
	      minlength: 2
	    },
	    faceImg: 'required isImage limitSize',
	    backImg: 'required isImage limitSize'
	  },
	  messages: {
	    faceImg: {
	      required: Translator.trans('user.fields.idcard_front_placeholder')
	    },
	    backImg: {
	      required: Translator.trans('user.fields.idcard_back_placeholder')
	    }
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map