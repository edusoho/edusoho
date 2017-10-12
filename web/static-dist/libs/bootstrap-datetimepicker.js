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

/***/ "fbcdef65edfe3a26619d":
/***/ (function(module, exports, __webpack_require__) {

	exports = module.exports = __webpack_require__("e7f1add7f34e416618de")();
	// imports
	
	
	// module
	exports.push([module.id, "/*!\r\n * Datetimepicker for Bootstrap\r\n *\r\n * Copyright 2012 Stefan Petre\r\n * Improvements by Andrew Rowls\r\n * Licensed under the Apache License v2.0\r\n * http://www.apache.org/licenses/LICENSE-2.0\r\n *\r\n */\r\n.datetimepicker {\r\n\tpadding: 4px;\r\n\tmargin-top: 1px;\r\n\t-webkit-border-radius: 4px;\r\n\t-moz-border-radius: 4px;\r\n\tborder-radius: 4px;\r\n\tdirection: ltr;\r\n}\r\n\r\n.datetimepicker-inline {\r\n\twidth: 220px;\r\n}\r\n\r\n.datetimepicker.datetimepicker-rtl {\r\n\tdirection: rtl;\r\n}\r\n\r\n.datetimepicker.datetimepicker-rtl table tr td span {\r\n\tfloat: right;\r\n}\r\n\r\n.datetimepicker-dropdown, .datetimepicker-dropdown-left {\r\n\ttop: 0;\r\n\tleft: 0;\r\n}\r\n\r\n[class*=\" datetimepicker-dropdown\"]:before {\r\n\tcontent: '';\r\n\tdisplay: inline-block;\r\n\tborder-left: 7px solid transparent;\r\n\tborder-right: 7px solid transparent;\r\n\tborder-bottom: 7px solid #cccccc;\r\n\tborder-bottom-color: rgba(0, 0, 0, 0.2);\r\n\tposition: absolute;\r\n}\r\n\r\n[class*=\" datetimepicker-dropdown\"]:after {\r\n\tcontent: '';\r\n\tdisplay: inline-block;\r\n\tborder-left: 6px solid transparent;\r\n\tborder-right: 6px solid transparent;\r\n\tborder-bottom: 6px solid #ffffff;\r\n\tposition: absolute;\r\n}\r\n\r\n[class*=\" datetimepicker-dropdown-top\"]:before {\r\n\tcontent: '';\r\n\tdisplay: inline-block;\r\n\tborder-left: 7px solid transparent;\r\n\tborder-right: 7px solid transparent;\r\n\tborder-top: 7px solid #cccccc;\r\n\tborder-top-color: rgba(0, 0, 0, 0.2);\r\n\tborder-bottom: 0;\r\n}\r\n\r\n[class*=\" datetimepicker-dropdown-top\"]:after {\r\n\tcontent: '';\r\n\tdisplay: inline-block;\r\n\tborder-left: 6px solid transparent;\r\n\tborder-right: 6px solid transparent;\r\n\tborder-top: 6px solid #ffffff;\r\n\tborder-bottom: 0;\r\n}\r\n\r\n.datetimepicker-dropdown-bottom-left:before {\r\n\ttop: -7px;\r\n\tright: 6px;\r\n}\r\n\r\n.datetimepicker-dropdown-bottom-left:after {\r\n\ttop: -6px;\r\n\tright: 7px;\r\n}\r\n\r\n.datetimepicker-dropdown-bottom-right:before {\r\n\ttop: -7px;\r\n\tleft: 6px;\r\n}\r\n\r\n.datetimepicker-dropdown-bottom-right:after {\r\n\ttop: -6px;\r\n\tleft: 7px;\r\n}\r\n\r\n.datetimepicker-dropdown-top-left:before {\r\n\tbottom: -7px;\r\n\tright: 6px;\r\n}\r\n\r\n.datetimepicker-dropdown-top-left:after {\r\n\tbottom: -6px;\r\n\tright: 7px;\r\n}\r\n\r\n.datetimepicker-dropdown-top-right:before {\r\n\tbottom: -7px;\r\n\tleft: 6px;\r\n}\r\n\r\n.datetimepicker-dropdown-top-right:after {\r\n\tbottom: -6px;\r\n\tleft: 7px;\r\n}\r\n\r\n.datetimepicker > div {\r\n\tdisplay: none;\r\n}\r\n\r\n.datetimepicker.minutes div.datetimepicker-minutes {\r\n\tdisplay: block;\r\n}\r\n\r\n.datetimepicker.hours div.datetimepicker-hours {\r\n\tdisplay: block;\r\n}\r\n\r\n.datetimepicker.days div.datetimepicker-days {\r\n\tdisplay: block;\r\n}\r\n\r\n.datetimepicker.months div.datetimepicker-months {\r\n\tdisplay: block;\r\n}\r\n\r\n.datetimepicker.years div.datetimepicker-years {\r\n\tdisplay: block;\r\n}\r\n\r\n.datetimepicker table {\r\n\tmargin: 0;\r\n}\r\n\r\n.datetimepicker  td,\r\n.datetimepicker th {\r\n\ttext-align: center;\r\n\twidth: 20px;\r\n\theight: 20px;\r\n\t-webkit-border-radius: 4px;\r\n\t-moz-border-radius: 4px;\r\n\tborder-radius: 4px;\r\n\tborder: none;\r\n}\r\n\r\n.table-striped .datetimepicker table tr td,\r\n.table-striped .datetimepicker table tr th {\r\n\tbackground-color: transparent;\r\n}\r\n\r\n.datetimepicker table tr td.minute:hover {\r\n\tbackground: #eeeeee;\r\n\tcursor: pointer;\r\n}\r\n\r\n.datetimepicker table tr td.hour:hover {\r\n\tbackground: #eeeeee;\r\n\tcursor: pointer;\r\n}\r\n\r\n.datetimepicker table tr td.day:hover {\r\n\tbackground: #eeeeee;\r\n\tcursor: pointer;\r\n}\r\n\r\n.datetimepicker table tr td.old,\r\n.datetimepicker table tr td.new {\r\n\tcolor: #999999;\r\n}\r\n\r\n.datetimepicker table tr td.disabled,\r\n.datetimepicker table tr td.disabled:hover {\r\n\tbackground: none;\r\n\tcolor: #999999;\r\n\tcursor: default;\r\n}\r\n\r\n.datetimepicker table tr td.today,\r\n.datetimepicker table tr td.today:hover,\r\n.datetimepicker table tr td.today.disabled,\r\n.datetimepicker table tr td.today.disabled:hover {\r\n\tbackground-color: #fde19a;\r\n\tbackground-image: -moz-linear-gradient(top, #fdd49a, #fdf59a);\r\n\tbackground-image: -ms-linear-gradient(top, #fdd49a, #fdf59a);\r\n\tbackground-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fdd49a), to(#fdf59a));\r\n\tbackground-image: -webkit-linear-gradient(top, #fdd49a, #fdf59a);\r\n\tbackground-image: -o-linear-gradient(top, #fdd49a, #fdf59a);\r\n\tbackground-image: linear-gradient(top, #fdd49a, #fdf59a);\r\n\tbackground-repeat: repeat-x;\r\n\tfilter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fdd49a', endColorstr='#fdf59a', GradientType=0);\r\n\tborder-color: #fdf59a #fdf59a #fbed50;\r\n\tborder-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);\r\n\tfilter: progid:DXImageTransform.Microsoft.gradient(enabled=false);\r\n}\r\n\r\n.datetimepicker table tr td.today:hover,\r\n.datetimepicker table tr td.today:hover:hover,\r\n.datetimepicker table tr td.today.disabled:hover,\r\n.datetimepicker table tr td.today.disabled:hover:hover,\r\n.datetimepicker table tr td.today:active,\r\n.datetimepicker table tr td.today:hover:active,\r\n.datetimepicker table tr td.today.disabled:active,\r\n.datetimepicker table tr td.today.disabled:hover:active,\r\n.datetimepicker table tr td.today.active,\r\n.datetimepicker table tr td.today:hover.active,\r\n.datetimepicker table tr td.today.disabled.active,\r\n.datetimepicker table tr td.today.disabled:hover.active,\r\n.datetimepicker table tr td.today.disabled,\r\n.datetimepicker table tr td.today:hover.disabled,\r\n.datetimepicker table tr td.today.disabled.disabled,\r\n.datetimepicker table tr td.today.disabled:hover.disabled,\r\n.datetimepicker table tr td.today[disabled],\r\n.datetimepicker table tr td.today:hover[disabled],\r\n.datetimepicker table tr td.today.disabled[disabled],\r\n.datetimepicker table tr td.today.disabled:hover[disabled] {\r\n\tbackground-color: #fdf59a;\r\n}\r\n\r\n.datetimepicker table tr td.today:active,\r\n.datetimepicker table tr td.today:hover:active,\r\n.datetimepicker table tr td.today.disabled:active,\r\n.datetimepicker table tr td.today.disabled:hover:active,\r\n.datetimepicker table tr td.today.active,\r\n.datetimepicker table tr td.today:hover.active,\r\n.datetimepicker table tr td.today.disabled.active,\r\n.datetimepicker table tr td.today.disabled:hover.active {\r\n\tbackground-color: #fbf069;\r\n}\r\n\r\n.datetimepicker table tr td.active,\r\n.datetimepicker table tr td.active:hover,\r\n.datetimepicker table tr td.active.disabled,\r\n.datetimepicker table tr td.active.disabled:hover {\r\n\tbackground-color: #006dcc;\r\n\tbackground-image: -moz-linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-image: -ms-linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-image: -webkit-gradient(linear, 0 0, 0 100%, from(#0088cc), to(#0044cc));\r\n\tbackground-image: -webkit-linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-image: -o-linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-image: linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-repeat: repeat-x;\r\n\tfilter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#0088cc', endColorstr='#0044cc', GradientType=0);\r\n\tborder-color: #0044cc #0044cc #002a80;\r\n\tborder-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);\r\n\tfilter: progid:DXImageTransform.Microsoft.gradient(enabled=false);\r\n\tcolor: #ffffff;\r\n\ttext-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);\r\n}\r\n\r\n.datetimepicker table tr td.active:hover,\r\n.datetimepicker table tr td.active:hover:hover,\r\n.datetimepicker table tr td.active.disabled:hover,\r\n.datetimepicker table tr td.active.disabled:hover:hover,\r\n.datetimepicker table tr td.active:active,\r\n.datetimepicker table tr td.active:hover:active,\r\n.datetimepicker table tr td.active.disabled:active,\r\n.datetimepicker table tr td.active.disabled:hover:active,\r\n.datetimepicker table tr td.active.active,\r\n.datetimepicker table tr td.active:hover.active,\r\n.datetimepicker table tr td.active.disabled.active,\r\n.datetimepicker table tr td.active.disabled:hover.active,\r\n.datetimepicker table tr td.active.disabled,\r\n.datetimepicker table tr td.active:hover.disabled,\r\n.datetimepicker table tr td.active.disabled.disabled,\r\n.datetimepicker table tr td.active.disabled:hover.disabled,\r\n.datetimepicker table tr td.active[disabled],\r\n.datetimepicker table tr td.active:hover[disabled],\r\n.datetimepicker table tr td.active.disabled[disabled],\r\n.datetimepicker table tr td.active.disabled:hover[disabled] {\r\n\tbackground-color: #0044cc;\r\n}\r\n\r\n.datetimepicker table tr td.active:active,\r\n.datetimepicker table tr td.active:hover:active,\r\n.datetimepicker table tr td.active.disabled:active,\r\n.datetimepicker table tr td.active.disabled:hover:active,\r\n.datetimepicker table tr td.active.active,\r\n.datetimepicker table tr td.active:hover.active,\r\n.datetimepicker table tr td.active.disabled.active,\r\n.datetimepicker table tr td.active.disabled:hover.active {\r\n\tbackground-color: #003399;\r\n}\r\n\r\n.datetimepicker table tr td span {\r\n\tdisplay: block;\r\n\twidth: 23%;\r\n\theight: 54px;\r\n\tline-height: 54px;\r\n\tfloat: left;\r\n\tmargin: 1%;\r\n\tcursor: pointer;\r\n\t-webkit-border-radius: 4px;\r\n\t-moz-border-radius: 4px;\r\n\tborder-radius: 4px;\r\n}\r\n\r\n.datetimepicker .datetimepicker-hours span {\r\n\theight: 26px;\r\n\tline-height: 26px;\r\n}\r\n\r\n.datetimepicker .datetimepicker-hours table tr td span.hour_am,\r\n.datetimepicker .datetimepicker-hours table tr td span.hour_pm {\r\n\twidth: 14.6%;\r\n}\r\n\r\n.datetimepicker .datetimepicker-hours fieldset legend,\r\n.datetimepicker .datetimepicker-minutes fieldset legend {\r\n\tmargin-bottom: inherit;\r\n\tline-height: 30px;\r\n}\r\n\r\n.datetimepicker .datetimepicker-minutes span {\r\n\theight: 26px;\r\n\tline-height: 26px;\r\n}\r\n\r\n.datetimepicker table tr td span:hover {\r\n\tbackground: #eeeeee;\r\n}\r\n\r\n.datetimepicker table tr td span.disabled,\r\n.datetimepicker table tr td span.disabled:hover {\r\n\tbackground: none;\r\n\tcolor: #999999;\r\n\tcursor: default;\r\n}\r\n\r\n.datetimepicker table tr td span.active,\r\n.datetimepicker table tr td span.active:hover,\r\n.datetimepicker table tr td span.active.disabled,\r\n.datetimepicker table tr td span.active.disabled:hover {\r\n\tbackground-color: #006dcc;\r\n\tbackground-image: -moz-linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-image: -ms-linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-image: -webkit-gradient(linear, 0 0, 0 100%, from(#0088cc), to(#0044cc));\r\n\tbackground-image: -webkit-linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-image: -o-linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-image: linear-gradient(top, #0088cc, #0044cc);\r\n\tbackground-repeat: repeat-x;\r\n\tfilter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#0088cc', endColorstr='#0044cc', GradientType=0);\r\n\tborder-color: #0044cc #0044cc #002a80;\r\n\tborder-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);\r\n\tfilter: progid:DXImageTransform.Microsoft.gradient(enabled=false);\r\n\tcolor: #ffffff;\r\n\ttext-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);\r\n}\r\n\r\n.datetimepicker table tr td span.active:hover,\r\n.datetimepicker table tr td span.active:hover:hover,\r\n.datetimepicker table tr td span.active.disabled:hover,\r\n.datetimepicker table tr td span.active.disabled:hover:hover,\r\n.datetimepicker table tr td span.active:active,\r\n.datetimepicker table tr td span.active:hover:active,\r\n.datetimepicker table tr td span.active.disabled:active,\r\n.datetimepicker table tr td span.active.disabled:hover:active,\r\n.datetimepicker table tr td span.active.active,\r\n.datetimepicker table tr td span.active:hover.active,\r\n.datetimepicker table tr td span.active.disabled.active,\r\n.datetimepicker table tr td span.active.disabled:hover.active,\r\n.datetimepicker table tr td span.active.disabled,\r\n.datetimepicker table tr td span.active:hover.disabled,\r\n.datetimepicker table tr td span.active.disabled.disabled,\r\n.datetimepicker table tr td span.active.disabled:hover.disabled,\r\n.datetimepicker table tr td span.active[disabled],\r\n.datetimepicker table tr td span.active:hover[disabled],\r\n.datetimepicker table tr td span.active.disabled[disabled],\r\n.datetimepicker table tr td span.active.disabled:hover[disabled] {\r\n\tbackground-color: #0044cc;\r\n}\r\n\r\n.datetimepicker table tr td span.active:active,\r\n.datetimepicker table tr td span.active:hover:active,\r\n.datetimepicker table tr td span.active.disabled:active,\r\n.datetimepicker table tr td span.active.disabled:hover:active,\r\n.datetimepicker table tr td span.active.active,\r\n.datetimepicker table tr td span.active:hover.active,\r\n.datetimepicker table tr td span.active.disabled.active,\r\n.datetimepicker table tr td span.active.disabled:hover.active {\r\n\tbackground-color: #003399;\r\n}\r\n\r\n.datetimepicker table tr td span.old {\r\n\tcolor: #999999;\r\n}\r\n\r\n.datetimepicker th.switch {\r\n\twidth: 145px;\r\n}\r\n\r\n.datetimepicker th span.glyphicon {\r\n\tpointer-events: none;\r\n}\r\n\r\n.datetimepicker thead tr:first-child th,\r\n.datetimepicker tfoot th {\r\n\tcursor: pointer;\r\n}\r\n\r\n.datetimepicker thead tr:first-child th:hover,\r\n.datetimepicker tfoot th:hover {\r\n\tbackground: #eeeeee;\r\n}\r\n\r\n.input-append.date .add-on i,\r\n.input-prepend.date .add-on i,\r\n.input-group.date .input-group-addon span {\r\n\tcursor: pointer;\r\n\twidth: 14px;\r\n\theight: 14px;\r\n}\r\n", ""]);
	
	// exports


/***/ }),

