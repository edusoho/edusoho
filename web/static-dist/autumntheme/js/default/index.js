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

	"use strict";
	
	echo.init();
	
	var carousel = function carousel() {
	
	  var $this = $("#autumn-carousel .carousel-inner .item");
	
	  for (var i = 0; i < $this.length; i++) {
	    if (i == 0) {
	      var html = '<li data-target="#autumn-carousel" data-slide-to="0" class="active"></li>';
	      $this.parents(".carousel-inner").siblings(".carousel-indicators").append(html);
	    } else {
	      var _html = '<li data-target="#autumn-carousel" data-slide-to="' + i + '"></li>';
	      $this.parents(".carousel-inner").siblings(".carousel-indicators").append(_html);
	    }
	  }
	};
	carousel();

/***/ })
/******/ ]);
//# sourceMappingURL=index.js.map