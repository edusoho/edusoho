webpackJsonp(["app/js/main"],{

/***/ "e07fd113971ddccb226d":
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
	
	var RewardPointNotify = function () {
	  function RewardPointNotify() {
	    _classCallCheck(this, RewardPointNotify);
	
	    this.STORAGE_NAME = 'reward-point-notify-queue';
	
	    this.storage = window.localStorage;
	    this.init();
	  }
	
	  _createClass(RewardPointNotify, [{
	    key: 'init',
	    value: function init() {
	      var storageStr = this.storage.getItem(this.STORAGE_NAME);
	      if (!storageStr) {
	        this.stack = [];
	      } else {
	        this.stack = JSON.parse(storageStr);
	      }
	    }
	  }, {
	    key: 'display',
	    value: function display() {
	
	      if (this.stack.length > 0) {
	        var msg = this.stack.pop();
	        (0, _notify2["default"])('success', decodeURIComponent(msg));
	        this.store();
	      }
	    }
	  }, {
	    key: 'store',
	    value: function store() {
	      this.storage.setItem(this.STORAGE_NAME, JSON.stringify(this.stack));
	    }
	  }, {
	    key: 'push',
	    value: function push(msg) {
	      if (msg) {
	        this.stack.push(msg);
	        this.store();
	      }
	    }
	  }, {
	    key: 'size',
	    value: function size() {
	      return this.stack.size();
	    }
	  }]);
	
	  return RewardPointNotify;
	}();
	
	exports["default"] = RewardPointNotify;

/***/ }),

/***/ "ee19a46ef43088c77962":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("210ef5d7199861362f9b");
	
	(function ($) {
	  $.fn.lavaLamp = function (o) {
	    o = $.extend({ fx: "easein", speed: 200, click: function click() {} }, o || {});return this.each(function () {
	      var b = $(this),
	          noop = function noop() {},
	          $back = $('<li class="highlight"></li>').appendTo(b),
	          $li = $("li", this),
	          curr = $("li.active", this)[0] || $($li[0]).addClass("active")[0];$li.not(".highlight").hover(function () {
	        move(this);
	      }, noop);$(this).hover(noop, function () {
	        move(curr);
	      });$li.click(function (e) {
	        setCurr(this);return o.click.apply(this, [e, this]);
	      });setCurr(curr);function setCurr(a) {
	        $back.css({ "left": a.offsetLeft + "px", "width": a.offsetWidth + "px" });curr = a;
	      };function move(a) {
	        $back.each(function () {
	          $(this).dequeue();
	        }).animate({ width: a.offsetWidth, left: a.offsetLeft }, o.speed, o.fx);
	      }
	    });
	  };
	})(jQuery);
	/* eslint-enable */
	/* eslint-disable */

/***/ }),