/***/ "a9374df36b3d91e7ad15":
/***/ (function(module, exports, __webpack_require__) {

	exports = module.exports = __webpack_require__("e7f1add7f34e416618de")();
	// imports
	
	
	// module
	exports.push([module.id, ".datetimepicker {\n  padding: 4px !important;\n}\n", ""]);
	
	// exports


/***/ }),

/***/ "e7f1add7f34e416618de":
/***/ (function(module, exports) {

	/*
		MIT License http://www.opensource.org/licenses/mit-license.php
		Author Tobias Koppers @sokra
	*/
	// css base code, injected by the css-loader
	module.exports = function() {
		var list = [];
	
		// return the list of modules as css string
		list.toString = function toString() {
			var result = [];
			for(var i = 0; i < this.length; i++) {
				var item = this[i];
				if(item[2]) {
					result.push("@media " + item[2] + "{" + item[1] + "}");
				} else {
					result.push(item[1]);
				}
			}
			return result.join("");
		};
	
		// import a list of modules into the list
		list.i = function(modules, mediaQuery) {
			if(typeof modules === "string")
				modules = [[null, modules, ""]];
			var alreadyImportedModules = {};
			for(var i = 0; i < this.length; i++) {
				var id = this[i][0];
				if(typeof id === "number")
					alreadyImportedModules[id] = true;
			}
			for(i = 0; i < modules.length; i++) {
				var item = modules[i];
				// skip already imported module
				// this implementation is not 100% perfect for weird media query combinations
				//  when a module is imported multiple times with different media queries.
				//  I hope this will never occur (Hey this way we have smaller bundles)
				if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
					if(mediaQuery && !item[2]) {
						item[2] = mediaQuery;
					} else if(mediaQuery) {
						item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
					}
					list.push(item);
				}
			}
		};
		return list;
	};


/***/ }),

/***/ "703aa93cb1c4be3a3ba9":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("620c5ef0872df3df2c66");
	
	__webpack_require__("fbcdef65edfe3a266191");
	
	__webpack_require__("a9374df36b3d91e7ad11");
	
	$.fn.datetimepicker.dates['zh_CN'] = {
	    days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
	    daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
	    daysMin: ["日", "一", "二", "三", "四", "五", "六", "日"],
	    months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
	    monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
	    today: "今天",
	    suffix: [],
	    meridiem: ["上午", "下午"]
	};

/***/ }),

