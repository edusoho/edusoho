webpackJsonp(["app/js/courseset/show/index"],{

/***/ "d14d05cad9e7abf02a5d":
/***/ (function(module, exports) {

	export var toggleIcon = function toggleIcon(target, $expandIconClass, $putIconClass) {
	  var $icon = target.find('.js-remove-icon');
	  var $text = target.find('.js-remove-text');
	  if ($icon.hasClass($expandIconClass)) {
	    $icon.removeClass($expandIconClass).addClass($putIconClass);
	    $text ? $text.text(Translator.trans('收起')) : '';
	  } else {
	    $icon.removeClass($putIconClass).addClass($expandIconClass);
	    $text ? $text.text(Translator.trans('展开')) : '';
	  }
	};
	
	export var chapterAnimate = function chapterAnimate() {
	  var delegateTarget = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'body';
	  var target = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '.js-task-chapter';
	  var $expandIconClass = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'es-icon-remove';
	  var $putIconClass = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'es-icon-anonymous-iconfont';
	
	  $(delegateTarget).on('click', target, function (event) {
	    var $this = $(event.currentTarget);
	    $this.nextUntil(target).animate({ height: 'toggle', opacity: 'toggle' }, "normal");
	    toggleIcon($this, $expandIconClass, $putIconClass);
	  });
	};

/***/ }),

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

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _chapterAnimate = __webpack_require__("d14d05cad9e7abf02a5d");
	
	var _attachmentActions = __webpack_require__("d5fb0e67d2d4c1ebaaed");
	
	var _attachmentActions2 = _interopRequireDefault(_attachmentActions);
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	echo.init();
	(0, _chapterAnimate.chapterAnimate)();
	initTaskLearnChart();
	triggerMemberExpired();
	remainTime();
	
	if ($('.js-attachment-list').length > 0) {
	  new _attachmentActions2.default($('.js-attachment-list'));
	}
	
	function initTaskLearnChart() {
	  var colorPrimary = $('.color-primary').css('color');
	  var colorWarning = $('.color-warning').css('color');
	  $('#freeprogress').easyPieChart({
	    easing: 'easeOutBounce',
	    trackColor: '#ebebeb',
	    barColor: colorPrimary,
	    scaleColor: false,
	    lineWidth: 14,
	    size: 145,
	    onStep: function onStep(from, to, percent) {
	      $('canvas').css('height', '146px');
	      $('canvas').css('width', '146px');
	      if (Math.round(percent) == 100) {
	        $(this.el).addClass('done');
	      }
	      $(this.el).find('.percent').html('学习进度' + '<br><span class="num">' + Math.round(percent) + '%</span>');
	    }
	  });
	
	  $('#orderprogress-plan').easyPieChart({
	    easing: 'easeOutBounce',
	    trackColor: '#ebebeb',
	    barColor: colorWarning,
	    scaleColor: false,
	    lineWidth: 14,
	    size: 145
	  });
	
	  var bg = $('#orderprogress-plan').length > 0 ? 'transparent' : '#ebebeb';
	
	  $('#orderprogress').easyPieChart({
	    easing: 'easeOutBounce',
	    trackColor: bg,
	    barColor: colorPrimary,
	    scaleColor: false,
	    lineWidth: 14,
	    size: 145,
	    onStep: function onStep(from, to, percent) {
	      if (Math.round(percent) == 100) {
	        $(this.el).addClass('done');
	      }
	      $(this.el).find('.percent').html('学习进度' + '<br><span class="num">' + Math.round(percent) + '%</span>');
	    }
	  });
	}
	
	function triggerMemberExpired() {
	  if ($('.member-expire').length) {
	    $(".member-expire a").trigger('click');
	  }
	}
	
	function remainTime() {
	  var remainTime = parseInt($('#discount-endtime-countdown').data('remaintime'));
	  if (remainTime >= 0) {
	    var endtime = new Date(new Date().valueOf() + remainTime * 1000);
	    $('#discount-endtime-countdown').countdown(endtime, function (event) {
	      var $this = $(this).html(event.strftime(Translator.trans('剩余 ') + '<span>%D</span>' + Translator.trans('天 ') + '<span>%H</span>' + Translator.trans('时 ') + '<span>%M</span>' + Translator.trans('分 ') + '<span>%S</span> ' + Translator.trans('秒')));
	    }).on('finish.countdown', function () {
	      $(this).html(Translator.trans('活动时间到，正在刷新网页，请稍等...'));
	      setTimeout(function () {
	        $.post(app.crontab, function () {
	          window.location.reload();
	        });
	      }, 2000);
	    });
	  }
	}
	
	// 暂时去掉块状
	// let orderLearnSwiper = null;
	// $('.js-task-show-type').on('click', 'a', function() {
	//     let $this = $(this).addClass('active');
	//     $($this.data('list')).removeClass('hidden');
	//     $($this.siblings('a').removeClass('active').data('list')).addClass('hidden');
	//     if($this.data('type') == 'chart'&& !orderLearnSwiper) {
	//       initSwiper();
	//     }
	// })
	// 暂时去掉块状
	// function initSwiper() {
	//   orderLearnSwiper = new Swiper('.swiper-container',{
	//     pagination: '.swiper-pager',
	//     loop:true,
	//     grabCursor: true,
	//     paginationClickable: true
	//   })
	//   $('.arrow-left').on('click', function(e){
	//     e.preventDefault()
	//     orderLearnSwiper.swipePrev();
	//   })
	//   $('.arrow-right').on('click', function(e){
	//     e.preventDefault()
	//     orderLearnSwiper.swipeNext();
	//   })
	// }

/***/ })

});