/***/ "227ff5f887a3789f9963":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	if (!navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)) {
	  bindCardEvent('.js-card-content');
	  $(".js-user-card").on("mouseenter", function () {
	
	    var _this = $(this);
	    var userId = _this.data('userId');
	    var loadingHtml = '<div class="card-body"><div class="card-loader"><span class="loader-inner"><span></span><span></span><span></span></span>' + Translator.trans('user.card_load_hint') + '</div>';
	
	    var timer = setTimeout(function () {
	
	      function callback(html) {
	        _this.popover('destroy');
	
	        setTimeout(function () {
	          if ($('#user-card-' + userId).length == 0) {
	            if ($('body').find('#user-card-store').length > 0) {
	              $('#user-card-store').append(html);
	            } else {
	              $('body').append('<div id="user-card-store" class="hidden"></div>');
	              $('#user-card-store').append(html);
	            }
	          }
	
	          _this.popover({
	            trigger: 'manual',
	            placement: 'auto top',
	            html: 'true',
	            content: function content() {
	              return html;
	            },
	            template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
	            container: 'body',
	            animation: true
	          });
	          _this.popover("show");
	
	          _this.data('popover', true);
	
	          $(".popover").on("mouseleave", function () {
	            _this.popover('hide');
	          });
	        }, 200);
	      }
	
	      if ($('#user-card-' + userId).length == 0 || !_this.data('popover')) {
	        var beforeSend = function beforeSend() {
	
	          _this.popover({
	            trigger: 'manual',
	            placement: 'auto top',
	            html: 'true',
	            content: function content() {
	              return loadingHtml;
	            },
	            template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
	            container: 'body',
	            animation: true
	          });
	
	          // _this.popover("show");
	        };
	
	        ;
	
	        $.ajax({
	          type: "GET",
	          url: _this.data('cardUrl'),
	          dataType: "html",
	          beforeSend: beforeSend,
	          success: callback
	        });
	      } else {
	        var html = $('#user-card-' + userId).clone();
	        callback(html);
	        // _this.popover("show");
	      }
	
	      bindMsgBtn($('.es-card'), _this);
	    }, 100);
	
	    _this.data('timerId', timer);
	  }).on("mouseleave", function () {
	
	    var _this = $(this);
	
	    setTimeout(function () {
	
	      if (!$(".popover:hover").length) {
	
	        _this.popover("hide");
	      }
	    }, 100);
	
	    clearTimeout(_this.data('timerId'));
	  });
	}
	
	function bindCardEvent(selector) {
	  $('body').on('click', '.js-card-content .follow-btn', function () {
	    var $btn = $(this);
	    var loggedin = $btn.data('loggedin');
	    if (loggedin == "1") {
	      showUnfollowBtn($btn);
	    }
	    $.post($btn.data('url'));
	  }).on('click', '.js-card-content .unfollow-btn', function () {
	    var $btn = $(this);
	    showFollowBtn($btn);
	    $.post($btn.data('url'));
	  });
	}
	
	function bindMsgBtn($card, self) {
	  $card.on('click', '.direct-message-btn', function () {
	    $(self).popover('hide');
	  });
	}
	
	function showFollowBtn($btn) {
	  $btn.hide();
	  $btn.siblings('.follow-btn').show();
	  var $actualCard = $('#user-card-' + $btn.closest('.js-card-content').data('userId'));
	  $actualCard.find('.unfollow-btn').hide();
	  $actualCard.find('.follow-btn').show();
	}
	
	function showUnfollowBtn($btn) {
	  $btn.hide();
	  $btn.siblings('.unfollow-btn').show();
	  var $actualCard = $('#user-card-' + $btn.closest('.js-card-content').data('userId'));
	  $actualCard.find('.follow-btn').hide();
	  $actualCard.find('.unfollow-btn').show();
	}

/***/ }),

/***/ "4d9b0dab3f4f00038468":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _radio = __webpack_require__("4ed97247d4dc16a650a7");
	
	var _radio2 = _interopRequireDefault(_radio);
	
	var _confirm = __webpack_require__("5a23aebcc376b74ba5b0");
	
	var _confirm2 = _interopRequireDefault(_confirm);
	
	__webpack_require__("bc0db7ae498f28b1c7b4");
	
	__webpack_require__("90ed575288b0bb9908a4");
	
	__webpack_require__("98da90a6b03c53c65408");
	
	__webpack_require__("9d0c73806de237279c58");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var codeAgesDisign = {
	  radio: _radio2["default"],
	  confirm: _confirm2["default"]
	};
	
	window.cd = codeAgesDisign;

/***/ }),

