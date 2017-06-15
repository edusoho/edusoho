webpackJsonp(["app/js/course-manage/live-replay/upload/index"],[
/* 0 */
/***/ (function(module, exports) {

	import FileChooser from 'app/js/file-chooser/file-choose';
	
	var fileChooser = new FileChooser();
	var $fileId = $('#material-file-chooser').find('[name=fileId]');
	fileChooser.on('select', function (file) {
	  $fileId.val(file.id);
	  FileChooser.closeUI();
	  $('.jq-validate-error').remove();
	});
	
	$('.js-choose-trigger').click(function (event) {
	  FileChooser.openUI();
	  $fileId.val('');
	});
	
	var $form = $('#replay-material-form');
	
	$form.validate({
	  rules: {
	    fileId: {
	      required: true
	    }
	  },
	  messages: {
	    fileId: '请上传录像文件'
	  }
	});

/***/ })
]);