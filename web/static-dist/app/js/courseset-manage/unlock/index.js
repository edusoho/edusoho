webpackJsonp(["app/js/courseset-manage/unlock/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Unlock = function () {
		function Unlock() {
			_classCallCheck(this, Unlock);
	
			this.init();
		}
	
		_createClass(Unlock, [{
			key: 'init',
			value: function init() {
				$('#courseSync-btn').click(function () {
					var $form = $("#courseSync-form");
					$.post($form.attr('action'), $form.serialize(), function (resp) {
						console.log(resp);
						if (resp.success) {
							(0, _notify2["default"])('success', Translator.trans('course_set.manage.unlock_success_hint'));
							$('#modal').modal('hide');
							location.reload();
						} else {
							(0, _notify2["default"])('danger', Translator.trans('course_set.manage.unlock_failure_hint') + resp.message);
						}
					});
				});
			}
		}]);
	
		return Unlock;
	}();
	
	new Unlock();

/***/ })
]);
//# sourceMappingURL=index.js.map