/***/ "5a23aebcc376b74ba5b0":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Confirm = function () {
	  function Confirm(props) {
	    _classCallCheck(this, Confirm);
	
	    this.title = props.title || '';
	    this.content = props.content || '';
	    this.confirmText = props.confirmText || Translator.trans('site.confirm');
	    this.cancelText = props.cancelText || Translator.trans('site.close');
	
	    this.confirmClass = props.confirmClass || 'btn cd-btn cd-btn-flat-danger cd-btn-lg';
	    this.cancelClass = props.cancelClass || 'btn cd-btn cd-btn-flat-default cd-btn-lg';
	    this.dialogClass = props.dialogClass || 'cd-modal-dialog cd-modal-dialog-sm';
	
	    this.confirm = props.confirm || this.confirm;
	
	    this.confirmType = props.confirmType || '', this.confirmUrl = props.confirmUrl || '', this.ajax = props.ajax;
	
	    this.init();
	  }
	
	  _createClass(Confirm, [{
	    key: 'init',
	    value: function init() {
	      var html = this.template();
	      var $modal = $(html);
	
	      this.initEvent($modal);
	
	      $('body').append($modal);
	      $modal.modal({
	        backdrop: 'static',
	        keyboard: false,
	        show: true
	      });
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent($modal) {
	      var _this = this;
	
	      $modal.on('hidden.bs.modal', function () {
	        $modal.remove();
	      });
	
	      $modal.on('click', '[data-toggle="cd-confirm-btn"]', function (event) {
	        return _this.confirm(event, $modal);
	      });
	    }
	  }, {
	    key: 'confirm',
	    value: function confirm(event, $modal) {
	      var $this = $(event.currentTarget);
	      var url = $this.data('url');
	
	      if (!url) {
	        return;
	      }
	
	      if (this.confirmType) {
	        var promise = $.ajax({
	          type: this.confirmType,
	          url: url
	        }).always(function () {
	          $modal.modal('hide');
	        });
	
	        this.ajax && this.ajax(promise);
	      } else {
	        window.location = url;
	      }
	    }
	  }, {
	    key: 'template',
	    value: function template() {
	      var modalHeader = this.title ? '\n      <div class="modal-header">\n        <h4 class="modal-title">' + this.title + '</h4>\n      </div>\n    ' : '';
	
	      var modalBody = '\n      <div class="modal-body">\n        <div class="cd-pb24 cd-text-gray-dark">\n          ' + this.content + '\n        </div>\n      </div>\n    ';
	
	      var confirmBtn = '\n      <button class="' + this.confirmClass + '" type="button" data-toggle="cd-confirm-btn" data-url="' + this.confirmUrl + '">\n        ' + this.confirmText + '\n      </button>\n    ';
	
	      var modalFooter = '\n      <div class="modal-footer">\n        <button class="' + this.cancelClass + '" type="button" data-dismiss="modal">\n          ' + this.cancelText + '\n        </button>\n        ' + confirmBtn + '\n      </div>\n    ';
	
	      return '\n      <div class="modal fade">\n        <div class="modal-dialog ' + this.dialogClass + '">\n          <div class="modal-content">\n            ' + modalHeader + '\n            ' + modalBody + '\n            ' + modalFooter + '\n          </div>\n        </div>\n      </div>\n    ';
	    }
	  }]);
	
	  return Confirm;
	}();
	
	function confirm(props) {
	  return new Confirm(props);
	}
	
	exports["default"] = confirm;

/***/ }),

/***/ "9d0c73806de237279c58":
/***/ (function(module, exports) {

	'use strict';
	
	(function ($) {
	  $(document).on('click.cd.pic.review', '[data-toggle="pic-review"]', function () {
	    var picUrl = $(this).data('url');
	    window.open(picUrl);
	  });
	})(jQuery);

/***/ }),