/***/ "620c5ef0872df3df2c66":
/***/ (function(module, exports, __webpack_require__) {

	/*** IMPORTS FROM imports-loader ***/
	var define = false;
	var module = false;
	var exports = false;
	(function() {
	
	/* =========================================================
	 * bootstrap-datetimepicker.js
	 * =========================================================
	 * Copyright 2012 Stefan Petre
	 *
	 * Improvements by Andrew Rowls
	 * Improvements by Sébastien Malot
	 * Improvements by Yun Lai
	 * Improvements by Kenneth Henderick
	 * Improvements by CuGBabyBeaR
	 * Improvements by Christian Vaas <auspex@auspex.eu>
	 *
	 * Project URL : http://www.malot.fr/bootstrap-datetimepicker
	 *
	 * Licensed under the Apache License, Version 2.0 (the "License");
	 * you may not use this file except in compliance with the License.
	 * You may obtain a copy of the License at
	 *
	 * http://www.apache.org/licenses/LICENSE-2.0
	 *
	 * Unless required by applicable law or agreed to in writing, software
	 * distributed under the License is distributed on an "AS IS" BASIS,
	 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 * See the License for the specific language governing permissions and
	 * limitations under the License.
	 * ========================================================= */
	
	(function(factory){
	    if (typeof define === 'function' && define.amd)
	      define(['jquery'], factory);
	    else if (typeof exports === 'object')
	      factory(__webpack_require__(1));
	    else
	      factory(jQuery);
	
	}(function($, undefined){
	
	  // Add ECMA262-5 Array methods if not supported natively (IE8)
	  if (!('indexOf' in Array.prototype)) {
	    Array.prototype.indexOf = function (find, i) {
	      if (i === undefined) i = 0;
	      if (i < 0) i += this.length;
	      if (i < 0) i = 0;
	      for (var n = this.length; i < n; i++) {
	        if (i in this && this[i] === find) {
	          return i;
	        }
	      }
	      return -1;
	    }
	  }
	
	  function elementOrParentIsFixed (element) {
	    var $element = $(element);
	    var $checkElements = $element.add($element.parents());
	    var isFixed = false;
	    $checkElements.each(function(){
	      if ($(this).css('position') === 'fixed') {
	        isFixed = true;
	        return false;
	      }
	    });
	    return isFixed;
	  }
	
	  function UTCDate() {
	    return new Date(Date.UTC.apply(Date, arguments));
	  }
	
	  function UTCToday() {
	    var today = new Date();
	    return UTCDate(today.getUTCFullYear(), today.getUTCMonth(), today.getUTCDate(), today.getUTCHours(), today.getUTCMinutes(), today.getUTCSeconds(), 0);
	  }
	
	  // Picker object
	  var Datetimepicker = function (element, options) {
	    var that = this;
	
	    this.element = $(element);
	
	    // add container for single page application
	    // when page switch the datetimepicker div will be removed also.
	    this.container = options.container || 'body';
	
	    this.language = options.language || this.element.data('date-language') || 'en';
	    this.language = this.language in dates ? this.language : this.language.split('-')[0]; // fr-CA fallback to fr
	    this.language = this.language in dates ? this.language : 'en';
	    this.isRTL = dates[this.language].rtl || false;
	    this.formatType = options.formatType || this.element.data('format-type') || 'standard';
	    this.format = DPGlobal.parseFormat(options.format || this.element.data('date-format') || dates[this.language].format || DPGlobal.getDefaultFormat(this.formatType, 'input'), this.formatType);
	    this.isInline = false;
	    this.isVisible = false;
	    this.isInput = this.element.is('input');
	    this.fontAwesome = options.fontAwesome || this.element.data('font-awesome') || false;
	
	    this.bootcssVer = options.bootcssVer || (this.isInput ? (this.element.is('.form-control') ? 3 : 2) : ( this.bootcssVer = this.element.is('.input-group') ? 3 : 2 ));
	
	    this.component = this.element.is('.date') ? ( this.bootcssVer == 3 ? this.element.find('.input-group-addon .glyphicon-th, .input-group-addon .glyphicon-time, .input-group-addon .glyphicon-remove, .input-group-addon .glyphicon-calendar, .input-group-addon .fa-calendar, .input-group-addon .fa-clock-o').parent() : this.element.find('.add-on .icon-th, .add-on .icon-time, .add-on .icon-calendar, .add-on .fa-calendar, .add-on .fa-clock-o').parent()) : false;
	    this.componentReset = this.element.is('.date') ? ( this.bootcssVer == 3 ? this.element.find('.input-group-addon .glyphicon-remove, .input-group-addon .fa-times').parent():this.element.find('.add-on .icon-remove, .add-on .fa-times').parent()) : false;
	    this.hasInput = this.component && this.element.find('input').length;
	    if (this.component && this.component.length === 0) {
	      this.component = false;
	    }
	    this.linkField = options.linkField || this.element.data('link-field') || false;
	    this.linkFormat = DPGlobal.parseFormat(options.linkFormat || this.element.data('link-format') || DPGlobal.getDefaultFormat(this.formatType, 'link'), this.formatType);
	    this.minuteStep = options.minuteStep || this.element.data('minute-step') || 5;
	    this.pickerPosition = options.pickerPosition || this.element.data('picker-position') || 'bottom-right';
	    this.showMeridian = options.showMeridian || this.element.data('show-meridian') || false;
	    this.initialDate = options.initialDate || new Date();
	    this.zIndex = options.zIndex || this.element.data('z-index') || undefined;
	    this.title = typeof options.title === 'undefined' ? false : options.title;
	
	    this.icons = {
	      leftArrow: this.fontAwesome ? 'fa-arrow-left' : (this.bootcssVer === 3 ? 'glyphicon-arrow-left' : 'icon-arrow-left'),
	      rightArrow: this.fontAwesome ? 'fa-arrow-right' : (this.bootcssVer === 3 ? 'glyphicon-arrow-right' : 'icon-arrow-right')
	    }
	    this.icontype = this.fontAwesome ? 'fa' : 'glyphicon';
	
	    this._attachEvents();
	
	    this.clickedOutside = function (e) {
	        // Clicked outside the datetimepicker, hide it
	        if ($(e.target).closest('.datetimepicker').length === 0) {
	            that.hide();
	        }
	    }
	
	    this.formatViewType = 'datetime';
	    if ('formatViewType' in options) {
	      this.formatViewType = options.formatViewType;
	    } else if ('formatViewType' in this.element.data()) {
	      this.formatViewType = this.element.data('formatViewType');
	    }
	
	    this.minView = 0;
	    if ('minView' in options) {
	      this.minView = options.minView;
	    } else if ('minView' in this.element.data()) {
	      this.minView = this.element.data('min-view');
	    }
	    this.minView = DPGlobal.convertViewMode(this.minView);
	
	    this.maxView = DPGlobal.modes.length - 1;
	    if ('maxView' in options) {
	      this.maxView = options.maxView;
	    } else if ('maxView' in this.element.data()) {
	      this.maxView = this.element.data('max-view');
	    }
	    this.maxView = DPGlobal.convertViewMode(this.maxView);
	
	    this.wheelViewModeNavigation = false;
	    if ('wheelViewModeNavigation' in options) {
	      this.wheelViewModeNavigation = options.wheelViewModeNavigation;
	    } else if ('wheelViewModeNavigation' in this.element.data()) {
	      this.wheelViewModeNavigation = this.element.data('view-mode-wheel-navigation');
	    }
	
	    this.wheelViewModeNavigationInverseDirection = false;
	
	    if ('wheelViewModeNavigationInverseDirection' in options) {
	      this.wheelViewModeNavigationInverseDirection = options.wheelViewModeNavigationInverseDirection;
	    } else if ('wheelViewModeNavigationInverseDirection' in this.element.data()) {
	      this.wheelViewModeNavigationInverseDirection = this.element.data('view-mode-wheel-navigation-inverse-dir');
	    }
	
	    this.wheelViewModeNavigationDelay = 100;
	    if ('wheelViewModeNavigationDelay' in options) {
	      this.wheelViewModeNavigationDelay = options.wheelViewModeNavigationDelay;
	    } else if ('wheelViewModeNavigationDelay' in this.element.data()) {
	      this.wheelViewModeNavigationDelay = this.element.data('view-mode-wheel-navigation-delay');
	    }
	
	    this.startViewMode = 2;
	    if ('startView' in options) {
	      this.startViewMode = options.startView;
	    } else if ('startView' in this.element.data()) {
	      this.startViewMode = this.element.data('start-view');
	    }
	    this.startViewMode = DPGlobal.convertViewMode(this.startViewMode);
	    this.viewMode = this.startViewMode;
	
	    this.viewSelect = this.minView;
	    if ('viewSelect' in options) {
	      this.viewSelect = options.viewSelect;
	    } else if ('viewSelect' in this.element.data()) {
	      this.viewSelect = this.element.data('view-select');
	    }
	    this.viewSelect = DPGlobal.convertViewMode(this.viewSelect);
	
	    this.forceParse = true;
	    if ('forceParse' in options) {
	      this.forceParse = options.forceParse;
	    } else if ('dateForceParse' in this.element.data()) {
	      this.forceParse = this.element.data('date-force-parse');
	    }
	    var template = this.bootcssVer === 3 ? DPGlobal.templateV3 : DPGlobal.template;
	    while (template.indexOf('{iconType}') !== -1) {
	      template = template.replace('{iconType}', this.icontype);
	    }
	    while (template.indexOf('{leftArrow}') !== -1) {
	      template = template.replace('{leftArrow}', this.icons.leftArrow);
	    }
	    while (template.indexOf('{rightArrow}') !== -1) {
	      template = template.replace('{rightArrow}', this.icons.rightArrow);
	    }
	    this.picker = $(template)
	      .appendTo(this.isInline ? this.element : this.container) // 'body')
	      .on({
	        click:     $.proxy(this.click, this),
	        mousedown: $.proxy(this.mousedown, this)
	      });
	
	    if (this.wheelViewModeNavigation) {
	      if ($.fn.mousewheel) {
	        this.picker.on({mousewheel: $.proxy(this.mousewheel, this)});
	      } else {
	        console.log('Mouse Wheel event is not supported. Please include the jQuery Mouse Wheel plugin before enabling this option');
	      }
	    }
	
	    if (this.isInline) {
	      this.picker.addClass('datetimepicker-inline');
	    } else {
	      this.picker.addClass('datetimepicker-dropdown-' + this.pickerPosition + ' dropdown-menu');
	    }
	    if (this.isRTL) {
	      this.picker.addClass('datetimepicker-rtl');
	      var selector = this.bootcssVer === 3 ? '.prev span, .next span' : '.prev i, .next i';
	      this.picker.find(selector).toggleClass(this.icons.leftArrow + ' ' + this.icons.rightArrow);
	    }
	
	    $(document).on('mousedown', this.clickedOutside);
	
	    this.autoclose = false;
	    if ('autoclose' in options) {
	      this.autoclose = options.autoclose;
	    } else if ('dateAutoclose' in this.element.data()) {
	      this.autoclose = this.element.data('date-autoclose');
	    }
	
	    this.keyboardNavigation = true;
	    if ('keyboardNavigation' in options) {
	      this.keyboardNavigation = options.keyboardNavigation;
	    } else if ('dateKeyboardNavigation' in this.element.data()) {
	      this.keyboardNavigation = this.element.data('date-keyboard-navigation');
	    }
	
	    this.todayBtn = (options.todayBtn || this.element.data('date-today-btn') || false);
	    this.clearBtn = (options.clearBtn || this.element.data('date-clear-btn') || false);
	    this.todayHighlight = (options.todayHighlight || this.element.data('date-today-highlight') || false);
	
	    this.weekStart = ((options.weekStart || this.element.data('date-weekstart') || dates[this.language].weekStart || 0) % 7);
	    this.weekEnd = ((this.weekStart + 6) % 7);
	    this.startDate = -Infinity;
	    this.endDate = Infinity;
	    this.datesDisabled = [];
	    this.daysOfWeekDisabled = [];
	    this.setStartDate(options.startDate || this.element.data('date-startdate'));
	    this.setEndDate(options.endDate || this.element.data('date-enddate'));
	    this.setDatesDisabled(options.datesDisabled || this.element.data('date-dates-disabled'));
	    this.setDaysOfWeekDisabled(options.daysOfWeekDisabled || this.element.data('date-days-of-week-disabled'));
	    this.setMinutesDisabled(options.minutesDisabled || this.element.data('date-minute-disabled'));
	    this.setHoursDisabled(options.hoursDisabled || this.element.data('date-hour-disabled'));
	    this.fillDow();
	    this.fillMonths();
	    this.update();
	    this.showMode();
	
	    if (this.isInline) {
	      this.show();
	    }
	  };
	
	  Datetimepicker.prototype = {
	    constructor: Datetimepicker,
	
	    _events:       [],
	    _attachEvents: function () {
	      this._detachEvents();
	      if (this.isInput) { // single input
	        this._events = [
	          [this.element, {
	            focus:   $.proxy(this.show, this),
	            keyup:   $.proxy(this.update, this),
	            keydown: $.proxy(this.keydown, this)
	          }]
	        ];
	      }
	      else if (this.component && this.hasInput) { // component: input + button
	        this._events = [
	          // For components that are not readonly, allow keyboard nav
	          [this.element.find('input'), {
	            focus:   $.proxy(this.show, this),
	            keyup:   $.proxy(this.update, this),
	            keydown: $.proxy(this.keydown, this)
	          }],
	          [this.component, {
	            click: $.proxy(this.show, this)
	          }]
	        ];
	        if (this.componentReset) {
	          this._events.push([
	            this.componentReset,
	            {click: $.proxy(this.reset, this)}
	          ]);
	        }
	      }
	      else if (this.element.is('div')) {  // inline datetimepicker
	        this.isInline = true;
	      }
	      else {
	        this._events = [
	          [this.element, {
	            click: $.proxy(this.show, this)
	          }]
	        ];
	      }
	      for (var i = 0, el, ev; i < this._events.length; i++) {
	        el = this._events[i][0];
	        ev = this._events[i][1];
	        el.on(ev);
	      }
	    },
	
	    _detachEvents: function () {
	      for (var i = 0, el, ev; i < this._events.length; i++) {
	        el = this._events[i][0];
	        ev = this._events[i][1];
	        el.off(ev);
	      }
	      this._events = [];
	    },
	
	    show: function (e) {
	      this.picker.show();
	      this.height = this.component ? this.component.outerHeight() : this.element.outerHeight();
	      if (this.forceParse) {
	        this.update();
	      }
	      this.place();
	      $(window).on('resize', $.proxy(this.place, this));
	      if (e) {
	        e.stopPropagation();
	        e.preventDefault();
	      }
	      this.isVisible = true;
	      this.element.trigger({
	        type: 'show',
	        date: this.date
	      });
	    },
	
	    hide: function (e) {
	      if (!this.isVisible) return;
	      if (this.isInline) return;
	      this.picker.hide();
	      $(window).off('resize', this.place);
	      this.viewMode = this.startViewMode;
	      this.showMode();
	      if (!this.isInput) {
	        $(document).off('mousedown', this.hide);
	      }
	
	      if (
	        this.forceParse &&
	          (
	            this.isInput && this.element.val() ||
	              this.hasInput && this.element.find('input').val()
	            )
	        )
	        this.setValue();
	      this.isVisible = false;
	      this.element.trigger({
	        type: 'hide',
	        date: this.date
	      });
	    },
	
	    remove: function () {
	      this._detachEvents();
	      $(document).off('mousedown', this.clickedOutside);
	      this.picker.remove();
	      delete this.picker;
	      delete this.element.data().datetimepicker;
	    },
	
	    getDate: function () {
	      var d = this.getUTCDate();
	      return new Date(d.getTime() + (d.getTimezoneOffset() * 60000));
	    },
	
	    getUTCDate: function () {
	      return this.date;
	    },
	
	    getInitialDate: function () {
	      return this.initialDate
	    },
	
	    setInitialDate: function (initialDate) {
	      this.initialDate = initialDate;
	    },
	
	    setDate: function (d) {
	      this.setUTCDate(new Date(d.getTime() - (d.getTimezoneOffset() * 60000)));
	    },
	
	    setUTCDate: function (d) {
	      if (d >= this.startDate && d <= this.endDate) {
	        this.date = d;
	        this.setValue();
	        this.viewDate = this.date;
	        this.fill();
	      } else {
	        this.element.trigger({
	          type:      'outOfRange',
	          date:      d,
	          startDate: this.startDate,
	          endDate:   this.endDate
	        });
	      }
	    },
	
	    setFormat: function (format) {
	      this.format = DPGlobal.parseFormat(format, this.formatType);
	      var element;
	      if (this.isInput) {
	        element = this.element;
	      } else if (this.component) {
	        element = this.element.find('input');
	      }
	      if (element && element.val()) {
	        this.setValue();
	      }
	    },
	
	    setValue: function () {
	      var formatted = this.getFormattedDate();
	      if (!this.isInput) {
	        if (this.component) {
	          this.element.find('input').val(formatted);
	        }
	        this.element.data('date', formatted);
	      } else {
	        this.element.val(formatted);
	      }
	      if (this.linkField) {
	        $('#' + this.linkField).val(this.getFormattedDate(this.linkFormat));
	      }
	    },
	
	    getFormattedDate: function (format) {
	      if (format == undefined) format = this.format;
	      return DPGlobal.formatDate(this.date, format, this.language, this.formatType);
	    },
	
	    setStartDate: function (startDate) {
	      this.startDate = startDate || -Infinity;
	      if (this.startDate !== -Infinity) {
	        this.startDate = DPGlobal.parseDate(this.startDate, this.format, this.language, this.formatType);
	      }
	      this.update();
	      this.updateNavArrows();
	    },
	
	    setEndDate: function (endDate) {
	      this.endDate = endDate || Infinity;
	      if (this.endDate !== Infinity) {
	        this.endDate = DPGlobal.parseDate(this.endDate, this.format, this.language, this.formatType);
	      }
	      this.update();
	      this.updateNavArrows();
	    },
	
	    setDatesDisabled: function (datesDisabled) {
	      this.datesDisabled = datesDisabled || [];
	      if (!$.isArray(this.datesDisabled)) {
	        this.datesDisabled = this.datesDisabled.split(/,\s*/);
	      }
	      this.datesDisabled = $.map(this.datesDisabled, function (d) {
	        return DPGlobal.parseDate(d, this.format, this.language, this.formatType).toDateString();
	      });
	      this.update();
	      this.updateNavArrows();
	    },
	
	    setTitle: function (selector, value) {
	      return this.picker.find(selector)
	        .find('th:eq(1)')
	        .text(this.title === false ? value : this.title);
	    },
	
	    setDaysOfWeekDisabled: function (daysOfWeekDisabled) {
	      this.daysOfWeekDisabled = daysOfWeekDisabled || [];
	      if (!$.isArray(this.daysOfWeekDisabled)) {
	        this.daysOfWeekDisabled = this.daysOfWeekDisabled.split(/,\s*/);
	      }
	      this.daysOfWeekDisabled = $.map(this.daysOfWeekDisabled, function (d) {
	        return parseInt(d, 10);
	      });
	      this.update();
	      this.updateNavArrows();
	    },
	
	    setMinutesDisabled: function (minutesDisabled) {
	      this.minutesDisabled = minutesDisabled || [];
	      if (!$.isArray(this.minutesDisabled)) {
	        this.minutesDisabled = this.minutesDisabled.split(/,\s*/);
	      }
	      this.minutesDisabled = $.map(this.minutesDisabled, function (d) {
	        return parseInt(d, 10);
	      });
	      this.update();
	      this.updateNavArrows();
	    },
	
	    setHoursDisabled: function (hoursDisabled) {
	      this.hoursDisabled = hoursDisabled || [];
	      if (!$.isArray(this.hoursDisabled)) {
	        this.hoursDisabled = this.hoursDisabled.split(/,\s*/);
	      }
	      this.hoursDisabled = $.map(this.hoursDisabled, function (d) {
	        return parseInt(d, 10);
	      });
	      this.update();
	      this.updateNavArrows();
	    },
	
	    place: function () {
	      if (this.isInline) return;
	
	      if (!this.zIndex) {
	        var index_highest = 0;
	        $('div').each(function () {
	          var index_current = parseInt($(this).css('zIndex'), 10);
	          if (index_current > index_highest) {
	            index_highest = index_current;
	          }
	        });
	        this.zIndex = index_highest + 10;
	      }
	
	      var offset, top, left, containerOffset;
	      if (this.container instanceof $) {
	        containerOffset = this.container.offset();
	      } else {
	        containerOffset = $(this.container).offset();
	      }
	
	      if (this.component) {
	        offset = this.component.offset();
	        left = offset.left;
	        if (this.pickerPosition == 'bottom-left' || this.pickerPosition == 'top-left') {
	          left += this.component.outerWidth() - this.picker.outerWidth();
	        }
	      } else {
	        offset = this.element.offset();
	        left = offset.left;
	        if (this.pickerPosition == 'bottom-left' || this.pickerPosition == 'top-left') {
	          left += this.element.outerWidth() - this.picker.outerWidth();
	        }
	      }
	
	      var bodyWidth = document.body.clientWidth || window.innerWidth;
	      if (left + 220 > bodyWidth) {
	        left = bodyWidth - 220;
	      }
	
	      if (this.pickerPosition == 'top-left' || this.pickerPosition == 'top-right') {
	        top = offset.top - this.picker.outerHeight();
	      } else {
	        top = offset.top + this.height;
	      }
	
	      top = top - containerOffset.top;
	      left = left - containerOffset.left;
	
	      this.picker.css({
	        top:    top,
	        left:   left,
	        zIndex: this.zIndex
	      });
	    },
	
	    update: function () {
	      var date, fromArgs = false;
	      if (arguments && arguments.length && (typeof arguments[0] === 'string' || arguments[0] instanceof Date)) {
	        date = arguments[0];
	        fromArgs = true;
	      } else {
	        date = (this.isInput ? this.element.val() : this.element.find('input').val()) || this.element.data('date') || this.initialDate;
	        if (typeof date == 'string' || date instanceof String) {
	          date = date.replace(/^\s+|\s+$/g,'');
	        }
	      }
	
	      if (!date) {
	        date = new Date();
	        fromArgs = false;
	      }
	
	      this.date = DPGlobal.parseDate(date, this.format, this.language, this.formatType);
	
	      if (fromArgs) this.setValue();
	
	      if (this.date < this.startDate) {
	        this.viewDate = new Date(this.startDate);
	      } else if (this.date > this.endDate) {
	        this.viewDate = new Date(this.endDate);
	      } else {
	        this.viewDate = new Date(this.date);
	      }
	      this.fill();
	    },
	
	    fillDow: function () {
	      var dowCnt = this.weekStart,
	        html = '<tr>';
	      while (dowCnt < this.weekStart + 7) {
	        html += '<th class="dow">' + dates[this.language].daysMin[(dowCnt++) % 7] + '</th>';
	      }
	      html += '</tr>';
	      this.picker.find('.datetimepicker-days thead').append(html);
	    },
	
	    fillMonths: function () {
	      var html = '',
	        i = 0;
	      while (i < 12) {
	        html += '<span class="month">' + dates[this.language].monthsShort[i++] + '</span>';
	      }
	      this.picker.find('.datetimepicker-months td').html(html);
	    },
	
	    fill: function () {
	      if (this.date == null || this.viewDate == null) {
	        return;
	      }
	      var d = new Date(this.viewDate),
	        year = d.getUTCFullYear(),
	        month = d.getUTCMonth(),
	        dayMonth = d.getUTCDate(),
	        hours = d.getUTCHours(),
	        minutes = d.getUTCMinutes(),
	        startYear = this.startDate !== -Infinity ? this.startDate.getUTCFullYear() : -Infinity,
	        startMonth = this.startDate !== -Infinity ? this.startDate.getUTCMonth() + 1 : -Infinity,
	        endYear = this.endDate !== Infinity ? this.endDate.getUTCFullYear() : Infinity,
	        endMonth = this.endDate !== Infinity ? this.endDate.getUTCMonth() + 1 : Infinity,
	        currentDate = (new UTCDate(this.date.getUTCFullYear(), this.date.getUTCMonth(), this.date.getUTCDate())).valueOf(),
	        today = new Date();
	      this.setTitle('.datetimepicker-days', dates[this.language].months[month] + ' ' + year)
	      if (this.formatViewType == 'time') {
	        var formatted = this.getFormattedDate();
	        this.setTitle('.datetimepicker-hours', formatted);
	        this.setTitle('.datetimepicker-minutes', formatted);
	      } else {
	        this.setTitle('.datetimepicker-hours', dayMonth + ' ' + dates[this.language].months[month] + ' ' + year);
	        this.setTitle('.datetimepicker-minutes', dayMonth + ' ' + dates[this.language].months[month] + ' ' + year);
	      }
	      this.picker.find('tfoot th.today')
	        .text(dates[this.language].today || dates['en'].today)
	        .toggle(this.todayBtn !== false);
	      this.picker.find('tfoot th.clear')
	        .text(dates[this.language].clear || dates['en'].clear)
	        .toggle(this.clearBtn !== false);
	      this.updateNavArrows();
	      this.fillMonths();
	      /*var prevMonth = UTCDate(year, month, 0,0,0,0,0);
	       prevMonth.setUTCDate(prevMonth.getDate() - (prevMonth.getUTCDay() - this.weekStart + 7)%7);*/
	      var prevMonth = UTCDate(year, month - 1, 28, 0, 0, 0, 0),
	        day = DPGlobal.getDaysInMonth(prevMonth.getUTCFullYear(), prevMonth.getUTCMonth());
	      prevMonth.setUTCDate(day);
	      prevMonth.setUTCDate(day - (prevMonth.getUTCDay() - this.weekStart + 7) % 7);
	      var nextMonth = new Date(prevMonth);
	      nextMonth.setUTCDate(nextMonth.getUTCDate() + 42);
	      nextMonth = nextMonth.valueOf();
	      var html = [];
	      var clsName;
	      while (prevMonth.valueOf() < nextMonth) {
	        if (prevMonth.getUTCDay() == this.weekStart) {
	          html.push('<tr>');
	        }
	        clsName = '';
	        if (prevMonth.getUTCFullYear() < year || (prevMonth.getUTCFullYear() == year && prevMonth.getUTCMonth() < month)) {
	          clsName += ' old';
	        } else if (prevMonth.getUTCFullYear() > year || (prevMonth.getUTCFullYear() == year && prevMonth.getUTCMonth() > month)) {
	          clsName += ' new';
	        }
	        // Compare internal UTC date with local today, not UTC today
	        if (this.todayHighlight &&
	          prevMonth.getUTCFullYear() == today.getFullYear() &&
	          prevMonth.getUTCMonth() == today.getMonth() &&
	          prevMonth.getUTCDate() == today.getDate()) {
	          clsName += ' today';
	        }
	        if (prevMonth.valueOf() == currentDate) {
	          clsName += ' active';
	        }
	        if ((prevMonth.valueOf() + 86400000) <= this.startDate || prevMonth.valueOf() > this.endDate ||
	          $.inArray(prevMonth.getUTCDay(), this.daysOfWeekDisabled) !== -1 ||
						$.inArray(prevMonth.toDateString(), this.datesDisabled) !== -1) {
	          clsName += ' disabled';
	        }
	        html.push('<td class="day' + clsName + '">' + prevMonth.getUTCDate() + '</td>');
	        if (prevMonth.getUTCDay() == this.weekEnd) {
	          html.push('</tr>');
	        }
	        prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
	      }
	      this.picker.find('.datetimepicker-days tbody').empty().append(html.join(''));
	
	      html = [];
	      var txt = '', meridian = '', meridianOld = '';
	      var hoursDisabled = this.hoursDisabled || [];
	      for (var i = 0; i < 24; i++) {
	        if (hoursDisabled.indexOf(i) !== -1) continue;
	        var actual = UTCDate(year, month, dayMonth, i);
	        clsName = '';
	        // We want the previous hour for the startDate
	        if ((actual.valueOf() + 3600000) <= this.startDate || actual.valueOf() > this.endDate) {
	          clsName += ' disabled';
	        } else if (hours == i) {
	          clsName += ' active';
	        }
	        if (this.showMeridian && dates[this.language].meridiem.length == 2) {
	          meridian = (i < 12 ? dates[this.language].meridiem[0] : dates[this.language].meridiem[1]);
	          if (meridian != meridianOld) {
	            if (meridianOld != '') {
	              html.push('</fieldset>');
	            }
	            html.push('<fieldset class="hour"><legend>' + meridian.toUpperCase() + '</legend>');
	          }
	          meridianOld = meridian;
	          txt = (i % 12 ? i % 12 : 12);
	          html.push('<span class="hour' + clsName + ' hour_' + (i < 12 ? 'am' : 'pm') + '">' + txt + '</span>');
	          if (i == 23) {
	            html.push('</fieldset>');
	          }
	        } else {
	          txt = i + ':00';
	          html.push('<span class="hour' + clsName + '">' + txt + '</span>');
	        }
	      }
	      this.picker.find('.datetimepicker-hours td').html(html.join(''));
	
	      html = [];
	      txt = '', meridian = '', meridianOld = '';
	      var minutesDisabled = this.minutesDisabled || [];
	      for (var i = 0; i < 60; i += this.minuteStep) {
	        if (minutesDisabled.indexOf(i) !== -1) continue;
	        var actual = UTCDate(year, month, dayMonth, hours, i, 0);
	        clsName = '';
	        if (actual.valueOf() < this.startDate || actual.valueOf() > this.endDate) {
	          clsName += ' disabled';
	        } else if (Math.floor(minutes / this.minuteStep) == Math.floor(i / this.minuteStep)) {
	          clsName += ' active';
	        }
	        if (this.showMeridian && dates[this.language].meridiem.length == 2) {
	          meridian = (hours < 12 ? dates[this.language].meridiem[0] : dates[this.language].meridiem[1]);
	          if (meridian != meridianOld) {
	            if (meridianOld != '') {
	              html.push('</fieldset>');
	            }
	            html.push('<fieldset class="minute"><legend>' + meridian.toUpperCase() + '</legend>');
	          }
	          meridianOld = meridian;
	          txt = (hours % 12 ? hours % 12 : 12);
	          //html.push('<span class="minute'+clsName+' minute_'+(hours<12?'am':'pm')+'">'+txt+'</span>');
	          html.push('<span class="minute' + clsName + '">' + txt + ':' + (i < 10 ? '0' + i : i) + '</span>');
	          if (i == 59) {
	            html.push('</fieldset>');
	          }
	        } else {
	          txt = i + ':00';
	          //html.push('<span class="hour'+clsName+'">'+txt+'</span>');
	          html.push('<span class="minute' + clsName + '">' + hours + ':' + (i < 10 ? '0' + i : i) + '</span>');
	        }
	      }
	      this.picker.find('.datetimepicker-minutes td').html(html.join(''));
	
	      var currentYear = this.date.getUTCFullYear();
	      var months = this.setTitle('.datetimepicker-months', year)
	        .end()
	        .find('span').removeClass('active');
	      if (currentYear == year) {
	        // getUTCMonths() returns 0 based, and we need to select the next one
	        // To cater bootstrap 2 we don't need to select the next one
	        var offset = months.length - 12;
	        months.eq(this.date.getUTCMonth() + offset).addClass('active');
	      }
	      if (year < startYear || year > endYear) {
	        months.addClass('disabled');
	      }
	      if (year == startYear) {
	        months.slice(0, startMonth + 1).addClass('disabled');
	      }
	      if (year == endYear) {
	        months.slice(endMonth).addClass('disabled');
	      }
	
	      html = '';
	      year = parseInt(year / 10, 10) * 10;
	      var yearCont = this.setTitle('.datetimepicker-years', year + '-' + (year + 9))
	        .end()
	        .find('td');
	      year -= 1;
	      for (var i = -1; i < 11; i++) {
	        html += '<span class="year' + (i == -1 || i == 10 ? ' old' : '') + (currentYear == year ? ' active' : '') + (year < startYear || year > endYear ? ' disabled' : '') + '">' + year + '</span>';
	        year += 1;
	      }
	      yearCont.html(html);
	      this.place();
	    },
	
	    updateNavArrows: function () {
	      var d = new Date(this.viewDate),
	        year = d.getUTCFullYear(),
	        month = d.getUTCMonth(),
	        day = d.getUTCDate(),
	        hour = d.getUTCHours();
	      switch (this.viewMode) {
	        case 0:
	          if (this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear()
	            && month <= this.startDate.getUTCMonth()
	            && day <= this.startDate.getUTCDate()
	            && hour <= this.startDate.getUTCHours()) {
	            this.picker.find('.prev').css({visibility: 'hidden'});
	          } else {
	            this.picker.find('.prev').css({visibility: 'visible'});
	          }
	          if (this.endDate !== Infinity && year >= this.endDate.getUTCFullYear()
	            && month >= this.endDate.getUTCMonth()
	            && day >= this.endDate.getUTCDate()
	            && hour >= this.endDate.getUTCHours()) {
	            this.picker.find('.next').css({visibility: 'hidden'});
	          } else {
	            this.picker.find('.next').css({visibility: 'visible'});
	          }
	          break;
	        case 1:
	          if (this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear()
	            && month <= this.startDate.getUTCMonth()
	            && day <= this.startDate.getUTCDate()) {
	            this.picker.find('.prev').css({visibility: 'hidden'});
	          } else {
	            this.picker.find('.prev').css({visibility: 'visible'});
	          }
	          if (this.endDate !== Infinity && year >= this.endDate.getUTCFullYear()
	            && month >= this.endDate.getUTCMonth()
	            && day >= this.endDate.getUTCDate()) {
	            this.picker.find('.next').css({visibility: 'hidden'});
	          } else {
	            this.picker.find('.next').css({visibility: 'visible'});
	          }
	          break;
	        case 2:
	          if (this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear()
	            && month <= this.startDate.getUTCMonth()) {
	            this.picker.find('.prev').css({visibility: 'hidden'});
	          } else {
	            this.picker.find('.prev').css({visibility: 'visible'});
	          }
	          if (this.endDate !== Infinity && year >= this.endDate.getUTCFullYear()
	            && month >= this.endDate.getUTCMonth()) {
	            this.picker.find('.next').css({visibility: 'hidden'});
	          } else {
	            this.picker.find('.next').css({visibility: 'visible'});
	          }
	          break;
	        case 3:
	        case 4:
	          if (this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear()) {
	            this.picker.find('.prev').css({visibility: 'hidden'});
	          } else {
	            this.picker.find('.prev').css({visibility: 'visible'});
	          }
	          if (this.endDate !== Infinity && year >= this.endDate.getUTCFullYear()) {
	            this.picker.find('.next').css({visibility: 'hidden'});
	          } else {
	            this.picker.find('.next').css({visibility: 'visible'});
	          }
	          break;
	      }
	    },
	
	    mousewheel: function (e) {
	
	      e.preventDefault();
	      e.stopPropagation();
	
	      if (this.wheelPause) {
	        return;
	      }
	
	      this.wheelPause = true;
	
	      var originalEvent = e.originalEvent;
	
	      var delta = originalEvent.wheelDelta;
	
	      var mode = delta > 0 ? 1 : (delta === 0) ? 0 : -1;
	
	      if (this.wheelViewModeNavigationInverseDirection) {
	        mode = -mode;
	      }
	
	      this.showMode(mode);
	
	      setTimeout($.proxy(function () {
	
	        this.wheelPause = false
	
	      }, this), this.wheelViewModeNavigationDelay);
	
	    },
	
	    click: function (e) {
	      e.stopPropagation();
	      e.preventDefault();
	      var target = $(e.target).closest('span, td, th, legend');
	      if (target.is('.' + this.icontype)) {
	        target = $(target).parent().closest('span, td, th, legend');
	      }
	      if (target.length == 1) {
	        if (target.is('.disabled')) {
	          this.element.trigger({
	            type:      'outOfRange',
	            date:      this.viewDate,
	            startDate: this.startDate,
	            endDate:   this.endDate
	          });
	          return;
	        }
	        switch (target[0].nodeName.toLowerCase()) {
	          case 'th':
	            switch (target[0].className) {
	              case 'switch':
	                this.showMode(1);
	                break;
	              case 'prev':
	              case 'next':
	                var dir = DPGlobal.modes[this.viewMode].navStep * (target[0].className == 'prev' ? -1 : 1);
	                switch (this.viewMode) {
	                  case 0:
	                    this.viewDate = this.moveHour(this.viewDate, dir);
	                    break;
	                  case 1:
	                    this.viewDate = this.moveDate(this.viewDate, dir);
	                    break;
	                  case 2:
	                    this.viewDate = this.moveMonth(this.viewDate, dir);
	                    break;
	                  case 3:
	                  case 4:
	                    this.viewDate = this.moveYear(this.viewDate, dir);
	                    break;
	                }
	                this.fill();
	                this.element.trigger({
	                  type:      target[0].className + ':' + this.convertViewModeText(this.viewMode),
	                  date:      this.viewDate,
	                  startDate: this.startDate,
	                  endDate:   this.endDate
	                });
	                break;
	              case 'clear':
	                this.reset();
	                if (this.autoclose) {
	                  this.hide();
	                }
	                break;
	              case 'today':
	                var date = new Date();
	                date = UTCDate(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), 0);
	
	                // Respect startDate and endDate.
	                if (date < this.startDate) date = this.startDate;
	                else if (date > this.endDate) date = this.endDate;
	
	                this.viewMode = this.startViewMode;
	                this.showMode(0);
	                this._setDate(date);
	                this.fill();
	                if (this.autoclose) {
	                  this.hide();
	                }
	                break;
	            }
	            break;
	          case 'span':
	            if (!target.is('.disabled')) {
	              var year = this.viewDate.getUTCFullYear(),
	                month = this.viewDate.getUTCMonth(),
	                day = this.viewDate.getUTCDate(),
	                hours = this.viewDate.getUTCHours(),
	                minutes = this.viewDate.getUTCMinutes(),
	                seconds = this.viewDate.getUTCSeconds();
	
	              if (target.is('.month')) {
	                this.viewDate.setUTCDate(1);
	                month = target.parent().find('span').index(target);
	                day = this.viewDate.getUTCDate();
	                this.viewDate.setUTCMonth(month);
	                this.element.trigger({
	                  type: 'changeMonth',
	                  date: this.viewDate
	                });
	                if (this.viewSelect >= 3) {
	                  this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
	                }
	              } else if (target.is('.year')) {
	                this.viewDate.setUTCDate(1);
	                year = parseInt(target.text(), 10) || 0;
	                this.viewDate.setUTCFullYear(year);
	                this.element.trigger({
	                  type: 'changeYear',
	                  date: this.viewDate
	                });
	                if (this.viewSelect >= 4) {
	                  this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
	                }
	              } else if (target.is('.hour')) {
	                hours = parseInt(target.text(), 10) || 0;
	                if (target.hasClass('hour_am') || target.hasClass('hour_pm')) {
	                  if (hours == 12 && target.hasClass('hour_am')) {
	                    hours = 0;
	                  } else if (hours != 12 && target.hasClass('hour_pm')) {
	                    hours += 12;
	                  }
	                }
	                this.viewDate.setUTCHours(hours);
	                this.element.trigger({
	                  type: 'changeHour',
	                  date: this.viewDate
	                });
	                if (this.viewSelect >= 1) {
	                  this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
	                }
	              } else if (target.is('.minute')) {
	                minutes = parseInt(target.text().substr(target.text().indexOf(':') + 1), 10) || 0;
	                this.viewDate.setUTCMinutes(minutes);
	                this.element.trigger({
	                  type: 'changeMinute',
	                  date: this.viewDate
	                });
	                if (this.viewSelect >= 0) {
	                  this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
	                }
	              }
	              if (this.viewMode != 0) {
	                var oldViewMode = this.viewMode;
	                this.showMode(-1);
	                this.fill();
	                if (oldViewMode == this.viewMode && this.autoclose) {
	                  this.hide();
	                }
	              } else {
	                this.fill();
	                if (this.autoclose) {
	                  this.hide();
	                }
	              }
	            }
	            break;
	          case 'td':
	            if (target.is('.day') && !target.is('.disabled')) {
	              var day = parseInt(target.text(), 10) || 1;
	              var year = this.viewDate.getUTCFullYear(),
	                month = this.viewDate.getUTCMonth(),
	                hours = this.viewDate.getUTCHours(),
	                minutes = this.viewDate.getUTCMinutes(),
	                seconds = this.viewDate.getUTCSeconds();
	              if (target.is('.old')) {
	                if (month === 0) {
	                  month = 11;
	                  year -= 1;
	                } else {
	                  month -= 1;
	                }
	              } else if (target.is('.new')) {
	                if (month == 11) {
	                  month = 0;
	                  year += 1;
	                } else {
	                  month += 1;
	                }
	              }
	              this.viewDate.setUTCFullYear(year);
	              this.viewDate.setUTCMonth(month, day);
	              this.element.trigger({
	                type: 'changeDay',
	                date: this.viewDate
	              });
	              if (this.viewSelect >= 2) {
	                this._setDate(UTCDate(year, month, day, hours, minutes, seconds, 0));
	              }
	            }
	            var oldViewMode = this.viewMode;
	            this.showMode(-1);
	            this.fill();
	            if (oldViewMode == this.viewMode && this.autoclose) {
	              this.hide();
	            }
	            break;
	        }
	      }
	    },
	
	    _setDate: function (date, which) {
	      if (!which || which == 'date')
	        this.date = date;
	      if (!which || which == 'view')
	        this.viewDate = date;
	      this.fill();
	      this.setValue();
	      var element;
	      if (this.isInput) {
	        element = this.element;
	      } else if (this.component) {
	        element = this.element.find('input');
	      }
	      if (element) {
	        element.change();
	        if (this.autoclose && (!which || which == 'date')) {
	          //this.hide();
	        }
	      }
	      this.element.trigger({
	        type: 'changeDate',
	        date: this.getDate()
	      });
	      if(date == null)
	        this.date = this.viewDate;
	    },
	
	    moveMinute: function (date, dir) {
	      if (!dir) return date;
	      var new_date = new Date(date.valueOf());
	      //dir = dir > 0 ? 1 : -1;
	      new_date.setUTCMinutes(new_date.getUTCMinutes() + (dir * this.minuteStep));
	      return new_date;
	    },
	
	    moveHour: function (date, dir) {
	      if (!dir) return date;
	      var new_date = new Date(date.valueOf());
	      //dir = dir > 0 ? 1 : -1;
	      new_date.setUTCHours(new_date.getUTCHours() + dir);
	      return new_date;
	    },
	
	    moveDate: function (date, dir) {
	      if (!dir) return date;
	      var new_date = new Date(date.valueOf());
	      //dir = dir > 0 ? 1 : -1;
	      new_date.setUTCDate(new_date.getUTCDate() + dir);
	      return new_date;
	    },
	
	    moveMonth: function (date, dir) {
	      if (!dir) return date;
	      var new_date = new Date(date.valueOf()),
	        day = new_date.getUTCDate(),
	        month = new_date.getUTCMonth(),
	        mag = Math.abs(dir),
	        new_month, test;
	      dir = dir > 0 ? 1 : -1;
	      if (mag == 1) {
	        test = dir == -1
	          // If going back one month, make sure month is not current month
	          // (eg, Mar 31 -> Feb 31 == Feb 28, not Mar 02)
	          ? function () {
	          return new_date.getUTCMonth() == month;
	        }
	          // If going forward one month, make sure month is as expected
	          // (eg, Jan 31 -> Feb 31 == Feb 28, not Mar 02)
	          : function () {
	          return new_date.getUTCMonth() != new_month;
	        };
	        new_month = month + dir;
	        new_date.setUTCMonth(new_month);
	        // Dec -> Jan (12) or Jan -> Dec (-1) -- limit expected date to 0-11
	        if (new_month < 0 || new_month > 11)
	          new_month = (new_month + 12) % 12;
	      } else {
	        // For magnitudes >1, move one month at a time...
	        for (var i = 0; i < mag; i++)
	          // ...which might decrease the day (eg, Jan 31 to Feb 28, etc)...
	          new_date = this.moveMonth(new_date, dir);
	        // ...then reset the day, keeping it in the new month
	        new_month = new_date.getUTCMonth();
	        new_date.setUTCDate(day);
	        test = function () {
	          return new_month != new_date.getUTCMonth();
	        };
	      }
	      // Common date-resetting loop -- if date is beyond end of month, make it
	      // end of month
	      while (test()) {
	        new_date.setUTCDate(--day);
	        new_date.setUTCMonth(new_month);
	      }
	      return new_date;
	    },
	
	    moveYear: function (date, dir) {
	      return this.moveMonth(date, dir * 12);
	    },
	
	    dateWithinRange: function (date) {
	      return date >= this.startDate && date <= this.endDate;
	    },
	
	    keydown: function (e) {
	      if (this.picker.is(':not(:visible)')) {
	        if (e.keyCode == 27) // allow escape to hide and re-show picker
	          this.show();
	        return;
	      }
	      var dateChanged = false,
	        dir, day, month,
	        newDate, newViewDate;
	      switch (e.keyCode) {
	        case 27: // escape
	          this.hide();
	          e.preventDefault();
	          break;
	        case 37: // left
	        case 39: // right
	          if (!this.keyboardNavigation) break;
	          dir = e.keyCode == 37 ? -1 : 1;
	          viewMode = this.viewMode;
	          if (e.ctrlKey) {
	            viewMode += 2;
	          } else if (e.shiftKey) {
	            viewMode += 1;
	          }
	          if (viewMode == 4) {
	            newDate = this.moveYear(this.date, dir);
	            newViewDate = this.moveYear(this.viewDate, dir);
	          } else if (viewMode == 3) {
	            newDate = this.moveMonth(this.date, dir);
	            newViewDate = this.moveMonth(this.viewDate, dir);
	          } else if (viewMode == 2) {
	            newDate = this.moveDate(this.date, dir);
	            newViewDate = this.moveDate(this.viewDate, dir);
	          } else if (viewMode == 1) {
	            newDate = this.moveHour(this.date, dir);
	            newViewDate = this.moveHour(this.viewDate, dir);
	          } else if (viewMode == 0) {
	            newDate = this.moveMinute(this.date, dir);
	            newViewDate = this.moveMinute(this.viewDate, dir);
	          }
	          if (this.dateWithinRange(newDate)) {
	            this.date = newDate;
	            this.viewDate = newViewDate;
	            this.setValue();
	            this.update();
	            e.preventDefault();
	            dateChanged = true;
	          }
	          break;
	        case 38: // up
	        case 40: // down
	          if (!this.keyboardNavigation) break;
	          dir = e.keyCode == 38 ? -1 : 1;
	          viewMode = this.viewMode;
	          if (e.ctrlKey) {
	            viewMode += 2;
	          } else if (e.shiftKey) {
	            viewMode += 1;
	          }
	          if (viewMode == 4) {
	            newDate = this.moveYear(this.date, dir);
	            newViewDate = this.moveYear(this.viewDate, dir);
	          } else if (viewMode == 3) {
	            newDate = this.moveMonth(this.date, dir);
	            newViewDate = this.moveMonth(this.viewDate, dir);
	          } else if (viewMode == 2) {
	            newDate = this.moveDate(this.date, dir * 7);
	            newViewDate = this.moveDate(this.viewDate, dir * 7);
	          } else if (viewMode == 1) {
	            if (this.showMeridian) {
	              newDate = this.moveHour(this.date, dir * 6);
	              newViewDate = this.moveHour(this.viewDate, dir * 6);
	            } else {
	              newDate = this.moveHour(this.date, dir * 4);
	              newViewDate = this.moveHour(this.viewDate, dir * 4);
	            }
	          } else if (viewMode == 0) {
	            newDate = this.moveMinute(this.date, dir * 4);
	            newViewDate = this.moveMinute(this.viewDate, dir * 4);
	          }
	          if (this.dateWithinRange(newDate)) {
	            this.date = newDate;
	            this.viewDate = newViewDate;
	            this.setValue();
	            this.update();
	            e.preventDefault();
	            dateChanged = true;
	          }
	          break;
	        case 13: // enter
	          if (this.viewMode != 0) {
	            var oldViewMode = this.viewMode;
	            this.showMode(-1);
	            this.fill();
	            if (oldViewMode == this.viewMode && this.autoclose) {
	              this.hide();
	            }
	          } else {
	            this.fill();
	            if (this.autoclose) {
	              this.hide();
	            }
	          }
	          e.preventDefault();
	          break;
	        case 9: // tab
	          this.hide();
	          break;
	      }
	      if (dateChanged) {
	        var element;
	        if (this.isInput) {
	          element = this.element;
	        } else if (this.component) {
	          element = this.element.find('input');
	        }
	        if (element) {
	          element.change();
	        }
	        this.element.trigger({
	          type: 'changeDate',
	          date: this.getDate()
	        });
	      }
	    },
	
	    showMode: function (dir) {
	      if (dir) {
	        var newViewMode = Math.max(0, Math.min(DPGlobal.modes.length - 1, this.viewMode + dir));
	        if (newViewMode >= this.minView && newViewMode <= this.maxView) {
	          this.element.trigger({
	            type:        'changeMode',
	            date:        this.viewDate,
	            oldViewMode: this.viewMode,
	            newViewMode: newViewMode
	          });
	
	          this.viewMode = newViewMode;
	        }
	      }
	      /*
	       vitalets: fixing bug of very special conditions:
	       jquery 1.7.1 + webkit + show inline datetimepicker in bootstrap popover.
	       Method show() does not set display css correctly and datetimepicker is not shown.
	       Changed to .css('display', 'block') solve the problem.
	       See https://github.com/vitalets/x-editable/issues/37
	
	       In jquery 1.7.2+ everything works fine.
	       */
	      //this.picker.find('>div').hide().filter('.datetimepicker-'+DPGlobal.modes[this.viewMode].clsName).show();
	      this.picker.find('>div').hide().filter('.datetimepicker-' + DPGlobal.modes[this.viewMode].clsName).css('display', 'block');
	      this.updateNavArrows();
	    },
	
	    reset: function (e) {
	      this._setDate(null, 'date');
	    },
	
	    convertViewModeText:  function (viewMode) {
	      switch (viewMode) {
	        case 4:
	          return 'decade';
	        case 3:
	          return 'year';
	        case 2:
	          return 'month';
	        case 1:
	          return 'day';
	        case 0:
	          return 'hour';
	      }
	    }
	  };
	
	  var old = $.fn.datetimepicker;
	  $.fn.datetimepicker = function (option) {
	    var args = Array.apply(null, arguments);
	    args.shift();
	    var internal_return;
	    this.each(function () {
	      var $this = $(this),
	        data = $this.data('datetimepicker'),
	        options = typeof option == 'object' && option;
	      if (!data) {
	        $this.data('datetimepicker', (data = new Datetimepicker(this, $.extend({}, $.fn.datetimepicker.defaults, options))));
	      }
	      if (typeof option == 'string' && typeof data[option] == 'function') {
	        internal_return = data[option].apply(data, args);
	        if (internal_return !== undefined) {
	          return false;
	        }
	      }
	    });
	    if (internal_return !== undefined)
	      return internal_return;
	    else
	      return this;
	  };
	
	  $.fn.datetimepicker.defaults = {
	  };
	  $.fn.datetimepicker.Constructor = Datetimepicker;
	  var dates = $.fn.datetimepicker.dates = {
	    en: {
	      days:        ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
	      daysShort:   ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
	      daysMin:     ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
	      months:      ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
	      monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
	      meridiem:    ['am', 'pm'],
	      suffix:      ['st', 'nd', 'rd', 'th'],
	      today:       'Today',
	      clear:       'Clear'
	    }
	  };
	
	  var DPGlobal = {
	    modes:            [
	      {
	        clsName: 'minutes',
	        navFnc:  'Hours',
	        navStep: 1
	      },
	      {
	        clsName: 'hours',
	        navFnc:  'Date',
	        navStep: 1
	      },
	      {
	        clsName: 'days',
	        navFnc:  'Month',
	        navStep: 1
	      },
	      {
	        clsName: 'months',
	        navFnc:  'FullYear',
	        navStep: 1
	      },
	      {
	        clsName: 'years',
	        navFnc:  'FullYear',
	        navStep: 10
	      }
	    ],
	    isLeapYear:       function (year) {
	      return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0))
	    },
	    getDaysInMonth:   function (year, month) {
	      return [31, (DPGlobal.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month]
	    },
	    getDefaultFormat: function (type, field) {
	      if (type == 'standard') {
	        if (field == 'input')
	          return 'yyyy-mm-dd hh:ii';
	        else
	          return 'yyyy-mm-dd hh:ii:ss';
	      } else if (type == 'php') {
	        if (field == 'input')
	          return 'Y-m-d H:i';
	        else
	          return 'Y-m-d H:i:s';
	      } else {
	        throw new Error('Invalid format type.');
	      }
	    },
	    validParts: function (type) {
	      if (type == 'standard') {
	        return /t|hh?|HH?|p|P|ii?|ss?|dd?|DD?|mm?|MM?|yy(?:yy)?/g;
	      } else if (type == 'php') {
	        return /[dDjlNwzFmMnStyYaABgGhHis]/g;
	      } else {
	        throw new Error('Invalid format type.');
	      }
	    },
	    nonpunctuation: /[^ -\/:-@\[-`{-~\t\n\rTZ]+/g,
	    parseFormat: function (format, type) {
	      // IE treats \0 as a string end in inputs (truncating the value),
	      // so it's a bad format delimiter, anyway
	      var separators = format.replace(this.validParts(type), '\0').split('\0'),
	        parts = format.match(this.validParts(type));
	      if (!separators || !separators.length || !parts || parts.length == 0) {
	        throw new Error('Invalid date format.');
	      }
	      return {separators: separators, parts: parts};
	    },
	    parseDate: function (date, format, language, type) {
	      if (date instanceof Date) {
	        var dateUTC = new Date(date.valueOf() - date.getTimezoneOffset() * 60000);
	        dateUTC.setMilliseconds(0);
	        return dateUTC;
	      }
	      if (/^\d{4}\-\d{1,2}\-\d{1,2}$/.test(date)) {
	        format = this.parseFormat('yyyy-mm-dd', type);
	      }
	      if (/^\d{4}\-\d{1,2}\-\d{1,2}[T ]\d{1,2}\:\d{1,2}$/.test(date)) {
	        format = this.parseFormat('yyyy-mm-dd hh:ii', type);
	      }
	      if (/^\d{4}\-\d{1,2}\-\d{1,2}[T ]\d{1,2}\:\d{1,2}\:\d{1,2}[Z]{0,1}$/.test(date)) {
	        format = this.parseFormat('yyyy-mm-dd hh:ii:ss', type);
	      }
	      if (/^[-+]\d+[dmwy]([\s,]+[-+]\d+[dmwy])*$/.test(date)) {
	        var part_re = /([-+]\d+)([dmwy])/,
	          parts = date.match(/([-+]\d+)([dmwy])/g),
	          part, dir;
	        date = new Date();
	        for (var i = 0; i < parts.length; i++) {
	          part = part_re.exec(parts[i]);
	          dir = parseInt(part[1]);
	          switch (part[2]) {
	            case 'd':
	              date.setUTCDate(date.getUTCDate() + dir);
	              break;
	            case 'm':
	              date = Datetimepicker.prototype.moveMonth.call(Datetimepicker.prototype, date, dir);
	              break;
	            case 'w':
	              date.setUTCDate(date.getUTCDate() + dir * 7);
	              break;
	            case 'y':
	              date = Datetimepicker.prototype.moveYear.call(Datetimepicker.prototype, date, dir);
	              break;
	          }
	        }
	        return UTCDate(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds(), 0);
	      }
	      var parts = date && date.toString().match(this.nonpunctuation) || [],
	        date = new Date(0, 0, 0, 0, 0, 0, 0),
	        parsed = {},
	        setters_order = ['hh', 'h', 'ii', 'i', 'ss', 's', 'yyyy', 'yy', 'M', 'MM', 'm', 'mm', 'D', 'DD', 'd', 'dd', 'H', 'HH', 'p', 'P'],
	        setters_map = {
	          hh:   function (d, v) {
	            return d.setUTCHours(v);
	          },
	          h:    function (d, v) {
	            return d.setUTCHours(v);
	          },
	          HH:   function (d, v) {
	            return d.setUTCHours(v == 12 ? 0 : v);
	          },
	          H:    function (d, v) {
	            return d.setUTCHours(v == 12 ? 0 : v);
	          },
	          ii:   function (d, v) {
	            return d.setUTCMinutes(v);
	          },
	          i:    function (d, v) {
	            return d.setUTCMinutes(v);
	          },
	          ss:   function (d, v) {
	            return d.setUTCSeconds(v);
	          },
	          s:    function (d, v) {
	            return d.setUTCSeconds(v);
	          },
	          yyyy: function (d, v) {
	            return d.setUTCFullYear(v);
	          },
	          yy:   function (d, v) {
	            return d.setUTCFullYear(2000 + v);
	          },
	          m:    function (d, v) {
	            v -= 1;
	            while (v < 0) v += 12;
	            v %= 12;
	            d.setUTCMonth(v);
	            while (d.getUTCMonth() != v)
	              if (isNaN(d.getUTCMonth()))
	                return d;
	              else
	                d.setUTCDate(d.getUTCDate() - 1);
	            return d;
	          },
	          d:    function (d, v) {
	            return d.setUTCDate(v);
	          },
	          p:    function (d, v) {
	            return d.setUTCHours(v == 1 ? d.getUTCHours() + 12 : d.getUTCHours());
	          }
	        },
	        val, filtered, part;
	      setters_map['M'] = setters_map['MM'] = setters_map['mm'] = setters_map['m'];
	      setters_map['dd'] = setters_map['d'];
	      setters_map['P'] = setters_map['p'];
	      date = UTCDate(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds());
	      if (parts.length == format.parts.length) {
	        for (var i = 0, cnt = format.parts.length; i < cnt; i++) {
	          val = parseInt(parts[i], 10);
	          part = format.parts[i];
	          if (isNaN(val)) {
	            switch (part) {
	              case 'MM':
	                filtered = $(dates[language].months).filter(function () {
	                  var m = this.slice(0, parts[i].length),
	                    p = parts[i].slice(0, m.length);
	                  return m == p;
	                });
	                val = $.inArray(filtered[0], dates[language].months) + 1;
	                break;
	              case 'M':
	                filtered = $(dates[language].monthsShort).filter(function () {
	                  var m = this.slice(0, parts[i].length),
	                    p = parts[i].slice(0, m.length);
	                  return m.toLowerCase() == p.toLowerCase();
	                });
	                val = $.inArray(filtered[0], dates[language].monthsShort) + 1;
	                break;
	              case 'p':
	              case 'P':
	                val = $.inArray(parts[i].toLowerCase(), dates[language].meridiem);
	                break;
	            }
	          }
	          parsed[part] = val;
	        }
	        for (var i = 0, s; i < setters_order.length; i++) {
	          s = setters_order[i];
	          if (s in parsed && !isNaN(parsed[s]))
	            setters_map[s](date, parsed[s])
	        }
	      }
	      return date;
	    },
	    formatDate:       function (date, format, language, type) {
	      if (date == null) {
	        return '';
	      }
	      var val;
	      if (type == 'standard') {
	        val = {
	          t:    date.getTime(),
	          // year
	          yy:   date.getUTCFullYear().toString().substring(2),
	          yyyy: date.getUTCFullYear(),
	          // month
	          m:    date.getUTCMonth() + 1,
	          M:    dates[language].monthsShort[date.getUTCMonth()],
	          MM:   dates[language].months[date.getUTCMonth()],
	          // day
	          d:    date.getUTCDate(),
	          D:    dates[language].daysShort[date.getUTCDay()],
	          DD:   dates[language].days[date.getUTCDay()],
	          p:    (dates[language].meridiem.length == 2 ? dates[language].meridiem[date.getUTCHours() < 12 ? 0 : 1] : ''),
	          // hour
	          h:    date.getUTCHours(),
	          // minute
	          i:    date.getUTCMinutes(),
	          // second
	          s:    date.getUTCSeconds()
	        };
	
	        if (dates[language].meridiem.length == 2) {
	          val.H = (val.h % 12 == 0 ? 12 : val.h % 12);
	        }
	        else {
	          val.H = val.h;
	        }
	        val.HH = (val.H < 10 ? '0' : '') + val.H;
	        val.P = val.p.toUpperCase();
	        val.hh = (val.h < 10 ? '0' : '') + val.h;
	        val.ii = (val.i < 10 ? '0' : '') + val.i;
	        val.ss = (val.s < 10 ? '0' : '') + val.s;
	        val.dd = (val.d < 10 ? '0' : '') + val.d;
	        val.mm = (val.m < 10 ? '0' : '') + val.m;
	      } else if (type == 'php') {
	        // php format
	        val = {
	          // year
	          y: date.getUTCFullYear().toString().substring(2),
	          Y: date.getUTCFullYear(),
	          // month
	          F: dates[language].months[date.getUTCMonth()],
	          M: dates[language].monthsShort[date.getUTCMonth()],
	          n: date.getUTCMonth() + 1,
	          t: DPGlobal.getDaysInMonth(date.getUTCFullYear(), date.getUTCMonth()),
	          // day
	          j: date.getUTCDate(),
	          l: dates[language].days[date.getUTCDay()],
	          D: dates[language].daysShort[date.getUTCDay()],
	          w: date.getUTCDay(), // 0 -> 6
	          N: (date.getUTCDay() == 0 ? 7 : date.getUTCDay()),       // 1 -> 7
	          S: (date.getUTCDate() % 10 <= dates[language].suffix.length ? dates[language].suffix[date.getUTCDate() % 10 - 1] : ''),
	          // hour
	          a: (dates[language].meridiem.length == 2 ? dates[language].meridiem[date.getUTCHours() < 12 ? 0 : 1] : ''),
	          g: (date.getUTCHours() % 12 == 0 ? 12 : date.getUTCHours() % 12),
	          G: date.getUTCHours(),
	          // minute
	          i: date.getUTCMinutes(),
	          // second
	          s: date.getUTCSeconds()
	        };
	        val.m = (val.n < 10 ? '0' : '') + val.n;
	        val.d = (val.j < 10 ? '0' : '') + val.j;
	        val.A = val.a.toString().toUpperCase();
	        val.h = (val.g < 10 ? '0' : '') + val.g;
	        val.H = (val.G < 10 ? '0' : '') + val.G;
	        val.i = (val.i < 10 ? '0' : '') + val.i;
	        val.s = (val.s < 10 ? '0' : '') + val.s;
	      } else {
	        throw new Error('Invalid format type.');
	      }
	      var date = [],
	        seps = $.extend([], format.separators);
	      for (var i = 0, cnt = format.parts.length; i < cnt; i++) {
	        if (seps.length) {
	          date.push(seps.shift());
	        }
	        date.push(val[format.parts[i]]);
	      }
	      if (seps.length) {
	        date.push(seps.shift());
	      }
	      return date.join('');
	    },
	    convertViewMode:  function (viewMode) {
	      switch (viewMode) {
	        case 4:
	        case 'decade':
	          viewMode = 4;
	          break;
	        case 3:
	        case 'year':
	          viewMode = 3;
	          break;
	        case 2:
	        case 'month':
	          viewMode = 2;
	          break;
	        case 1:
	        case 'day':
	          viewMode = 1;
	          break;
	        case 0:
	        case 'hour':
	          viewMode = 0;
	          break;
	      }
	
	      return viewMode;
	    },
	    headTemplate: '<thead>' +
	                '<tr>' +
	                '<th class="prev"><i class="{iconType} {leftArrow}"/></th>' +
	                '<th colspan="5" class="switch"></th>' +
	                '<th class="next"><i class="{iconType} {rightArrow}"/></th>' +
	                '</tr>' +
	      '</thead>',
	    headTemplateV3: '<thead>' +
	                '<tr>' +
	                '<th class="prev"><span class="{iconType} {leftArrow}"></span> </th>' +
	                '<th colspan="5" class="switch"></th>' +
	                '<th class="next"><span class="{iconType} {rightArrow}"></span> </th>' +
	                '</tr>' +
	      '</thead>',
	    contTemplate: '<tbody><tr><td colspan="7"></td></tr></tbody>',
	    footTemplate: '<tfoot>' + 
	                    '<tr><th colspan="7" class="today"></th></tr>' +
	                    '<tr><th colspan="7" class="clear"></th></tr>' +
	                  '</tfoot>'
	  };
	  DPGlobal.template = '<div class="datetimepicker">' +
	    '<div class="datetimepicker-minutes">' +
	    '<table class=" table-condensed">' +
	    DPGlobal.headTemplate +
	    DPGlobal.contTemplate +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '<div class="datetimepicker-hours">' +
	    '<table class=" table-condensed">' +
	    DPGlobal.headTemplate +
	    DPGlobal.contTemplate +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '<div class="datetimepicker-days">' +
	    '<table class=" table-condensed">' +
	    DPGlobal.headTemplate +
	    '<tbody></tbody>' +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '<div class="datetimepicker-months">' +
	    '<table class="table-condensed">' +
	    DPGlobal.headTemplate +
	    DPGlobal.contTemplate +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '<div class="datetimepicker-years">' +
	    '<table class="table-condensed">' +
	    DPGlobal.headTemplate +
	    DPGlobal.contTemplate +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '</div>';
	  DPGlobal.templateV3 = '<div class="datetimepicker">' +
	    '<div class="datetimepicker-minutes">' +
	    '<table class=" table-condensed">' +
	    DPGlobal.headTemplateV3 +
	    DPGlobal.contTemplate +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '<div class="datetimepicker-hours">' +
	    '<table class=" table-condensed">' +
	    DPGlobal.headTemplateV3 +
	    DPGlobal.contTemplate +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '<div class="datetimepicker-days">' +
	    '<table class=" table-condensed">' +
	    DPGlobal.headTemplateV3 +
	    '<tbody></tbody>' +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '<div class="datetimepicker-months">' +
	    '<table class="table-condensed">' +
	    DPGlobal.headTemplateV3 +
	    DPGlobal.contTemplate +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '<div class="datetimepicker-years">' +
	    '<table class="table-condensed">' +
	    DPGlobal.headTemplateV3 +
	    DPGlobal.contTemplate +
	    DPGlobal.footTemplate +
	    '</table>' +
	    '</div>' +
	    '</div>';
	  $.fn.datetimepicker.DPGlobal = DPGlobal;
	
	  /* DATETIMEPICKER NO CONFLICT
	   * =================== */
	
	  $.fn.datetimepicker.noConflict = function () {
	    $.fn.datetimepicker = old;
	    return this;
	  };
	
	  /* DATETIMEPICKER DATA-API
	   * ================== */
	
	  $(document).on(
	    'focus.datetimepicker.data-api click.datetimepicker.data-api',
	    '[data-provide="datetimepicker"]',
	    function (e) {
	      var $this = $(this);
	      if ($this.data('datetimepicker')) return;
	      e.preventDefault();
	      // component click requires us to explicitly show it
	      $this.datetimepicker('show');
	    }
	  );
	  $(function () {
	    $('[data-provide="datetimepicker-inline"]').datetimepicker();
	  });
	
	}));
	
	}.call(window));

/***/ }),

/***/ "bdf3ee7433c7d244da7e":
/***/ (function(module, exports, __webpack_require__) {

	/*
		MIT License http://www.opensource.org/licenses/mit-license.php
		Author Tobias Koppers @sokra
	*/
	var stylesInDom = {},
		memoize = function(fn) {
			var memo;
			return function () {
				if (typeof memo === "undefined") memo = fn.apply(this, arguments);
				return memo;
			};
		},
		isOldIE = memoize(function() {
			return /msie [6-9]\b/.test(window.navigator.userAgent.toLowerCase());
		}),
		getHeadElement = memoize(function () {
			return document.head || document.getElementsByTagName("head")[0];
		}),
		singletonElement = null,
		singletonCounter = 0,
		styleElementsInsertedAtTop = [];
	
	module.exports = function(list, options) {
		if(false) {
			if(typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
		}
	
		options = options || {};
		// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
		// tags it will allow on a page
		if (typeof options.singleton === "undefined") options.singleton = isOldIE();
	
		// By default, add <style> tags to the bottom of <head>.
		if (typeof options.insertAt === "undefined") options.insertAt = "bottom";
	
		var styles = listToStyles(list);
		addStylesToDom(styles, options);
	
		return function update(newList) {
			var mayRemove = [];
			for(var i = 0; i < styles.length; i++) {
				var item = styles[i];
				var domStyle = stylesInDom[item.id];
				domStyle.refs--;
				mayRemove.push(domStyle);
			}
			if(newList) {
				var newStyles = listToStyles(newList);
				addStylesToDom(newStyles, options);
			}
			for(var i = 0; i < mayRemove.length; i++) {
				var domStyle = mayRemove[i];
				if(domStyle.refs === 0) {
					for(var j = 0; j < domStyle.parts.length; j++)
						domStyle.parts[j]();
					delete stylesInDom[domStyle.id];
				}
			}
		};
	}
	
	function addStylesToDom(styles, options) {
		for(var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];
			if(domStyle) {
				domStyle.refs++;
				for(var j = 0; j < domStyle.parts.length; j++) {
					domStyle.parts[j](item.parts[j]);
				}
				for(; j < item.parts.length; j++) {
					domStyle.parts.push(addStyle(item.parts[j], options));
				}
			} else {
				var parts = [];
				for(var j = 0; j < item.parts.length; j++) {
					parts.push(addStyle(item.parts[j], options));
				}
				stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
			}
		}
	}
	
	function listToStyles(list) {
		var styles = [];
		var newStyles = {};
		for(var i = 0; i < list.length; i++) {
			var item = list[i];
			var id = item[0];
			var css = item[1];
			var media = item[2];
			var sourceMap = item[3];
			var part = {css: css, media: media, sourceMap: sourceMap};
			if(!newStyles[id])
				styles.push(newStyles[id] = {id: id, parts: [part]});
			else
				newStyles[id].parts.push(part);
		}
		return styles;
	}
	
	function insertStyleElement(options, styleElement) {
		var head = getHeadElement();
		var lastStyleElementInsertedAtTop = styleElementsInsertedAtTop[styleElementsInsertedAtTop.length - 1];
		if (options.insertAt === "top") {
			if(!lastStyleElementInsertedAtTop) {
				head.insertBefore(styleElement, head.firstChild);
			} else if(lastStyleElementInsertedAtTop.nextSibling) {
				head.insertBefore(styleElement, lastStyleElementInsertedAtTop.nextSibling);
			} else {
				head.appendChild(styleElement);
			}
			styleElementsInsertedAtTop.push(styleElement);
		} else if (options.insertAt === "bottom") {
			head.appendChild(styleElement);
		} else {
			throw new Error("Invalid value for parameter 'insertAt'. Must be 'top' or 'bottom'.");
		}
	}
	
	function removeStyleElement(styleElement) {
		styleElement.parentNode.removeChild(styleElement);
		var idx = styleElementsInsertedAtTop.indexOf(styleElement);
		if(idx >= 0) {
			styleElementsInsertedAtTop.splice(idx, 1);
		}
	}
	
	function createStyleElement(options) {
		var styleElement = document.createElement("style");
		styleElement.type = "text/css";
		insertStyleElement(options, styleElement);
		return styleElement;
	}
	
	function createLinkElement(options) {
		var linkElement = document.createElement("link");
		linkElement.rel = "stylesheet";
		insertStyleElement(options, linkElement);
		return linkElement;
	}
	
	function addStyle(obj, options) {
		var styleElement, update, remove;
	
		if (options.singleton) {
			var styleIndex = singletonCounter++;
			styleElement = singletonElement || (singletonElement = createStyleElement(options));
			update = applyToSingletonTag.bind(null, styleElement, styleIndex, false);
			remove = applyToSingletonTag.bind(null, styleElement, styleIndex, true);
		} else if(obj.sourceMap &&
			typeof URL === "function" &&
			typeof URL.createObjectURL === "function" &&
			typeof URL.revokeObjectURL === "function" &&
			typeof Blob === "function" &&
			typeof btoa === "function") {
			styleElement = createLinkElement(options);
			update = updateLink.bind(null, styleElement);
			remove = function() {
				removeStyleElement(styleElement);
				if(styleElement.href)
					URL.revokeObjectURL(styleElement.href);
			};
		} else {
			styleElement = createStyleElement(options);
			update = applyToTag.bind(null, styleElement);
			remove = function() {
				removeStyleElement(styleElement);
			};
		}
	
		update(obj);
	
		return function updateStyle(newObj) {
			if(newObj) {
				if(newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap)
					return;
				update(obj = newObj);
			} else {
				remove();
			}
		};
	}
	
	var replaceText = (function () {
		var textStore = [];
	
		return function (index, replacement) {
			textStore[index] = replacement;
			return textStore.filter(Boolean).join('\n');
		};
	})();
	
	function applyToSingletonTag(styleElement, index, remove, obj) {
		var css = remove ? "" : obj.css;
	
		if (styleElement.styleSheet) {
			styleElement.styleSheet.cssText = replaceText(index, css);
		} else {
			var cssNode = document.createTextNode(css);
			var childNodes = styleElement.childNodes;
			if (childNodes[index]) styleElement.removeChild(childNodes[index]);
			if (childNodes.length) {
				styleElement.insertBefore(cssNode, childNodes[index]);
			} else {
				styleElement.appendChild(cssNode);
			}
		}
	}
	
	function applyToTag(styleElement, obj) {
		var css = obj.css;
		var media = obj.media;
	
		if(media) {
			styleElement.setAttribute("media", media)
		}
	
		if(styleElement.styleSheet) {
			styleElement.styleSheet.cssText = css;
		} else {
			while(styleElement.firstChild) {
				styleElement.removeChild(styleElement.firstChild);
			}
			styleElement.appendChild(document.createTextNode(css));
		}
	}
	
	function updateLink(linkElement, obj) {
		var css = obj.css;
		var sourceMap = obj.sourceMap;
	
		if(sourceMap) {
			// http://stackoverflow.com/a/26603875
			css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
		}
	
		var blob = new Blob([css], { type: "text/css" });
	
		var oldSrc = linkElement.href;
	
		linkElement.href = URL.createObjectURL(blob);
	
		if(oldSrc)
			URL.revokeObjectURL(oldSrc);
	}


/***/ }),

/***/ "fbcdef65edfe3a266191":
/***/ (function(module, exports, __webpack_require__) {

	// style-loader: Adds some css to the DOM by adding a <style> tag
	
	// load the styles
	var content = __webpack_require__("fbcdef65edfe3a26619d");
	if(typeof content === 'string') content = [[module.id, content, '']];
	// add the styles to the DOM
	var update = __webpack_require__("bdf3ee7433c7d244da7e")(content, {"insertAt":"top"});
	if(content.locals) module.exports = content.locals;
	// Hot Module Replacement
	if(false) {
		// When the styles change, update the <style> tags
		if(!content.locals) {
			module.hot.accept("!!../../css-loader/index.js!./bootstrap-datetimepicker.css", function() {
				var newContent = require("!!../../css-loader/index.js!./bootstrap-datetimepicker.css");
				if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
				update(newContent);
			});
		}
		// When the module is disposed, remove the <style> tags
		module.hot.dispose(function() { update(); });
	}

/***/ }),

/***/ "a9374df36b3d91e7ad11":
/***/ (function(module, exports, __webpack_require__) {

	// style-loader: Adds some css to the DOM by adding a <style> tag
	
	// load the styles
	var content = __webpack_require__("a9374df36b3d91e7ad15");
	if(typeof content === 'string') content = [[module.id, content, '']];
	// add the styles to the DOM
	var update = __webpack_require__("bdf3ee7433c7d244da7e")(content, {"insertAt":"top"});
	if(content.locals) module.exports = content.locals;
	// Hot Module Replacement
	if(false) {
		// When the styles change, update the <style> tags
		if(!content.locals) {
			module.hot.accept("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/less-loader/index.js!./index.less", function() {
				var newContent = require("!!../../../../../node_modules/css-loader/index.js!../../../../../node_modules/less-loader/index.js!./index.less");
				if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
				update(newContent);
			});
		}
		// When the module is disposed, remove the <style> tags
		module.hot.dispose(function() { update(); });
	}

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	module.exports = __webpack_require__("703aa93cb1c4be3a3ba9");


/***/ }),

/***/ 1:
/***/ (function(module, exports) {

	module.exports = jQuery;

/***/ })

/******/ });
//# sourceMappingURL=bootstrap-datetimepicker.js.map