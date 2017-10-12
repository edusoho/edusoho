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

/***/ "a140ba2b3f9117751115":
/***/ (function(module, exports) {

	/*** IMPORTS FROM imports-loader ***/
	var define = false;
	var module = false;
	var exports = false;
	(function() {
	
	/*! echo.js v1.7.0 | (c) 2015 @toddmotto | https://github.com/toddmotto/echo */
	(function (root, factory) {
	  if (typeof define === 'function' && define.amd) {
	    define(function() {
	      return factory(root);
	    });
	  } else if (typeof exports === 'object') {
	    module.exports = factory;
	  } else {
	    root.echo = factory(root);
	  }
	})(this, function (root) {
	
	  'use strict';
	
	  var echo = {};
	
	  var callback = function () {};
	
	  var offset, poll, delay, useDebounce, unload;
	
	  var isHidden = function (element) {
	    return (element.offsetParent === null);
	  };
	  
	  var inView = function (element, view) {
	    if (isHidden(element)) {
	      return false;
	    }
	
	    var box = element.getBoundingClientRect();
	    return (box.right >= view.l && box.bottom >= view.t && box.left <= view.r && box.top <= view.b);
	  };
	
	  var debounceOrThrottle = function () {
	    if(!useDebounce && !!poll) {
	      return;
	    }
	    clearTimeout(poll);
	    poll = setTimeout(function(){
	      echo.render();
	      poll = null;
	    }, delay);
	  };
	
	  echo.init = function (opts) {
	    opts = opts || {};
	    var offsetAll = opts.offset || 0;
	    var offsetVertical = opts.offsetVertical || offsetAll;
	    var offsetHorizontal = opts.offsetHorizontal || offsetAll;
	    var optionToInt = function (opt, fallback) {
	      return parseInt(opt || fallback, 10);
	    };
	    offset = {
	      t: optionToInt(opts.offsetTop, offsetVertical),
	      b: optionToInt(opts.offsetBottom, offsetVertical),
	      l: optionToInt(opts.offsetLeft, offsetHorizontal),
	      r: optionToInt(opts.offsetRight, offsetHorizontal)
	    };
	    delay = optionToInt(opts.throttle, 250);
	    useDebounce = opts.debounce !== false;
	    unload = !!opts.unload;
	    callback = opts.callback || callback;
	    echo.render();
	    if (document.addEventListener) {
	      root.addEventListener('scroll', debounceOrThrottle, false);
	      root.addEventListener('load', debounceOrThrottle, false);
	    } else {
	      root.attachEvent('onscroll', debounceOrThrottle);
	      root.attachEvent('onload', debounceOrThrottle);
	    }
	  };
	
	  echo.render = function () {
	    var nodes = document.querySelectorAll('img[data-echo], [data-echo-background]');
	    var length = nodes.length;
	    var src, elem;
	    var view = {
	      l: 0 - offset.l,
	      t: 0 - offset.t,
	      b: (root.innerHeight || document.documentElement.clientHeight) + offset.b,
	      r: (root.innerWidth || document.documentElement.clientWidth) + offset.r
	    };
	    for (var i = 0; i < length; i++) {
	      elem = nodes[i];
	      if (inView(elem, view)) {
	
	        if (unload) {
	          elem.setAttribute('data-echo-placeholder', elem.src);
	        }
	
	        if (elem.getAttribute('data-echo-background') !== null) {
	          elem.style.backgroundImage = "url(" + elem.getAttribute('data-echo-background') + ")";
	        }
	        else {
	          elem.src = elem.getAttribute('data-echo');
	        }
	
	        if (!unload) {
	          elem.removeAttribute('data-echo');
	          elem.removeAttribute('data-echo-background');
	        }
	
	        callback(elem, 'load');
	      }
	      else if (unload && !!(src = elem.getAttribute('data-echo-placeholder'))) {
	
	        if (elem.getAttribute('data-echo-background') !== null) {
	          elem.style.backgroundImage = "url(" + src + ")";
	        }
	        else {
	          elem.src = src;
	        }
	
	        elem.removeAttribute('data-echo-placeholder');
	        callback(elem, 'unload');
	      }
	    }
	    if (!length) {
	      echo.detach();
	    }
	  };
	
	  echo.detach = function () {
	    if (document.removeEventListener) {
	      root.removeEventListener('scroll', debounceOrThrottle);
	    } else {
	      root.detachEvent('onscroll', debounceOrThrottle);
	    }
	    clearTimeout(poll);
	  };
	
	  return echo;
	
	});
	
	}.call(window));

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	module.exports = __webpack_require__("a140ba2b3f9117751115");


/***/ })

/******/ });
//# sourceMappingURL=echo-js.js.map