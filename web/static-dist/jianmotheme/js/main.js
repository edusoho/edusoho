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
/******/ ([
/* 0 */
/***/ (function(module, exports) {

	import { isMobileDevice } from 'common/utils';
	
	var eventtype = isMobileDevice ? 'touchstart' : 'click';
	
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
/******/ ]);