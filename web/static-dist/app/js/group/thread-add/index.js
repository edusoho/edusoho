webpackJsonp(["app/js/group/thread-add/index"],[
/* 0 */
/***/ (function(module, exports) {

	import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
	
	var $userThreadForm = $('#user-thread-form');
	var groupThreadAddBtn = '#groupthread-save-btn';
	var threadContent = 'thread_content';
	
	new AttachmentActions($userThreadForm);
	var editor = CKEDITOR.replace(threadContent, {
	  toolbar: 'Thread',
	  filebrowserImageUploadUrl: $("#" + threadContent).data('imageUploadUrl'),
	  allowedContent: true,
	  height: 300
	});
	editor.on('change', function () {
	  $("#" + threadContent).val(editor.getData());
	});
	editor.on('blur', function () {
	  $("#" + threadContent).val(editor.getData());
	});
	
	var formValidator = $userThreadForm.validate({
	  currentDom: groupThreadAddBtn,
	  rules: {
	    'thread[title]': {
	      required: true,
	      minlength: 2,
	      maxlength: 100
	    },
	    'thread[content]': {
	      required: true,
	      minlength: 2
	    }
	  }
	});
	
	$(groupThreadAddBtn).click(function () {
	  if (formValidator.form()) {
	    $userThreadForm.submit();
	  }
	});

/***/ })
]);