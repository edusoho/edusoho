webpackJsonp(["app/js/settings/user-profile/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _esWebuploader = __webpack_require__("0f84c916401868c4758e");
	
	var _esWebuploader2 = _interopRequireDefault(_esWebuploader);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var editor = CKEDITOR.replace('profile_about', {
	  toolbar: 'Simple',
	  filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
	});
	
	var uploader = new _esWebuploader2["default"]({
	  element: '#upload-picture-btn',
	  onUploadSuccess: function onUploadSuccess(file, response) {
	    var url = $("#upload-picture-btn").data("gotoUrl");
	    $.get(url, function (html) {
	      $("#modal").modal('show').html(html);
	    });
	  }
	});
	
	var validator = $('#user-profile-form').validate({
	  rules: {
	    'profile[about]': 'required',
	    'profile[title]': {
	      required: true,
	      chinese_limit: 24
	    },
	    'profile_avatar': 'required'
	  },
	  ajax: true,
	  submitSuccess: function submitSuccess(data) {
	    (0, _notify2["default"])('success', Translator.trans('settings.user_profile.save_success_hint'));
	
	    setTimeout(function () {
	      window.location.reload();
	    }, 1000);
	  }
	});
	
	$('#profile-save-btn').on('click', function (event) {
	  var $this = $(event.currentTarget);
	
	  if (editor.updateElement() && validator.form()) {
	    $this.button('loading');
	    $('#user-profile-form').submit();
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map