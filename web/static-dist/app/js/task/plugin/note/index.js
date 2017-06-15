webpackJsonp(["app/js/task/plugin/note/index"],[
/* 0 */
/***/ (function(module, exports) {

	import notify from 'common/notify';
	import { saveRedmineLoading, saveRedmineSuccess } from '../save-redmine';
	var heigth = $('.js-sidebar-pane').height() - 175;
	var $content = $('#note-content-field');
	var lastNoteContent = void 0;
	var editor = CKEDITOR.replace('note-content-field', {
	  toolbar: 'Simple',
	  filebrowserImageUploadUrl: $content.data('imageUploadUrl'),
	  allowedContent: true,
	  height: heigth < 300 ? 200 : heigth
	});
	
	editor.on('change', function () {
	  $content.val(editor.getData());
	});
	
	$('#note-save-btn').click(function (event) {
	  var $btn = $(this);
	  event.preventDefault();
	  saveNote($btn);
	});
	
	setInterval(saveNote, 30000);
	
	function saveNote() {
	  var $btn = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	
	  if (!$.trim($content.val())) {
	    $btn ? notify('danger', '请输入笔记内容！') : '';
	    return;
	  }
	  var $form = $('#task-note-plugin-form');
	  var data = $form.serializeArray();
	  if (lastNoteContent === data[0].value) {
	    return;
	  }
	  saveRedmineLoading();
	  $btn ? $btn.attr('disabled', 'disabled') : "";
	  $.post($form.attr('action'), data).then(function (response) {
	    saveRedmineSuccess();
	    if ($btn) {
	      $btn.removeAttr('disabled');
	    }
	    lastNoteContent = data[0].value;
	  });
	}

/***/ })
]);