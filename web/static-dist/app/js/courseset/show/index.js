webpackJsonp(["app/js/courseset/show/index"],{

/***/ "584608d4ce1895020bac":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };
	
	var buyBtn = exports.buyBtn = function buyBtn($element) {
	  $element.on('click', function (event) {
	    $.post($(event.currentTarget).data('url'), function (resp) {
	      if ((typeof resp === 'undefined' ? 'undefined' : _typeof(resp)) === 'object') {
	        window.location.href = resp.url;
	      } else {
	        $('#modal').modal('show').html(resp);
	      }
	    });
	  });
	};

/***/ }),

/***/ "d14d05cad9e7abf02a5d":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var toggleIcon = exports.toggleIcon = function toggleIcon(target, $expandIconClass, $putIconClass) {
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
	
	var chapterAnimate = exports.chapterAnimate = function chapterAnimate() {
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

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _chapterAnimate = __webpack_require__("d14d05cad9e7abf02a5d");
	
	var _attachmentActions = __webpack_require__("d5fb0e67d2d4c1ebaaed");
	
	var _attachmentActions2 = _interopRequireDefault(_attachmentActions);
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	var _esInfiniteScroll = __webpack_require__("e66ca5da7109f35e9051");
	
	var _esInfiniteScroll2 = _interopRequireDefault(_esInfiniteScroll);
	
	var _btnUtil = __webpack_require__("584608d4ce1895020bac");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _esInfiniteScroll2["default"]();
	
	echo.init();
	(0, _chapterAnimate.chapterAnimate)();
	initTaskLearnChart();
	triggerMemberExpired();
	remainTime();
	
	if ($('.js-attachment-list').length > 0) {
	  new _attachmentActions2["default"]($('.js-attachment-list'));
	}
	
	(0, _btnUtil.buyBtn)($('.js-buy-btn'));
	(0, _btnUtil.buyBtn)($('.js-task-buy-btn'));
	
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
	      $(this.el).find('.percent').html(Translator.trans('course_set.learn_progress') + '<br><span class="num">' + Math.round(percent) + '%</span>');
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
	      $(this.el).find('.percent').html(Translator.trans('course_set.learn_progress') + '<br><span class="num">' + Math.round(percent) + '%</span>');
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
	      var $this = $(this).html(event.strftime(Translator.trans('course_set.show.count_down_format_hint')));
	    }).on('finish.countdown', function () {
	      $(this).html(Translator.trans('course_set.show.time_finish_hint'));
	      setTimeout(function () {
	        $.post(app.crontab, function () {
	          window.location.reload();
	        });
	      }, 2000);
	    });
	  }
	}
	
	function postCourseViewEvent() {
	  var $obj = $('#event-report');
	  var postData = $obj.data();
	  $.post($obj.data('url'), postData);
	}
	
	postCourseViewEvent();
	
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

/***/ }),

/***/ "e66ca5da7109f35e9051":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	__webpack_require__("8f3ec98312b1f1f6bafb");
	
	__webpack_require__("c5e642028fa5ee5a3554");
	
	var _esEventEmitter = __webpack_require__("63fff8fb24f3bd1f61cd");
	
	var _esEventEmitter2 = _interopRequireDefault(_esEventEmitter);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var ESInfiniteScroll = function (_Emitter) {
	  _inherits(ESInfiniteScroll, _Emitter);
	
	  function ESInfiniteScroll(options) {
	    _classCallCheck(this, ESInfiniteScroll);
	
	    var _this = _possibleConstructorReturn(this, (ESInfiniteScroll.__proto__ || Object.getPrototypeOf(ESInfiniteScroll)).call(this));
	
	    _this.options = options;
	
	    _this.initDownInfinite();
	    _this.initUpLoading();
	    return _this;
	  }
	
	  _createClass(ESInfiniteScroll, [{
	    key: 'initUpLoading',
	    value: function initUpLoading() {
	      $('.js-up-more-link').on('click', function (event) {
	        var $target = $(event.currentTarget);
	        $.ajax({
	          method: 'GET',
	          url: $target.data('url'),
	          async: false,
	          success: function success(html) {
	            $(html).find('.infinite-item').prependTo($('.infinite-container'));
	            var $upLink = $(html).find('.js-up-more-link');
	            if ($upLink.length > 0) {
	              $target.data('url', $upLink.data('url'));
	            } else {
	              $target.remove();
	            }
	          }
	        });
	      });
	    }
	  }, {
	    key: 'initDownInfinite',
	    value: function initDownInfinite() {
	      var defaultDownOptions = {
	        element: $('.infinite-container')[0]
	      };
	
	      defaultDownOptions = Object.assign(defaultDownOptions, this.options);
	
	      this.downInfinite = new Waypoint.Infinite(defaultDownOptions);
	    }
	  }]);
	
	  return ESInfiniteScroll;
	}(_esEventEmitter2["default"]);
	
	exports["default"] = ESInfiniteScroll;

