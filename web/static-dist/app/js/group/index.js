webpackJsonp(["app/js/group/index"],{

/***/ "d5fb0e67d2d4c1ebaaed":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var AttachmentActions = function () {
	  function AttachmentActions($ele) {
	    _classCallCheck(this, AttachmentActions);
	
	    this.$ele = $ele;
	    this.initEvent();
	  }
	
	  _createClass(AttachmentActions, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$ele.on('click', '[data-role="delte-item"]', function (event) {
	        return _this._deleteItem(event);
	      });
	    }
	  }, {
	    key: '_deleteItem',
	    value: function _deleteItem(event) {
	      var $target = $(event.currentTarget).button('loading');
	      $.post($target.data('url'), {}, function (response) {
	        if (response.msg == 'ok') {
	          (0, _notify2.default)('success', Translator.trans('删除成功！'));
	          $target.closest('.js-attachment-list').siblings('.js-upload-file').show();
	          $target.closest('.js-attachment-list').closest('div').siblings('[data-role="fileId"]').val('');
	          $target.closest('div').remove();
	          $('.js-upload-file').show();
	        }
	      });
	    }
	  }]);
	
	  return AttachmentActions;
	}();
	
	exports.default = AttachmentActions;

/***/ }),

/***/ "b7b955d31d3c6acc3b71":
/***/ (function(module, exports) {

	export var initEditor = function initEditor(options) {
	  var editor = CKEDITOR.replace(options.replace, {
	    toolbar: options.toolbar,
	    filebrowserImageUploadUrl: $("#" + options.replace).data('imageUploadUrl'),
	    allowedContent: true,
	    height: 300
	  });
	  editor.on('change', function () {
	    $("#" + options.replace).val(editor.getData());
	  });
	  editor.on('blur', function () {
	    $("#" + options.replace).val(editor.getData());
	  });
	};

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _threadOpreate = __webpack_require__("4833bf6727a52ba97d0c");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	(0, _threadOpreate.initThread)();
	(0, _threadOpreate.initThreadReplay)();
	
	//@TODO等待整理迁移
	function checkUrl(url) {
	  var hrefArray = url.split('#');
	  hrefArray = hrefArray[0].split('?');
	  return hrefArray[1];
	}
	
	var addBtnClicked = false;
	
	$('#add-btn').click(function () {
	  $(this).addClass('disabled');
	  var url = $(this).data('url');
	  $.post(url, function (data) {
	    if (data.status == 'success') {
	      window.location.reload();
	    } else {
	      Notify.danger(data.message);
	    }
	  });
	});
	
	if ($('#exit-btn').length > 0) {
	  $('#exit-btn').click(function () {
	    if (!confirm(Translator.trans('真的要退出该小组？您在该小组的信息将删除！'))) {
	      return false;
	    }
	
	    var url = $(this).data('url');
	    $.post(url, function (data) {
	      if (data.status == 'success') {
	        window.location.reload();
	      } else {
	        Notify.danger(data.message);
	      }
	    });
	  });
	}
	
	$('#thread-list').on('click', '.uncollect-btn, .collect-btn', function () {
	  var $this = $(this);
	
	  $.post($this.data('url'), function () {
	    $this.hide();
	    if ($this.hasClass('collect-btn')) {
	      $this.parent().find('.uncollect-btn').show();
	    } else {
	      $this.parent().find('.collect-btn').show();
	    }
	  });
	});
	
	$('.attach').tooltip();
	
	if ($('.group-post-list').length > 0) {
	  $('.group-post-list').on('click', '.li-reply', function () {
	    var postId = $(this).attr('postId');
	    var fromUserId = $(this).data('fromUserId');
	    $('#fromUserIdDiv').html('<input type="hidden" id="fromUserId" value="' + fromUserId + '">');
	    $('#li-' + postId).show();
	    $('#reply-content-' + postId).focus();
	    $('#reply-content-' + postId).val(Translator.trans('回复 ') + $(this).attr('postName') + ':');
	  });
	
	  $('.group-post-list').on('click', '.reply', function () {
	    var postId = $(this).attr('postId');
	    if ($(this).data('fromUserIdNosub') != '') {
	
	      var fromUserIdNosubVal = $(this).data('fromUserIdNosub');
	      $('#fromUserIdNoSubDiv').html('<input type="hidden" id="fromUserIdNosub" value="' + fromUserIdNosubVal + '">');
	      $('#fromUserIdDiv').html('');
	    }
	
	    ;
	    $(this).hide();
	    $('#unreply-' + postId).show();
	    $('.reply-' + postId).css('display', '');
	  });
	
	  $('.group-post-list').on('click', '.unreply', function () {
	    var postId = $(this).attr('postId');
	
	    $(this).hide();
	    $('#reply-' + postId).show();
	    $('.reply-' + postId).css('display', 'none');
	  });
	
	  $('.group-post-list').on('click', '.replyToo', function () {
	    var postId = $(this).attr('postId');
	    if ($(this).attr('data-status') == 'hidden') {
	      $(this).attr('data-status', '');
	      $('#li-' + postId).show();
	      $('#reply-content-' + postId).focus();
	      $('#reply-content-' + postId).val('');
	    } else {
	      $('#li-' + postId).hide();
	      $(this).attr('data-status', 'hidden');
	    }
	  });
	
	  $('.group-post-list').on('click', '.lookOver', function () {
	
	    var postId = $(this).attr('postId');
	    $('.li-reply-' + postId).css('display', '');
	    $('.lookOver-' + postId).hide();
	    $('.paginator-' + postId).css('display', '');
	  });
	
	  $('.group-post-list').on('click', '.postReply-page', function () {
	
	    var postId = $(this).attr('postId');
	    $.post($(this).data('url'), '', function (html) {
	
	      $('body,html').animate({
	        scrollTop: $('#post-' + postId).offset().top
	      }, 300), !1;
	
	      $('.reply-post-list-' + postId).replaceWith(html);
	    });
	  });
	}
	
	if ($('#hasAttach').length > 0) {
	
	  $('.ke-icon-accessory').addClass('ke-icon-accessory-red');
	}
	
	if ($('#post-action').length > 0) {
	
	  $('#post-action').on('click', '#closeThread', function () {
	
	    var $trigger = $(this);
	    if (!confirm($trigger.attr('title') + '？')) {
	      return false;
	    }
	
	    $.post($trigger.data('url'), function (data) {
	
	      window.location.href = data;
	    });
	  });
	
	  $('#post-action').on('click', '#elite,#stick,#cancelReward', function () {
	
	    var $trigger = $(this);
	
	    $.post($trigger.data('url'), function (data) {
	      window.location.href = data;
	    });
	  });
	}
	
	if ($('.actions').length > 0) {
	
	  $('.group-post-list').on('click', '.post-delete-btn,.post-adopt-btn', function () {
	
	    var $trigger = $(this);
	    if (!confirm($trigger.attr('title') + '？')) {
	      return false;
	    }
	
	    $.post($trigger.data('url'), function () {
	      window.location.reload();
	    });
	  });
	}

/***/ }),

