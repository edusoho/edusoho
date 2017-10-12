webpackJsonp(["app/js/message/conversation-list/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$("#site-navbar").find('.message-badge-container .badge').remove();
	
	$('.conversation-list').on('click', 'a', function (e) {
	  e.stopPropagation();
	});
	
	$('.conversation-list').on('click', '.media', function (e) {
	  window.location.href = $(this).data('url');
	});
	
	$('.conversation-list').on('click', '.delete-conversation-btn', function (e) {
	  if (!confirm(Translator.trans('confirm.private_message_delete.message'))) {
	    return false;
	  }
	
	  var $item = $(this).parents('.media');
	
	  $.post($(this).data('url'), function () {
	    $item.remove();
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map