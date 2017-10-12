/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/static-dist/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "9181c6995ae8c5c94b7a":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var Browser = {};
	var userAgent = navigator.userAgent.toLowerCase();
	var s = void 0;
	
	(s = userAgent.match(/msie ([\d.]+)/)) ? Browser.ie = s[1] : (s = userAgent.match(/firefox\/([\d.]+)/)) ? Browser.firefox = s[1] : (s = userAgent.match(/chrome\/([\d.]+)/)) ? Browser.chrome = s[1] : (s = userAgent.match(/opera.([\d.]+)/)) ? Browser.opera = s[1] : (s = userAgent.match(/version\/([\d.]+).*safari/)) ? Browser.safari = s[1] : 0;
	
	Browser.ie10 = /MSIE\s+10.0/i.test(navigator.userAgent);
	Browser.ie11 = /Trident\/7\./.test(navigator.userAgent);
	Browser.edge = /Edge\/13./i.test(navigator.userAgent);
	
	var isMobileDevice = function isMobileDevice() {
	  return navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i);
	};
	
	var delHtmlTag = function delHtmlTag(str) {
	  return str.replace(/<[^>]+>/g, '').replace(/&nbsp;/ig, '');
	};
	
	var initTooltips = function initTooltips() {
	  $('[data-toggle="tooltip"]').tooltip({
	    html: true
	  });
	};
	
	var initPopover = function initPopover() {
	  $('[data-toggle="popover"]').popover({
	    html: true
	  });
	};
	
	var sec2Time = function sec2Time(sec) {
	  var time = '';
	  var h = parseInt(sec % 86400 / 3600);
	  var s = parseInt(sec % 3600 / 60);
	  var m = sec % 60;
	  if (h > 0) {
	    time += h + ':';
	  }
	  if (s.toString().length < 2) {
	    time += '0' + s + ':';
	  } else {
	    time += s + ':';
	  }
	  if (m.toString().length < 2) {
	    time += '0' + m;
	  } else {
	    time += m;
	  }
	  return time;
	};
	
	var time2Sec = function time2Sec(time) {
	  var arry = time.split(':');
	  var sec = 0;
	  for (var i = 0; i < arry.length; i++) {
	    if (arry.length > 2) {
	      if (i == 0) {
	        sec += arry[i] * 3600;
	      }
	      if (i == 1) {
	        sec += arry[i] * 60;
	      }
	      if (i == 2) {
	        sec += parseInt(arry[i]);
	      }
	    }
	    if (arry.length <= 2) {
	      if (i == 0) {
	        sec += arry[i] * 60;
	      }
	      if (i == 1) {
	        sec += parseInt(arry[i]);
	      }
	    }
	  }
	  return sec;
	};
	
	var isLogin = function () {
	  return $("meta[name='is-login']").attr("content") == 1;
	}();
	
	exports.Browser = Browser;
	exports.isLogin = isLogin;
	exports.isMobileDevice = isMobileDevice;
	exports.delHtmlTag = delHtmlTag;
	exports.initTooltips = initTooltips;
	exports.initPopover = initPopover;
	exports.sec2Time = sec2Time;
	exports.time2Sec = time2Sec;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	var eventtype = _utils.isMobileDevice ? 'touchstart' : 'click';
	
	var removeNavMobile = function removeNavMobile() {
	  $(".nav-mobile,.html-mask").removeClass("active");
	  $("html,.es-wrap").removeClass("nav-active");
	};
	
	$(".js-navbar-more").click(function (e) {
	  var $nav = $(".nav-mobile");
	
	  if ($nav.hasClass("active")) {
	    removeNavMobile();
	  } else {
	    var height = $(window).height();
	    $nav.addClass("active").css("height", height);
	
	    $(".html-mask").addClass("active");
	    $("html,.es-wrap").addClass("nav-active");
	  }
	});
	
	$("body").on(eventtype, '.html-mask.active', function (e) {
	  removeNavMobile();
	});

/***/ })

/******/ });
//# sourceMappingURL=main.js.map