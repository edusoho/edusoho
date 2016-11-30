webpackJsonp(["web/courseset-manage/detail/index"],[
/* 0 */
/***/ function(module, exports) {

	'use strict';

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var DetailEditor = function () {
		function DetailEditor() {
			_classCallCheck(this, DetailEditor);

			this.init();
		}

		_createClass(DetailEditor, [{
			key: 'init',
			value: function init() {
				CKEDITOR.replace('summary', {
					allowedContent: true,
					toolbar: 'Detail',
					filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl')
				});

				$('#courseset-submit').click(function (evt) {
					$(evt.currentTarget).button('loading');
					$('#courseset-detail-form').submit();
				});
			}
		}]);

		return DetailEditor;
	}();

	new DetailEditor();

/***/ }
]);