/***/ "98da90a6b03c53c65408":
/***/ (function(module, exports) {

	'use strict';
	
	var template = function template() {
	  var loadingClass = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	
	  return '<div class="cd-loading ' + loadingClass + '">\n            <div class="loading-content">\n              <div></div>\n              <div></div>\n              <div></div>\n            </div>\n          </div>';
	};
	
	$(document).ajaxSend(function (a, b, c) {
	
	  var url = c.url;
	
	  var $dom = $('[data-url="' + url + '"]');
	
	  if (!$dom.data('loading')) {
	    return;
	  };
	
	  var loading = void 0;
	  if ($dom.data('loading-class')) {
	    loading = template($dom.data('loading-class'));
	  } else {
	    loading = template();
	  }
	
	  var loadingBox = $($dom.data('target') || $dom);
	  loadingBox.append(loading);
	});

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _swiper = __webpack_require__("370d3340744bf261df0e");
	
	var _swiper2 = _interopRequireDefault(_swiper);
	
	__webpack_require__("dc0cc38836f18fdb00b4");
	
	__webpack_require__("227ff5f887a3789f9963");
	
	var _rewardPointNotify = __webpack_require__("e07fd113971ddccb226d");
	
	var _rewardPointNotify2 = _interopRequireDefault(_rewardPointNotify);
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	var _jsCookie = __webpack_require__("fe53252afd7b6c35cb73");
	
	var _jsCookie2 = _interopRequireDefault(_jsCookie);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	__webpack_require__("4d9b0dab3f4f00038468");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var rpn = new _rewardPointNotify2["default"]();
	rpn.display();
	
	$(document).ajaxSuccess(function (event, XMLHttpRequest, ajaxOptions) {
	  rpn.push(XMLHttpRequest.getResponseHeader('Reward-Point-Notify'));
	  rpn.display();
	});
	
	if ($('#rewardPointNotify').length > 0) {
	  var message = $('#rewardPointNotify').text();
	  if (message) {
	    (0, _notify2["default"])('success', decodeURIComponent(message));
	  };
	};
	
	$('[data-toggle="popover"]').popover({
	  html: true
	});
	
	$('[data-toggle="tooltip"]').tooltip({
	  html: true
	});
	
	$(document).ajaxError(function (event, jqxhr, settings, exception) {
	  if (jqxhr.responseText === 'LoginLimit') {
	    location.href = '/login';
	  }
	  var json = jQuery.parseJSON(jqxhr.responseText);
	  var error = json.error;
	  if (!error) {
	    return;
	  }
	
	  if (error.name === 'Unlogin') {
	    var ua = navigator.userAgent.toLowerCase();
	    if (ua.match(/micromessenger/i) == "micromessenger" && $('meta[name=is-open]').attr('content') != 0) {
	      window.location.href = '/login/bind/weixinmob?_target_path=' + location.href;
	    } else {
	      var $loginModal = $("#login-modal");
	      $('.modal').modal('hide');
	      $loginModal.modal('show');
	      $.get($loginModal.data('url'), function (html) {
	        $loginModal.html(html);
	      });
	    }
	  }
	});
	
	$(document).ajaxSend(function (a, b, c) {
	  if (c.notSetHeader) return;
	
	  if (c.type === 'POST') {
	    b.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
	  }
	});
	
	if (app.scheduleCrontab) {
	  $.post(app.scheduleCrontab);
	}
	
	$('i.hover-spin').mouseenter(function () {
	  $(this).addClass('md-spin');
	}).mouseleave(function () {
	  $(this).removeClass('md-spin');
	});
	
	if ($('.set-email-alert').length > 0) {
	  $('.set-email-alert .close').click(function () {
	    _jsCookie2["default"].set('close_set_email_alert', 'true');
	  });
	}
	
	if ($('#announcements-alert').length > 0) {
	  if ($('#announcements-alert .swiper-container .swiper-wrapper').children().length > 1) {
	    var noticeSwiper = new _swiper2["default"]('#announcements-alert .swiper-container', {
	      speed: 300,
	      loop: true,
	      mode: 'vertical',
	      autoplay: 5000,
	      calculateHeight: true
	    });
	  }
	
	  $('#announcements-alert .close').click(function () {
	    _jsCookie2["default"].set('close_announcements_alert', 'true', {
	      path: '/'
	    });
	  });
	}
	
	if (!(0, _utils.isMobileDevice)()) {
	  $('body').on('mouseenter', 'li.nav-hover', function (event) {
	    $(this).addClass('open');
	  }).on('mouseleave', 'li.nav-hover', function (event) {
	    $(this).removeClass('open');
	  });
	} else {
	  $('li.nav-hover >a').attr('data-toggle', 'dropdown');
	}
	
	$('.js-search').focus(function () {
	  $(this).prop('placeholder', '').addClass('active');
	}).blur(function () {
	  $(this).prop('placeholder', Translator.trans('site.search_hint')).removeClass('active');
	});
	
	$("select[name='language']").change(function () {
	  _jsCookie2["default"].set("locale", $('select[name=language]').val(), { 'path': '/' });
	  $("select[name='language']").parents('form').trigger('submit');
	});
	
	var eventPost = function eventPost($obj) {
	  var postData = $obj.data();
	  $.post($obj.data('url'), postData);
	};
	
	$('.event-report').each(function () {
	  (function ($obj) {
	    eventPost($obj);
	  })($(this));
	});
	
	$('body').on('event-report', function (e, name) {
	  var $obj = $(name);
	  eventPost($obj);
	});
	
	$.ajax('/online/sample');

/***/ }),