/***/ }),

/***/ "8f3ec98312b1f1f6bafb":
/***/ (function(module, exports) {

	/*!
	Waypoints - 4.0.1
	Copyright © 2011-2016 Caleb Troughton
	Licensed under the MIT license.
	https://github.com/imakewebthings/waypoints/blob/master/licenses.txt
	*/
	!function(){"use strict";function t(o){if(!o)throw new Error("No options passed to Waypoint constructor");if(!o.element)throw new Error("No element option passed to Waypoint constructor");if(!o.handler)throw new Error("No handler option passed to Waypoint constructor");this.key="waypoint-"+e,this.options=t.Adapter.extend({},t.defaults,o),this.element=this.options.element,this.adapter=new t.Adapter(this.element),this.callback=o.handler,this.axis=this.options.horizontal?"horizontal":"vertical",this.enabled=this.options.enabled,this.triggerPoint=null,this.group=t.Group.findOrCreate({name:this.options.group,axis:this.axis}),this.context=t.Context.findOrCreateByElement(this.options.context),t.offsetAliases[this.options.offset]&&(this.options.offset=t.offsetAliases[this.options.offset]),this.group.add(this),this.context.add(this),i[this.key]=this,e+=1}var e=0,i={};t.prototype.queueTrigger=function(t){this.group.queueTrigger(this,t)},t.prototype.trigger=function(t){this.enabled&&this.callback&&this.callback.apply(this,t)},t.prototype.destroy=function(){this.context.remove(this),this.group.remove(this),delete i[this.key]},t.prototype.disable=function(){return this.enabled=!1,this},t.prototype.enable=function(){return this.context.refresh(),this.enabled=!0,this},t.prototype.next=function(){return this.group.next(this)},t.prototype.previous=function(){return this.group.previous(this)},t.invokeAll=function(t){var e=[];for(var o in i)e.push(i[o]);for(var n=0,r=e.length;r>n;n++)e[n][t]()},t.destroyAll=function(){t.invokeAll("destroy")},t.disableAll=function(){t.invokeAll("disable")},t.enableAll=function(){t.Context.refreshAll();for(var e in i)i[e].enabled=!0;return this},t.refreshAll=function(){t.Context.refreshAll()},t.viewportHeight=function(){return window.innerHeight||document.documentElement.clientHeight},t.viewportWidth=function(){return document.documentElement.clientWidth},t.adapters=[],t.defaults={context:window,continuous:!0,enabled:!0,group:"default",horizontal:!1,offset:0},t.offsetAliases={"bottom-in-view":function(){return this.context.innerHeight()-this.adapter.outerHeight()},"right-in-view":function(){return this.context.innerWidth()-this.adapter.outerWidth()}},window.Waypoint=t}(),function(){"use strict";function t(t){window.setTimeout(t,1e3/60)}function e(t){this.element=t,this.Adapter=n.Adapter,this.adapter=new this.Adapter(t),this.key="waypoint-context-"+i,this.didScroll=!1,this.didResize=!1,this.oldScroll={x:this.adapter.scrollLeft(),y:this.adapter.scrollTop()},this.waypoints={vertical:{},horizontal:{}},t.waypointContextKey=this.key,o[t.waypointContextKey]=this,i+=1,n.windowContext||(n.windowContext=!0,n.windowContext=new e(window)),this.createThrottledScrollHandler(),this.createThrottledResizeHandler()}var i=0,o={},n=window.Waypoint,r=window.onload;e.prototype.add=function(t){var e=t.options.horizontal?"horizontal":"vertical";this.waypoints[e][t.key]=t,this.refresh()},e.prototype.checkEmpty=function(){var t=this.Adapter.isEmptyObject(this.waypoints.horizontal),e=this.Adapter.isEmptyObject(this.waypoints.vertical),i=this.element==this.element.window;t&&e&&!i&&(this.adapter.off(".waypoints"),delete o[this.key])},e.prototype.createThrottledResizeHandler=function(){function t(){e.handleResize(),e.didResize=!1}var e=this;this.adapter.on("resize.waypoints",function(){e.didResize||(e.didResize=!0,n.requestAnimationFrame(t))})},e.prototype.createThrottledScrollHandler=function(){function t(){e.handleScroll(),e.didScroll=!1}var e=this;this.adapter.on("scroll.waypoints",function(){(!e.didScroll||n.isTouch)&&(e.didScroll=!0,n.requestAnimationFrame(t))})},e.prototype.handleResize=function(){n.Context.refreshAll()},e.prototype.handleScroll=function(){var t={},e={horizontal:{newScroll:this.adapter.scrollLeft(),oldScroll:this.oldScroll.x,forward:"right",backward:"left"},vertical:{newScroll:this.adapter.scrollTop(),oldScroll:this.oldScroll.y,forward:"down",backward:"up"}};for(var i in e){var o=e[i],n=o.newScroll>o.oldScroll,r=n?o.forward:o.backward;for(var s in this.waypoints[i]){var a=this.waypoints[i][s];if(null!==a.triggerPoint){var l=o.oldScroll<a.triggerPoint,h=o.newScroll>=a.triggerPoint,p=l&&h,u=!l&&!h;(p||u)&&(a.queueTrigger(r),t[a.group.id]=a.group)}}}for(var c in t)t[c].flushTriggers();this.oldScroll={x:e.horizontal.newScroll,y:e.vertical.newScroll}},e.prototype.innerHeight=function(){return this.element==this.element.window?n.viewportHeight():this.adapter.innerHeight()},e.prototype.remove=function(t){delete this.waypoints[t.axis][t.key],this.checkEmpty()},e.prototype.innerWidth=function(){return this.element==this.element.window?n.viewportWidth():this.adapter.innerWidth()},e.prototype.destroy=function(){var t=[];for(var e in this.waypoints)for(var i in this.waypoints[e])t.push(this.waypoints[e][i]);for(var o=0,n=t.length;n>o;o++)t[o].destroy()},e.prototype.refresh=function(){var t,e=this.element==this.element.window,i=e?void 0:this.adapter.offset(),o={};this.handleScroll(),t={horizontal:{contextOffset:e?0:i.left,contextScroll:e?0:this.oldScroll.x,contextDimension:this.innerWidth(),oldScroll:this.oldScroll.x,forward:"right",backward:"left",offsetProp:"left"},vertical:{contextOffset:e?0:i.top,contextScroll:e?0:this.oldScroll.y,contextDimension:this.innerHeight(),oldScroll:this.oldScroll.y,forward:"down",backward:"up",offsetProp:"top"}};for(var r in t){var s=t[r];for(var a in this.waypoints[r]){var l,h,p,u,c,d=this.waypoints[r][a],f=d.options.offset,w=d.triggerPoint,y=0,g=null==w;d.element!==d.element.window&&(y=d.adapter.offset()[s.offsetProp]),"function"==typeof f?f=f.apply(d):"string"==typeof f&&(f=parseFloat(f),d.options.offset.indexOf("%")>-1&&(f=Math.ceil(s.contextDimension*f/100))),l=s.contextScroll-s.contextOffset,d.triggerPoint=Math.floor(y+l-f),h=w<s.oldScroll,p=d.triggerPoint>=s.oldScroll,u=h&&p,c=!h&&!p,!g&&u?(d.queueTrigger(s.backward),o[d.group.id]=d.group):!g&&c?(d.queueTrigger(s.forward),o[d.group.id]=d.group):g&&s.oldScroll>=d.triggerPoint&&(d.queueTrigger(s.forward),o[d.group.id]=d.group)}}return n.requestAnimationFrame(function(){for(var t in o)o[t].flushTriggers()}),this},e.findOrCreateByElement=function(t){return e.findByElement(t)||new e(t)},e.refreshAll=function(){for(var t in o)o[t].refresh()},e.findByElement=function(t){return o[t.waypointContextKey]},window.onload=function(){r&&r(),e.refreshAll()},n.requestAnimationFrame=function(e){var i=window.requestAnimationFrame||window.mozRequestAnimationFrame||window.webkitRequestAnimationFrame||t;i.call(window,e)},n.Context=e}(),function(){"use strict";function t(t,e){return t.triggerPoint-e.triggerPoint}function e(t,e){return e.triggerPoint-t.triggerPoint}function i(t){this.name=t.name,this.axis=t.axis,this.id=this.name+"-"+this.axis,this.waypoints=[],this.clearTriggerQueues(),o[this.axis][this.name]=this}var o={vertical:{},horizontal:{}},n=window.Waypoint;i.prototype.add=function(t){this.waypoints.push(t)},i.prototype.clearTriggerQueues=function(){this.triggerQueues={up:[],down:[],left:[],right:[]}},i.prototype.flushTriggers=function(){for(var i in this.triggerQueues){var o=this.triggerQueues[i],n="up"===i||"left"===i;o.sort(n?e:t);for(var r=0,s=o.length;s>r;r+=1){var a=o[r];(a.options.continuous||r===o.length-1)&&a.trigger([i])}}this.clearTriggerQueues()},i.prototype.next=function(e){this.waypoints.sort(t);var i=n.Adapter.inArray(e,this.waypoints),o=i===this.waypoints.length-1;return o?null:this.waypoints[i+1]},i.prototype.previous=function(e){this.waypoints.sort(t);var i=n.Adapter.inArray(e,this.waypoints);return i?this.waypoints[i-1]:null},i.prototype.queueTrigger=function(t,e){this.triggerQueues[e].push(t)},i.prototype.remove=function(t){var e=n.Adapter.inArray(t,this.waypoints);e>-1&&this.waypoints.splice(e,1)},i.prototype.first=function(){return this.waypoints[0]},i.prototype.last=function(){return this.waypoints[this.waypoints.length-1]},i.findOrCreate=function(t){return o[t.axis][t.name]||new i(t)},n.Group=i}(),function(){"use strict";function t(t){this.$element=e(t)}var e=window.jQuery,i=window.Waypoint;e.each(["innerHeight","innerWidth","off","offset","on","outerHeight","outerWidth","scrollLeft","scrollTop"],function(e,i){t.prototype[i]=function(){var t=Array.prototype.slice.call(arguments);return this.$element[i].apply(this.$element,t)}}),e.each(["extend","inArray","isEmptyObject"],function(i,o){t[o]=e[o]}),i.adapters.push({name:"jquery",Adapter:t}),i.Adapter=t}(),function(){"use strict";function t(t){return function(){var i=[],o=arguments[0];return t.isFunction(arguments[0])&&(o=t.extend({},arguments[1]),o.handler=arguments[0]),this.each(function(){var n=t.extend({},o,{element:this});"string"==typeof n.context&&(n.context=t(this).closest(n.context)[0]),i.push(new e(n))}),i}}var e=window.Waypoint;window.jQuery&&(window.jQuery.fn.waypoint=t(window.jQuery)),window.Zepto&&(window.Zepto.fn.waypoint=t(window.Zepto))}();

/***/ }),

