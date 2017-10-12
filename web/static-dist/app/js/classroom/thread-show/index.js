webpackJsonp(["app/js/classroom/thread-show/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _threadShow = __webpack_require__("29cf60bbbbb4e174f0f6");
	
	var _threadShow2 = _interopRequireDefault(_threadShow);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var threadShowWidget = new _threadShow2["default"]({
	  element: '.class-detail-content'
	});
	
	var $onlyTeacherBtnHtml = $('.js-only-teacher-div').html();
	$('.class-detail-content').find('.js-all-post-head').append($onlyTeacherBtnHtml);
	
	$('.class-detail-content').on('click', '.js-only-teacher', function () {
	  var $self = $(this);
	  var $filter = $self.hasClass('active') ? '' : '?adopted=1';
	  var $url = $self.data('url') + $filter;
	  document.location.href = $url;
	});
	
	var $userIds = '';
	$('.class-detail-content').find('.thread-post').each(function () {
	  $userIds += $(this).data('userId') + ',';
	});
	$userIds = $userIds.substring(0, $userIds.length - 1);
	$.get($('#isTeachersUrl').val() + '?ids=' + $userIds, function (ids) {
	  var $idArray = ids.split(',');
	  for (var i = 0; i < $idArray.length; i++) {
	    $('.class-detail-content').find('.user-id-' + $idArray[i]).each(function () {
	      $(this).addClass('teacher');
	    });
	  };
	});

/***/ }),