/***/ "bc0db7ae498f28b1c7b4":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	(function ($) {
	  function tabelAjax($target, url) {
	    $.get(url).done(function (html) {
	      $target.html(html);
	    }).fail(function () {
	      (0, _notify2["default"])('danger', Translator.trans('site.response_error'));
	    });
	  }
	
	  $(document).on('click.cd.table.filter', '[data-toggle="table-filter"]', function () {
	    var $this = $(this);
	    if ($this.closest('li').hasClass('active')) {
	      return;
	    }
	
	    var $target = $($this.data('target'));
	    var url = $target.data('url');
	
	    var filterStr = $this.data('filter');
	    $target.data('filter', filterStr);
	
	    var sortStr = $target.data('sort');
	
	    if (sortStr) {
	      url = url + '?' + sortStr;
	
	      if (filterStr) {
	        url = url + '&' + filterStr;
	      }
	    } else {
	      if (filterStr) {
	        url = url + '?' + filterStr;
	      }
	    }
	
	    tabelAjax($target, url);
	  });
	
	  $(document).on('click.cd.table.sort', '[data-toggle="table-sort"]', function () {
	    var $this = $(this);
	
	    var $target = $($this.data('target'));
	    var url = $target.data('url');
	
	    var sortKey = $this.data('sort-key');
	    var sortValue = 'desc';
	
	    var $sortIcon = $this.find('.active');
	    if ($sortIcon.length) {
	      sortValue = $sortIcon.siblings().data('sort-value');
	    }
	
	    var sortStr = sortKey + '=' + sortValue;
	    $target.data('sort', sortStr);
	
	    var filterStr = $target.data('filter');
	
	    if (filterStr) {
	      url = url + '?' + sortStr + '&' + filterStr;
	    } else {
	      url = url + '?' + sortStr;
	    }
	
	    tabelAjax($target, url);
	  });
	})(jQuery);

/***/ }),

/***/ "90ed575288b0bb9908a4":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _utils = __webpack_require__("43c010a1a8cfbeb1798d");
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	(function ($) {
	  var normalUpload = function normalUpload($input, src) {
	    var $target = $($input.data('target'));
	    $target.css('background-image', 'url(' + src + ')').addClass('done');
	    if (!$target.find('.mask').length) {
	      var html = '<div class="mask"></div>';
	      $target.append(html);
	    }
	  };
	
	  var cropUpload = function cropUpload($input, src) {
	    var $modal = $('#modal');
	    $('.js-upload-image, .upload-source-img').removeClass('active');
	    $input.addClass('active');
	
	    var image = new Image();
	    image.onload = function () {
	      var width = image.width;
	      var height = image.height;
	      var cropWidth = $input.data('crop-width');
	      var cropHeight = $input.data('crop-height');
	
	      var scale = (0, _utils.imageScale)(width, height, cropWidth, cropHeight);
	      $(image).attr({
	        'class': 'upload-source-img active hidden',
	        'data-natural-width': width,
	        'data-natural-height': height,
	        'width': scale.width,
	        'height': scale.height
	      });
	      $input.after(image);
	    };
	    image.src = src;
	    $modal.load($input.data('saveUrl')).modal({ backdrop: 'static', keyboard: false });
	  };
	
	  $(document).on('change.cd.local.upload', '[data-toggle="local-upload"]', function () {
	    var fr = new FileReader();
	    var $this = $(this);
	    var showType = $this.data('show-type') || 'background-image';
	
	    var fileTypes = ['image/bmp', 'image/jpeg', 'image/png'];
	
	    if (this.files[0].size > 2 * 1024 * 1024) {
	      (0, _notify2["default"])('danger', Translator.trans('uploader.size_2m_limit_hint'));
	      return;
	    }
	
	    if (!fileTypes.includes(this.files[0].type)) {
	      (0, _notify2["default"])('danger', Translator.trans('uploader.type_denied_limit_hint'));
	      return;
	    }
	
	    fr.onload = function (e) {
	      var src = e.target.result;
	      if (showType === 'background-image') {
	        normalUpload($this, src);
	      } else if (showType === 'image') {
	        cropUpload($this, src);
	      }
	    };
	
	    fr.readAsDataURL(this.files[0]);
	  });
	
	  $(document).on('upload-image', '.js-upload-image.active', function (e, cropOptions) {
	    var $this = $(this);
	    var $modal = $("#modal");
	
	    var fromData = new FormData();
	
	    fromData.append('token', $this.data('token'));
	    fromData.append('file', this.files[0]);
	
	    var uploadImage = function uploadImage(ret) {
	      return new Promise(function (resolve, reject) {
	        $.ajax({
	          url: $this.data('fileUpload'),
	          type: 'POST',
	          cache: false,
	          data: fromData,
	          processData: false,
	          contentType: false
	        }).done(function (data) {
	          resolve(data);
	        });
	      });
	    };
	
	    var cropImage = function cropImage(ret) {
	      return new Promise(function (resolve, reject) {
	        $.post($this.data('crop'), cropOptions, function (data) {
	          resolve(data);
	        });
	      });
	    };
	
	    var saveAvatar = function saveAvatar(ret) {
	      return new Promise(function (resolve, reject) {
	        $.post($this.data('saveUrl'), { images: ret }, function (data) {
	          if (data.image) {
	            $($this.data('targeImg')).attr('src', data.image);
	            (0, _notify2["default"])('success', Translator.trans('site.upload_success_hint'));
	            $modal.modal('hide');
	          }
	        }).error(function () {
	          (0, _notify2["default"])('danger', Translator.trans('site.upload_fail_retry_hint'));
	          $modal.modal('hide');
	        });
	      });
	    };
	
	    uploadImage().then(function (ret) {
	      return cropImage(ret);
	    }).then(function (ret) {
	      return saveAvatar(ret);
	    })["catch"](function (reason) {
	      (0, _notify2["default"])('danger', Translator.trans(reason));
	      $modal.modal('hide');
	    });
	  });
	
	  $('#modal').on('hide.bs.modal', function () {
	    $('[data-toggle="local-upload"]').val('');
	  });
	})(jQuery);

