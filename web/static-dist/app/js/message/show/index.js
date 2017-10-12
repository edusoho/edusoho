webpackJsonp(["app/js/message/show/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$('#message-reply-form').on('click', '#course-reply-btn', function (e) {
	  $("#course-reply-btn").addClass("disabled");
	  $("#course-reply-btn").attr("disabled", true);
	  if ($("#message_reply_content").val().length >= 500) {
	    (0, _notify2["default"])('danger', Translator.trans('notify.private_message_maxlength.message'));
	    return false;
	  }
	
	  if ($.trim($("#message_reply_content").val()).length == 0) {
	    (0, _notify2["default"])('danger', Translator.trans('不好意思，私信内容不允许为空!'));
	    return false;
	  }
	
	  $.post($("#message-reply-form").attr('action'), $("#message-reply-form").serialize(), function (response) {
	    $(".message-list").prepend(response.html);
	    $("#message_reply_content").val("");
	    $("#course-reply-btn").attr("disabled", false);
	  });
	
	  return false;
	});
	
	$('.message-list').on('click', '.delete-message', function (e) {
	
	  if ($(".message-list").find(".message-me").length == 1) {
	    if (!confirm(Translator.trans('confirm.last_private_message_delete.message'))) {
	      return false;
	    }
	  } else {
	    if (!confirm(Translator.trans('confirm.private_message_delete.message'))) {
	      return false;
	    }
	  }
	
	  var $item = $(this).parents('.media');
	  $.post($(this).data('url'), function () {
	    if ($(".message-list").find(".message-me").length == 1) {
	      window.location.href = $item.attr("parent-url");
	    }
	    $item.remove();
	  });
	});
	
	$('textarea').bind('input propertychange', function () {
	  if ($("#message_reply_content").val().length > 0) {
	    $("#course-reply-btn").removeClass("disabled");
	  } else {
	    $("#course-reply-btn").addClass("disabled");
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map