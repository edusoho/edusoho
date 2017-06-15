webpackJsonp(["app/js/courseset-manage/unlock/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import notify from 'common/notify';
	
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
							notify('success', '解除同步成功！');
							$('#modal').modal('hide');
							location.reload();
						} else {
							notify('danger', '解除同步失败：' + resp.message);
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