/***/ "29cf60bbbbb4e174f0f6":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	    value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var ThreadShowWidget = function () {
	    function ThreadShowWidget(prop) {
	        _classCallCheck(this, ThreadShowWidget);
	
	        this.ele = $(prop.element);
	        this.init();
	    }
	
	    _createClass(ThreadShowWidget, [{
	        key: 'init',
	        value: function init() {
	            this.initEvent();
	            if ($('[name=access-intercept-check]').length > 0) {
	                $.get($('[name=access-intercept-check]').val(), function (response) {
	                    if (response) {
	                        return;
	                    }
	
	                    $('.access-intercept-modal').modal('show');
	                }, 'json');
	            }
	            this.initPostForm();
	        }
	    }, {
	        key: 'initEvent',
	        value: function initEvent() {
	            var _this = this;
	
	            var $node = this.ele;
	
	            console.log($node);
	
	            $node.on('click', '.js-post-more', function (event) {
	                return _this.onClickPostMore(event);
	            });
	            $node.on('click', '.js-reply', function (event) {
	                return _this.onClickReply(event);
	            });
	            $node.on('click', '.js-post-delete', function (event) {
	                return _this.onPostDelete(event);
	            });
	            $node.on('click', '.js-post-up', function (event) {
	                return _this.onPostUp(event);
	            });
	            $node.on('click', '[data-role=confirm-btn]', function (event) {
	                return _this.onConfirmBtn(event);
	            });
	            $node.on('click', '.js-toggle-subpost-form', function (event) {
	                return _this.onClickToggleSubpostForm(event);
	            });
	            $node.on('click', '.js-event-cancel', function (event) {
	                return _this.onClickEventCancelBtn(event);
	            });
	            $node.on('click', '.thread-subpost-container .pagination a', function (event) {
	                return _this.onClickSubpost(event);
	            });
	        }
	    }, {
	        key: 'onClickPostMore',
	        value: function onClickPostMore(e) {
	            e.stopPropagation();
	            var $btn = $(e.currentTarget);
	            $btn.parents('.thread-subpost-moretext').addClass('hide');
	            $btn.parents('.thread-post').find('.thread-subpost').removeClass('hide');
	            $btn.parents('.thread-post').find('.pagination').removeClass('hide');
	        }
	    }, {
	        key: 'onClickReply',
	        value: function onClickReply(e) {
	            console.log('ok');
	            e.stopPropagation();
	            var $btn = $(e.currentTarget);
	            var inSubpost = $btn.parents('.thread-subpost-list').length > 0;
	            var $container = $btn.parents('.thread-post').find('.thread-subpost-container');
	            var $form = $container.find('.thread-subpost-form');
	            if (inSubpost) {
	                $form.removeClass('hide');
	                var text = Translator.trans('thread.post.reply') + ' @ ' + $btn.parents('.thread-post').data('authorName') + '： ';
	                $form.find('textarea').val(text).trigger('focus');
	            } else {
	                $container.toggleClass('hide');
	            }
	
	            if ($btn.html() == Translator.trans('thread.post.reply')) {
	                $btn.html(Translator.trans('thread.post.put_away'));
	            } else {
	                $btn.html(Translator.trans('thread.post.reply'));
	            }
	
	            this.initSubpostForm($form);
	        }
	    }, {
	        key: 'onPostDelete',
	        value: function onPostDelete(e) {
	            e.stopPropagation();
	            var $node = this.ele;
	            var $btn = $(e.currentTarget);
	            if (!confirm(Translator.trans('thread.post.delete_hint'))) {
	                return;
	            }
	            var inSubpost = $btn.parents('.thread-subpost-list').length > 0;
	
	            $.post($btn.data('url'), function () {
	                if (inSubpost) {
	                    var $subpostsNum = $btn.parents('.thread-post').find('.subposts-num');
	                    $subpostsNum.text(parseInt($subpostsNum.text()) - 1);
	                } else {
	                    $node.find('.thread-post-num').text(parseInt($node.find('.thread-post-num').text()) - 1);
	                }
	                $($btn.data('for')).remove();
	            });
	        }
	    }, {
	        key: 'onPostUp',
	        value: function onPostUp(e) {
	            e.stopPropagation();
	            var $btn = $(e.currentTarget);
	            $.post($btn.data('url'), function (response) {
	                if (response.status == 'ok') {
	                    $btn.find(".post-up-num").text(parseInt($btn.find(".post-up-num").text()) + 1);
	                } else if (response.status == 'votedError') {
	                    (0, _notify2["default"])('danger', Translator.trans('thread.post.like_hint'));
	                } else {
	                    (0, _notify2["default"])('danger', Translator.trans('thread.post.like_error_hint'));
	                }
	            }, 'json');
	        }
	    }, {
	        key: 'onConfirmBtn',
	        value: function onConfirmBtn(e) {
	            e.stopPropagation();
	            var $btn = $(e.currentTarget);
	            if (!confirm($btn.data('confirmMessage'))) {
	                return;
	            }
	            $.post($btn.data('url'), function () {
	                if ($btn.data('afterUrl')) {
	                    window.location.href = $btn.data('afterUrl');
	                    return;
	                }
	                window.location.reload();
	            });
	        }
	    }, {
	        key: 'onClickToggleSubpostForm',
	        value: function onClickToggleSubpostForm(e) {
	            e.stopPropagation();
	            var $btn = $(e.currentTarget);
	            var $form = $btn.parents('.thread-subpost-container').find('.thread-subpost-form');
	            $form.toggleClass('hide');
	            this.initSubpostForm($form);
	        }
	    }, {
	        key: 'onClickEventCancelBtn',
	        value: function onClickEventCancelBtn(e) {
	            $.post($(e.currentTarget).data('url'), function (result) {
	                window.location.reload();
	            });
	        }
	    }, {
	        key: 'onClickSubpost',
	        value: function onClickSubpost(e) {
	            e.preventDefault();
	            var $pageBtn = $(e.currentTarget);
	
	            $.post($pageBtn.attr('href'), function (result) {
	
	                var id = $pageBtn.parents(".thread-post").attr("id");
	                $("body,html").animate({
	                    scrollTop: $("#" + id).offset().top
	                }, 300), !1;
	
	                $pageBtn.closest('.thread-subpost-container .thread-subpost-content').html(result);
	            });
	        }
	    }, {
	        key: 'initPostForm',
	        value: function initPostForm() {
	            var $list = $('.thread-pripost-list');
	            var $form = $('#thread-post-form');
	            var that = this;
	
	            if ($form.length == 0) {
	                return;
	            }
	
	            var editor = null;
	            var $textarea = $form.find('textarea[name=content]');
	            if ($textarea.data('imageUploadUrl')) {
	                editor = CKEDITOR.replace($textarea.attr('id'), {
	                    toolbar: 'Thread',
	                    filebrowserImageUploadUrl: $textarea.data('imageUploadUrl')
	                });
	                editor.on('change', function () {
	                    $textarea.val(editor.getData());
	                });
	            }
	            var $btn = $form.find('[type=submit]');
	            $form.validate({
	                ajax: true,
	                currentDom: $btn,
	                rules: {
	                    content: 'required'
	                },
	                submitSuccess: function submitSuccess(response) {
	                    $btn.button('reset');
	                    if ($textarea.data('imageUploadUrl')) {
	                        $list.append(response);
	                        editor.setData('');
	                    } else {
	                        $list.prepend(response);
	                        $textarea.val('');
	                    }
	
	                    var pos = $list.find('li:last-child').offset();
	                    $('body').scrollTop(pos.top);
	                    $form.find('.thread-post-num').text(parseInt($form.find('.thread-post-num').text()) + 1);
	                    $list.find('li.empty').remove();
	                    $list.closest('.top-reply').removeClass('hidden');
	
	                    //清除附件
	                    $('.js-attachment-list').empty();
	                    $('.js-attachment-ids').val("");
	                    $('.js-upload-file').removeClass('hidden');
	                },
	                submitError: function submitError(data) {
	                    $btn.button('reset');
	                    data = $.parseJSON(data.responseText);
	                    if (data.error) {
	                        (0, _notify2["default"])('danger', data.error.message);
	                    } else {
	                        (0, _notify2["default"])('danger', Translator.trans('thread.post.reply_error_hint'));
	                    }
	                }
	            });
	        }
	    }, {
	        key: 'initSubpostForm',
	        value: function initSubpostForm($form) {
	            var $btn = $form.find('[type=submit]');
	            $form.validate({
	                ajax: true,
	                currentDom: $btn,
	                rules: {
	                    content: 'required'
	                },
	                submitSuccess: function submitSuccess(data) {
	                    if (data.error) {
	                        (0, _notify2["default"])('danger', data.error);
	                        return;
	                    }
	                    $btn.button('reset');
	                    $form.parents('.thread-subpost-container').find('.thread-subpost-list').append(data);
	                    $form.find('textarea').val('');
	                    var $subpostsNum = $form.parents('.thread-post').find('.subposts-num');
	                    $subpostsNum.text(parseInt($subpostsNum.text()) + 1);
	                    $subpostsNum.parent().removeClass('hide');
	                },
	                submitError: function submitError(data) {
	                    $btn.button('reset');
	                    data = $.parseJSON(data.responseText);
	                    if (data.error) {
	                        (0, _notify2["default"])('danger', data.error.message);
	                    } else {
	                        (0, _notify2["default"])('danger', Translator.trans('thread.post.reply_error_hint'));
	                    }
	                }
	            });
	        }
	    }, {
	        key: 'undelegateEvents',
	        value: function undelegateEvents(element, eventName) {
	            this.ele.off(element, eventName);
	        }
	    }]);
	
	    return ThreadShowWidget;
	}();
	
	exports["default"] = ThreadShowWidget;

/***/ })

});
//# sourceMappingURL=index.js.map