webpackJsonp(["app/js/settings/user-profile/index"],[
/* 0 */
/***/ (function(module, exports) {

	import EsWebUploader from 'common/es-webuploader.js';
	import notify from 'common/notify';
	
	var editor = CKEDITOR.replace('profile_about', {
	  toolbar: 'Simple',
	  filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
	});
	
	var uploader = new EsWebUploader({
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
	    notify('success', Translator.trans('保存成功'));
	
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