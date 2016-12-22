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

/***/ 0:
/***/ function(module, exports, __webpack_require__) {

	module.exports = __webpack_require__(5);


/***/ },

/***/ 5:
/***/ function(module, exports) {

	'use strict';
	
	jQuery.fn.extend({
	  insertAtCaret: function insertAtCaret(myValue) {
	    return this.each(function (i) {
	      if (document.selection) {
	        //For browsers like Internet Explorer
	        this.focus();
	        sel = document.selection.createRange();
	        sel.text = myValue;
	        this.focus();
	      } else if (this.selectionStart || this.selectionStart == '0') {
	        //For browsers like Firefox and Webkit based
	        var startPos = this.selectionStart;
	        var endPos = this.selectionEnd;
	        var scrollTop = this.scrollTop;
	        this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
	        this.focus();
	        this.selectionStart = startPos + myValue.length;
	        this.selectionEnd = startPos + myValue.length;
	        this.scrollTop = scrollTop;
	      } else {
	        this.value += myValue;
	        this.focus();
	      }
	    });
	  }
	});

/***/ }

/******/ });