/***/ "c5e642028fa5ee5a3554":
/***/ (function(module, exports) {

	/*!
	Waypoints Infinite Scroll Shortcut - 4.0.1
	Copyright © 2011-2016 Caleb Troughton
	Licensed under the MIT license.
	https://github.com/imakewebthings/waypoints/blob/master/licenses.txt
	*/
	!function(){"use strict";function t(n){this.options=i.extend({},t.defaults,n),this.container=this.options.element,"auto"!==this.options.container&&(this.container=this.options.container),this.$container=i(this.container),this.$more=i(this.options.more),this.$more.length&&(this.setupHandler(),this.waypoint=new o(this.options))}var i=window.jQuery,o=window.Waypoint;t.prototype.setupHandler=function(){this.options.handler=i.proxy(function(){this.options.onBeforePageLoad(),this.destroy(),this.$container.addClass(this.options.loadingClass),i.get(i(this.options.more).attr("href"),i.proxy(function(t){var n=i(i.parseHTML(t)),e=n.find(this.options.more),s=n.find(this.options.items);s.length||(s=n.filter(this.options.items)),this.$container.append(s),this.$container.removeClass(this.options.loadingClass),e.length||(e=n.filter(this.options.more)),e.length?(this.$more.replaceWith(e),this.$more=e,this.waypoint=new o(this.options)):this.$more.remove(),this.options.onAfterPageLoad(s)},this))},this)},t.prototype.destroy=function(){this.waypoint&&this.waypoint.destroy()},t.defaults={container:"auto",items:".infinite-item",more:".infinite-more-link",offset:"bottom-in-view",loadingClass:"infinite-loading",onBeforePageLoad:i.noop,onAfterPageLoad:i.noop},o.Infinite=t}();

/***/ })

});
//# sourceMappingURL=index.js.map