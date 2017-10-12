webpackJsonp(["app/js/settings/nickname/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var _nickname;
	
	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
	
	var validator = $('#nickname-form').validate({
		rules: {
			'nickname': (_nickname = {
				required: true,
				chinese_alphanumeric: true,
				byte_minlength: 4,
				byte_maxlength: 18,
				nickname: true
			}, _defineProperty(_nickname, 'chinese_alphanumeric', true), _defineProperty(_nickname, 'es_remote', {
				type: 'get'
			}), _nickname)
		}
	});
	
	$('#nickname-btn').on('click', function (event) {
		var $this = $(event.currentTarget);
		if (validator.form()) {
			$this.button('loading');
			$('#nickname-form').submit();
		}
	});

/***/ })
]);
//# sourceMappingURL=index.js.map