/***/ }),

/***/ "43c010a1a8cfbeb1798d":
/***/ (function(module, exports) {

	"use strict";
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var imageScale = exports.imageScale = function imageScale(naturalWidth, naturalHeight, cropWidth, cropHeight) {
	
	  var width = cropWidth;
	  var height = cropHeight;
	
	  var naturalScale = naturalWidth / naturalHeight;
	  var cropScale = cropWidth / cropHeight;
	
	  if (naturalScale > cropScale) {
	    width = naturalScale * cropWidth;
	  } else {
	    height = cropHeight / naturalScale;
	  }
	
	  return {
	    width: width,
	    height: height
	  };
	};

/***/ }),

/***/ "dc0cc38836f18fdb00b4":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("ee19a46ef43088c77962");
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	if ($(".nav.nav-tabs").length > 0 && !(0, _utils.isMobileDevice)()) {
	  // console.log(lavaLamp);
	  $(".nav.nav-tabs").lavaLamp();
	}

/***/ }),

/***/ "210ef5d7199861362f9b":
/***/ (function(module, exports) {

	'use strict';
	
	/* eslint-disable */
	jQuery.extend(jQuery.easing, { easein: function easein(x, t, b, c, d) {
	    return c * (t /= d) * t + b;
	  }, easeinout: function easeinout(x, t, b, c, d) {
	    if (t < d / 2) return 2 * c * t * t / (d * d) + b;var a = t - d / 2;return -2 * c * a * a / (d * d) + 2 * c * a / d + c / 2 + b;
	  }, easeout: function easeout(x, t, b, c, d) {
	    return -c * t * t / (d * d) + 2 * c * t / d + b;
	  }, expoin: function expoin(x, t, b, c, d) {
	    var a = 1;if (c < 0) {
	      a *= -1;c *= -1;
	    }return a * Math.exp(Math.log(c) / d * t) + b;
	  }, expoout: function expoout(x, t, b, c, d) {
	    var a = 1;if (c < 0) {
	      a *= -1;c *= -1;
	    }return a * (-Math.exp(-Math.log(c) / d * (t - d)) + c + 1) + b;
	  }, expoinout: function expoinout(x, t, b, c, d) {
	    var a = 1;if (c < 0) {
	      a *= -1;c *= -1;
	    }if (t < d / 2) return a * Math.exp(Math.log(c / 2) / (d / 2) * t) + b;return a * (-Math.exp(-2 * Math.log(c / 2) / d * (t - d)) + c + 1) + b;
	  }, bouncein: function bouncein(x, t, b, c, d) {
	    return c - jQuery.easing['bounceout'](x, d - t, 0, c, d) + b;
	  }, bounceout: function bounceout(x, t, b, c, d) {
	    if ((t /= d) < 1 / 2.75) {
	      return c * (7.5625 * t * t) + b;
	    } else if (t < 2 / 2.75) {
	      return c * (7.5625 * (t -= 1.5 / 2.75) * t + .75) + b;
	    } else if (t < 2.5 / 2.75) {
	      return c * (7.5625 * (t -= 2.25 / 2.75) * t + .9375) + b;
	    } else {
	      return c * (7.5625 * (t -= 2.625 / 2.75) * t + .984375) + b;
	    }
	  }, bounceinout: function bounceinout(x, t, b, c, d) {
	    if (t < d / 2) return jQuery.easing['bouncein'](x, t * 2, 0, c, d) * .5 + b;return jQuery.easing['bounceout'](x, t * 2 - d, 0, c, d) * .5 + c * .5 + b;
	  }, elasin: function elasin(x, t, b, c, d) {
	    var s = 1.70158;var p = 0;var a = c;if (t == 0) return b;if ((t /= d) == 1) return b + c;if (!p) p = d * .3;if (a < Math.abs(c)) {
	      a = c;var s = p / 4;
	    } else var s = p / (2 * Math.PI) * Math.asin(c / a);return -(a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b;
	  }, elasout: function elasout(x, t, b, c, d) {
	    var s = 1.70158;var p = 0;var a = c;if (t == 0) return b;if ((t /= d) == 1) return b + c;if (!p) p = d * .3;if (a < Math.abs(c)) {
	      a = c;var s = p / 4;
	    } else var s = p / (2 * Math.PI) * Math.asin(c / a);return a * Math.pow(2, -10 * t) * Math.sin((t * d - s) * (2 * Math.PI) / p) + c + b;
	  }, elasinout: function elasinout(x, t, b, c, d) {
	    var s = 1.70158;var p = 0;var a = c;if (t == 0) return b;if ((t /= d / 2) == 2) return b + c;if (!p) p = d * (.3 * 1.5);if (a < Math.abs(c)) {
	      a = c;var s = p / 4;
	    } else var s = p / (2 * Math.PI) * Math.asin(c / a);if (t < 1) return -.5 * (a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b;return a * Math.pow(2, -10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p) * .5 + c + b;
	  }, backin: function backin(x, t, b, c, d) {
	    var s = 1.70158;return c * (t /= d) * t * ((s + 1) * t - s) + b;
	  }, backout: function backout(x, t, b, c, d) {
	    var s = 1.70158;return c * ((t = t / d - 1) * t * ((s + 1) * t + s) + 1) + b;
	  }, backinout: function backinout(x, t, b, c, d) {
	    var s = 1.70158;if ((t /= d / 2) < 1) return c / 2 * (t * t * (((s *= 1.525) + 1) * t - s)) + b;return c / 2 * ((t -= 2) * t * (((s *= 1.525) + 1) * t + s) + 2) + b;
	  }, linear: function linear(x, t, b, c, d) {
	    return c * t / d + b;
	  } });
	/* eslint-enable */

/***/ }),

/***/ "4ed97247d4dc16a650a7":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Radio = function () {
	  function Radio(props) {
	    _classCallCheck(this, Radio);
	
	    this.el = props.el;
	    this.parent = props.parent || document;
	    this.cb = props.cb;
	
	    this.init();
	  }
	
	  _createClass(Radio, [{
	    key: 'init',
	    value: function init() {
	      this.event();
	    }
	  }, {
	    key: 'event',
	    value: function event() {
	      var _this = this;
	
	      $(this.parent).on('click.cd.radio', this.el, function (event) {
	        return _this.clickHandle(event);
	      });
	    }
	  }, {
	    key: 'clickHandle',
	    value: function clickHandle(event) {
	      event.stopPropagation();
	      var $this = $(event.currentTarget);
	
	      $this.parent().addClass('checked').siblings().removeClass('checked');
	      this.cb && this.cb(event);
	    }
	  }]);
	
	  return Radio;
	}();
	
	function radio(props) {
	  return new Radio(props);
	}
	
	// DATA-API
	$(document).on('click.cd.radio.data-api', '[data-toggle="cd-radio"]', function (event) {
	  event.stopPropagation();
	  var $this = $(event.currentTarget);
	
	  $this.parent().addClass('checked').siblings().removeClass('checked');
	});
	
	// HOW TO USE 
	// cd.radio({
	//   el: '[data-toggle="cd-radio"]',
	//   cb() {
	//     console.log('这是回调函数')
	//   }
	// });
	
	exports["default"] = radio;

/***/ })

});
//# sourceMappingURL=main.js.map