/***/ "4833bf6727a52ba97d0c":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	exports.initThreadReplay = exports.initThread = undefined;
	
	var _editor = __webpack_require__("b7b955d31d3c6acc3b71");
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _attachmentActions = __webpack_require__("d5fb0e67d2d4c1ebaaed");
	
	var _attachmentActions2 = _interopRequireDefault(_attachmentActions);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
	
	var initThread = exports.initThread = function initThread() {
	  var btn = '#post-thread-btn';
	  var $form = $("#post-thread-form");
	  new _attachmentActions2.default($form);
	
	  if ($('#post_content').length) {
	    (0, _editor.initEditor)({
	      toolbar: 'Thread',
	      replace: 'post_content'
	    });
	  }
	
	  var formValidator = $form.validate({
	    currentDom: btn,
	    ajax: true,
	    rules: {
	      'content': {
	        required: true,
	        minlength: 2,
	        trim: true
	      }
	    },
	    submitError: function submitError(data) {
	      data = data.responseText;
	      data = $.parseJSON(data);
	      if (data.error) {
	        (0, _notify2.default)('danger', data.error.message);
	      } else {
	        (0, _notify2.default)('danger', Translator.trans('发表回复失败，请重试'));
	      }
	    },
	    submitSuccess: function submitSuccess(data) {
	      console.log(data);
	      if (data == "/login") {
	        window.location.href = url;
	        return;
	      }
	      // @TODO优化不刷新页面
	      window.location.reload();
	    }
	  });
	  console.log(formValidator);
	  $(btn).click(function () {
	    formValidator.form();
	  });
	};
	
	var initThreadReplay = exports.initThreadReplay = function initThreadReplay() {
	  var $forms = $('.thread-post-reply-form');
	  $forms.each(function () {
	    var $form = $(this);
	    var content = $form.find('textarea').attr('name');
	    var formValidator = $form.validate({
	      ignore: '',
	      rules: _defineProperty({}, '' + content, {
	        required: true,
	        minlength: 2,
	        trim: true
	      }),
	      submitHandler: function submitHandler(form) {
	        // @TODO优化全局的submitHandler方法，提交统一方式；
	        var $replyBtn = $(form).find('.reply-btn');
	        var postId = $replyBtn.attr('postId');
	        var fromUserIdVal = "";
	        if ($('#fromUserId').length > 0) {
	          fromUserIdVal = $('#fromUserId').val();
	        } else {
	          if ($('#fromUserIdNosub').length > 0) {
	            fromUserIdVal = $('#fromUserIdNosub').val();
	          } else {
	            fromUserIdVal = "";
	          }
	        }
	        $replyBtn.button('submiting').addClass('disabled');
	        console.log($(form).attr('action'));
	        console.log("content=" + $(form).find('textarea').val() + '&' + 'postId=' + postId + '&' + 'fromUserId=' + fromUserIdVal);
	        $.ajax({
	          url: $(form).attr('action'),
	          data: "content=" + $(form).find('textarea').val() + '&' + 'postId=' + postId + '&' + 'fromUserId=' + fromUserIdVal,
	          cache: false,
	          async: false,
	          type: "POST",
	          dataType: 'text',
	          success: function success(url) {
	            if (url == "/login") {
	              window.location.href = url;
	              return;
	            }
	            // @TODO优化不刷新页面
	            window.location.reload();
	          },
	          error: function error(data) {
	            data = $.parseJSON(data.responseText);
	            if (data.error) {
	              (0, _notify2.default)('danger', data.error.message);
	            } else {
	              (0, _notify2.default)('danger', Translator.trans('发表回复失败，请重试'));
	            }
	            $replyBtn.button('reset').removeClass('disabled');
	          }
	        });
	      }
	    });
	    $form.find('button').click(function (e) {
	      formValidator.form();
	    });
	  });
	};

/***/ })

});