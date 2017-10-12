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

/***/ "9d6592e537e3e6d0281c":
/***/ (function(module, exports, __webpack_require__) {

	exports = module.exports = __webpack_require__("e7f1add7f34e416618de")();
	// imports
	
	
	// module
	exports.push([module.id, ".ps-container .ps-scrollbar-x-rail{position:absolute;bottom:3px;height:8px;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;opacity:0;filter:alpha(opacity=0);-o-transition:background-color .2s linear,opacity .2s linear;-webkit-transition:background-color.2s linear,opacity .2s linear;-moz-transition:background-color .2s linear,opacity .2s linear;transition:background-color .2s linear,opacity .2s linear}.ps-container:hover .ps-scrollbar-x-rail,.ps-container.hover .ps-scrollbar-x-rail{opacity:.6;filter:alpha(opacity=60)}.ps-container .ps-scrollbar-x-rail:hover,.ps-container .ps-scrollbar-x-rail.hover{background-color:#eee;opacity:.9;filter:alpha(opacity=90)}.ps-container .ps-scrollbar-x-rail.in-scrolling{opacity:.9;filter:alpha(opacity=90)}.ps-container .ps-scrollbar-y-rail{position:absolute;right:3px;width:8px;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;opacity:0;filter:alpha(opacity=0);-o-transition:background-color .2s linear,opacity .2s linear;-webkit-transition:background-color.2s linear,opacity .2s linear;-moz-transition:background-color .2s linear,opacity .2s linear;transition:background-color .2s linear,opacity .2s linear}.ps-container:hover .ps-scrollbar-y-rail,.ps-container.hover .ps-scrollbar-y-rail{opacity:.6;filter:alpha(opacity=60)}.ps-container .ps-scrollbar-y-rail:hover,.ps-container .ps-scrollbar-y-rail.hover{background-color:#eee;opacity:.9;filter:alpha(opacity=90)}.ps-container .ps-scrollbar-y-rail.in-scrolling{opacity:.9;filter:alpha(opacity=90)}.ps-container .ps-scrollbar-x{position:absolute;bottom:0;height:8px;background-color:#aaa;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;-o-transition:background-color .2s linear;-webkit-transition:background-color.2s linear;-moz-transition:background-color .2s linear;transition:background-color .2s linear}.ps-container.ie6 .ps-scrollbar-x{font-size:0}.ps-container .ps-scrollbar-x-rail:hover .ps-scrollbar-x,.ps-container .ps-scrollbar-x-rail.hover .ps-scrollbar-x{background-color:#999}.ps-container .ps-scrollbar-y{position:absolute;right:0;width:8px;background-color:#aaa;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;-o-transition:background-color .2s linear;-webkit-transition:background-color.2s linear;-moz-transition:background-color .2s linear;transition:background-color .2s linear}.ps-container.ie6 .ps-scrollbar-y{font-size:0}.ps-container .ps-scrollbar-y-rail:hover .ps-scrollbar-y,.ps-container .ps-scrollbar-y-rail.hover .ps-scrollbar-y{background-color:#999}.ps-container.ie .ps-scrollbar-x,.ps-container.ie .ps-scrollbar-y{visibility:hidden}.ps-container.ie:hover .ps-scrollbar-x,.ps-container.ie:hover .ps-scrollbar-y,.ps-container.ie.hover .ps-scrollbar-x,.ps-container.ie.hover .ps-scrollbar-y{visibility:visible}\n", ""]);
	
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

/***/ "977c745a5284d801809d":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	__webpack_require__("c625c29b098778814f10");
	
	__webpack_require__("9af7e9efae9b83cbe932");
	
	__webpack_require__("9d6592e537e3e6d02811");

/***/ }),

/***/ "c625c29b098778814f10":
/***/ (function(module, exports, __webpack_require__) {

	"use strict";
	
	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };
	
	/*! Copyright (c) 2013 Brandon Aaron (http://brandon.aaron.sh)
	  * Licensed under the MIT License (LICENSE.txt).
	  *
	  * Version: 3.1.9
	  *
	  * Requires: jQuery 1.2.2+
	  */
	(function (factory) {
	    if (true) {
	        // AMD. Register as an anonymous module.
	        factory(jQuery);
	    } else if ((typeof exports === "undefined" ? "undefined" : _typeof(exports)) === "object") {
	        // Node/CommonJS style for Browserify
	        module.exports = factory;
	    } else {
	        // Browser globals
	        factory(jQuery);
	    }
	})(function ($) {
	    var toFix = ["wheel", "mousewheel", "DOMMouseScroll", "MozMousePixelScroll"],
	        toBind = "onwheel" in document || document.documentMode >= 9 ? ["wheel"] : ["mousewheel", "DomMouseScroll", "MozMousePixelScroll"],
	        slice = Array.prototype.slice,
	        nullLowestDeltaTimeout,
	        lowestDelta;
	    if ($.event.fixHooks) {
	        for (var i = toFix.length; i;) {
	            $.event.fixHooks[toFix[--i]] = $.event.mouseHooks;
	        }
	    }
	    var special = $.event.special.mousewheel = {
	        version: "3.1.9",
	        setup: function setup() {
	            if (this.addEventListener) {
	                for (var i = toBind.length; i;) {
	                    this.addEventListener(toBind[--i], handler, false);
	                }
	            } else {
	                this.onmousewheel = handler;
	            }
	            // Store the line height and page height for this particular element
	            $.data(this, "mousewheel-line-height", special.getLineHeight(this));
	            $.data(this, "mousewheel-page-height", special.getPageHeight(this));
	        },
	        teardown: function teardown() {
	            if (this.removeEventListener) {
	                for (var i = toBind.length; i;) {
	                    this.removeEventListener(toBind[--i], handler, false);
	                }
	            } else {
	                this.onmousewheel = null;
	            }
	        },
	        getLineHeight: function getLineHeight(elem) {
	            return parseInt($(elem)["offsetParent" in $.fn ? "offsetParent" : "parent"]().css("fontSize"), 10);
	        },
	        getPageHeight: function getPageHeight(elem) {
	            return $(elem).height();
	        },
	        settings: {
	            adjustOldDeltas: true
	        }
	    };
	    $.fn.extend({
	        mousewheel: function mousewheel(fn) {
	            return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
	        },
	        unmousewheel: function unmousewheel(fn) {
	            return this.unbind("mousewheel", fn);
	        }
	    });
	    function handler(event) {
	        var orgEvent = event || window.event,
	            args = slice.call(arguments, 1),
	            delta = 0,
	            deltaX = 0,
	            deltaY = 0,
	            absDelta = 0;
	        event = $.event.fix(orgEvent);
	        event.type = "mousewheel";
	        // Old school scrollwheel delta
	        if ("detail" in orgEvent) {
	            deltaY = orgEvent.detail * -1;
	        }
	        if ("wheelDelta" in orgEvent) {
	            deltaY = orgEvent.wheelDelta;
	        }
	        if ("wheelDeltaY" in orgEvent) {
	            deltaY = orgEvent.wheelDeltaY;
	        }
	        if ("wheelDeltaX" in orgEvent) {
	            deltaX = orgEvent.wheelDeltaX * -1;
	        }
	        // Firefox < 17 horizontal scrolling related to DOMMouseScroll event
	        if ("axis" in orgEvent && orgEvent.axis === orgEvent.HORIZONTAL_AXIS) {
	            deltaX = deltaY * -1;
	            deltaY = 0;
	        }
	        // Set delta to be deltaY or deltaX if deltaY is 0 for backwards compatabilitiy
	        delta = deltaY === 0 ? deltaX : deltaY;
	        // New school wheel delta (wheel event)
	        if ("deltaY" in orgEvent) {
	            deltaY = orgEvent.deltaY * -1;
	            delta = deltaY;
	        }
	        if ("deltaX" in orgEvent) {
	            deltaX = orgEvent.deltaX;
	            if (deltaY === 0) {
	                delta = deltaX * -1;
	            }
	        }
	        // No change actually happened, no reason to go any further
	        if (deltaY === 0 && deltaX === 0) {
	            return;
	        }
	        // Need to convert lines and pages to pixels if we aren't already in pixels
	        // There are three delta modes:
	        //   * deltaMode 0 is by pixels, nothing to do
	        //   * deltaMode 1 is by lines
	        //   * deltaMode 2 is by pages
	        if (orgEvent.deltaMode === 1) {
	            var lineHeight = $.data(this, "mousewheel-line-height");
	            delta *= lineHeight;
	            deltaY *= lineHeight;
	            deltaX *= lineHeight;
	        } else if (orgEvent.deltaMode === 2) {
	            var pageHeight = $.data(this, "mousewheel-page-height");
	            delta *= pageHeight;
	            deltaY *= pageHeight;
	            deltaX *= pageHeight;
	        }
	        // Store lowest absolute delta to normalize the delta values
	        absDelta = Math.max(Math.abs(deltaY), Math.abs(deltaX));
	        if (!lowestDelta || absDelta < lowestDelta) {
	            lowestDelta = absDelta;
	            // Adjust older deltas if necessary
	            if (shouldAdjustOldDeltas(orgEvent, absDelta)) {
	                lowestDelta /= 40;
	            }
	        }
	        // Adjust older deltas if necessary
	        if (shouldAdjustOldDeltas(orgEvent, absDelta)) {
	            // Divide all the things by 40!
	            delta /= 40;
	            deltaX /= 40;
	            deltaY /= 40;
	        }
	        // Get a whole, normalized value for the deltas
	        delta = Math[delta >= 1 ? "floor" : "ceil"](delta / lowestDelta);
	        deltaX = Math[deltaX >= 1 ? "floor" : "ceil"](deltaX / lowestDelta);
	        deltaY = Math[deltaY >= 1 ? "floor" : "ceil"](deltaY / lowestDelta);
	        // Add information to the event object
	        event.deltaX = deltaX;
	        event.deltaY = deltaY;
	        event.deltaFactor = lowestDelta;
	        // Go ahead and set deltaMode to 0 since we converted to pixels
	        // Although this is a little odd since we overwrite the deltaX/Y
	        // properties with normalized deltas.
	        event.deltaMode = 0;
	        // Add event and delta to the front of the arguments
	        args.unshift(event, delta, deltaX, deltaY);
	        // Clearout lowestDelta after sometime to better
	        // handle multiple device types that give different
	        // a different lowestDelta
	        // Ex: trackpad = 3 and mouse wheel = 120
	        if (nullLowestDeltaTimeout) {
	            clearTimeout(nullLowestDeltaTimeout);
	        }
	        nullLowestDeltaTimeout = setTimeout(nullLowestDelta, 200);
	        return ($.event.dispatch || $.event.handle).apply(this, args);
	    }
	    function nullLowestDelta() {
	        lowestDelta = null;
	    }
	    function shouldAdjustOldDeltas(orgEvent, absDelta) {
	        // If this is an older event and the delta is divisable by 120,
	        // then we are assuming that the browser is treating this as an
	        // older mouse wheel event and that we should divide the deltas
	        // by 40 to try and get a more usable deltaFactor.
	        // Side note, this actually impacts the reported scroll distance
	        // in older browsers and can cause scrolling to be slower than native.
	        // Turn this off by setting $.event.special.mousewheel.settings.adjustOldDeltas to false.
	        return special.settings.adjustOldDeltas && orgEvent.type === "mousewheel" && absDelta % 120 === 0;
	    }
	});

/***/ }),

/***/ "9af7e9efae9b83cbe932":
/***/ (function(module, exports) {

	"use strict";
	
	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };
	
	(function (factory) {
	    factory($);
	})(function ($) {
	    // The default settings for the plugin
	    var defaultSettings = {
	        wheelSpeed: 50,
	        wheelPropagation: false,
	        minScrollbarLength: null,
	        useBothWheelAxes: false,
	        useKeyboard: true,
	        suppressScrollX: false,
	        suppressScrollY: false,
	        scrollXMarginOffset: 0,
	        scrollYMarginOffset: 0
	    };
	    var getEventClassName = function () {
	        var incrementingId = 0;
	        return function () {
	            var id = incrementingId;
	            incrementingId += 1;
	            return ".perfect-scrollbar-" + id;
	        };
	    }();
	    $.fn.perfectScrollbar = function (suppliedSettings, option) {
	        return this.each(function () {
	            // Use the default settings
	            var settings = $.extend(true, {}, defaultSettings),
	                $this = $(this);
	            if ((typeof suppliedSettings === "undefined" ? "undefined" : _typeof(suppliedSettings)) === "object") {
	                // But over-ride any supplied
	                $.extend(true, settings, suppliedSettings);
	            } else {
	                // If no settings were supplied, then the first param must be the option
	                option = suppliedSettings;
	            }
	            // Catch options
	            if (option === "update") {
	                if ($this.data("perfect-scrollbar-update")) {
	                    $this.data("perfect-scrollbar-update")();
	                }
	                return $this;
	            } else if (option === "destroy") {
	                if ($this.data("perfect-scrollbar-destroy")) {
	                    $this.data("perfect-scrollbar-destroy")();
	                }
	                return $this;
	            }
	            if ($this.data("perfect-scrollbar")) {
	                // if there's already perfect-scrollbar
	                return $this.data("perfect-scrollbar");
	            }
	            // Or generate new perfectScrollbar
	            // Set class to the container
	            $this.addClass("ps-container");
	            var $scrollbarXRail = $("<div class='ps-scrollbar-x-rail'></div>").appendTo($this),
	                $scrollbarYRail = $("<div class='ps-scrollbar-y-rail'></div>").appendTo($this),
	                $scrollbarX = $("<div class='ps-scrollbar-x'></div>").appendTo($scrollbarXRail),
	                $scrollbarY = $("<div class='ps-scrollbar-y'></div>").appendTo($scrollbarYRail),
	                scrollbarXActive,
	                scrollbarYActive,
	                containerWidth,
	                containerHeight,
	                contentWidth,
	                contentHeight,
	                scrollbarXWidth,
	                scrollbarXLeft,
	                scrollbarXBottom = parseInt($scrollbarXRail.css("bottom"), 10),
	                scrollbarYHeight,
	                scrollbarYTop,
	                scrollbarYRight = parseInt($scrollbarYRail.css("right"), 10),
	                eventClassName = getEventClassName();
	            var updateContentScrollTop = function updateContentScrollTop(currentTop, deltaY) {
	                var newTop = currentTop + deltaY,
	                    maxTop = containerHeight - scrollbarYHeight;
	                if (newTop < 0) {
	                    scrollbarYTop = 0;
	                } else if (newTop > maxTop) {
	                    scrollbarYTop = maxTop;
	                } else {
	                    scrollbarYTop = newTop;
	                }
	                var scrollTop = parseInt(scrollbarYTop * (contentHeight - containerHeight) / (containerHeight - scrollbarYHeight), 10);
	                $this.scrollTop(scrollTop);
	                $scrollbarXRail.css({
	                    bottom: scrollbarXBottom - scrollTop
	                });
	            };
	            var updateContentScrollLeft = function updateContentScrollLeft(currentLeft, deltaX) {
	                var newLeft = currentLeft + deltaX,
	                    maxLeft = containerWidth - scrollbarXWidth;
	                if (newLeft < 0) {
	                    scrollbarXLeft = 0;
	                } else if (newLeft > maxLeft) {
	                    scrollbarXLeft = maxLeft;
	                } else {
	                    scrollbarXLeft = newLeft;
	                }
	                var scrollLeft = parseInt(scrollbarXLeft * (contentWidth - containerWidth) / (containerWidth - scrollbarXWidth), 10);
	                $this.scrollLeft(scrollLeft);
	                $scrollbarYRail.css({
	                    right: scrollbarYRight - scrollLeft
	                });
	            };
	            var getSettingsAdjustedThumbSize = function getSettingsAdjustedThumbSize(thumbSize) {
	                if (settings.minScrollbarLength) {
	                    thumbSize = Math.max(thumbSize, settings.minScrollbarLength);
	                }
	                return thumbSize;
	            };
	            var updateScrollbarCss = function updateScrollbarCss() {
	                $scrollbarXRail.css({
	                    left: $this.scrollLeft(),
	                    bottom: scrollbarXBottom - $this.scrollTop(),
	                    width: containerWidth,
	                    display: scrollbarXActive ? "inherit" : "none"
	                });
	                $scrollbarYRail.css({
	                    top: $this.scrollTop(),
	                    right: scrollbarYRight - $this.scrollLeft(),
	                    height: containerHeight,
	                    display: scrollbarYActive ? "inherit" : "none"
	                });
	                $scrollbarX.css({
	                    left: scrollbarXLeft,
	                    width: scrollbarXWidth
	                });
	                $scrollbarY.css({
	                    top: scrollbarYTop,
	                    height: scrollbarYHeight
	                });
	            };
	            var updateBarSizeAndPosition = function updateBarSizeAndPosition() {
	                containerWidth = $this.width();
	                containerHeight = $this.height();
	                contentWidth = $this.prop("scrollWidth");
	                contentHeight = $this.prop("scrollHeight");
	                if (!settings.suppressScrollX && containerWidth + settings.scrollXMarginOffset < contentWidth) {
	                    scrollbarXActive = true;
	                    scrollbarXWidth = getSettingsAdjustedThumbSize(parseInt(containerWidth * containerWidth / contentWidth, 10));
	                    scrollbarXLeft = parseInt($this.scrollLeft() * (containerWidth - scrollbarXWidth) / (contentWidth - containerWidth), 10);
	                } else {
	                    scrollbarXActive = false;
	                    scrollbarXWidth = 0;
	                    scrollbarXLeft = 0;
	                    $this.scrollLeft(0);
	                }
	                if (!settings.suppressScrollY && containerHeight + settings.scrollYMarginOffset < contentHeight) {
	                    scrollbarYActive = true;
	                    scrollbarYHeight = getSettingsAdjustedThumbSize(parseInt(containerHeight * containerHeight / contentHeight, 10));
	                    scrollbarYTop = parseInt($this.scrollTop() * (containerHeight - scrollbarYHeight) / (contentHeight - containerHeight), 10);
	                } else {
	                    scrollbarYActive = false;
	                    scrollbarYHeight = 0;
	                    scrollbarYTop = 0;
	                    $this.scrollTop(0);
	                }
	                if (scrollbarYTop >= containerHeight - scrollbarYHeight) {
	                    scrollbarYTop = containerHeight - scrollbarYHeight;
	                }
	                if (scrollbarXLeft >= containerWidth - scrollbarXWidth) {
	                    scrollbarXLeft = containerWidth - scrollbarXWidth;
	                }
	                updateScrollbarCss();
	            };
	            var bindMouseScrollXHandler = function bindMouseScrollXHandler() {
	                var currentLeft, currentPageX;
	                $scrollbarX.bind("mousedown" + eventClassName, function (e) {
	                    currentPageX = e.pageX;
	                    currentLeft = $scrollbarX.position().left;
	                    $scrollbarXRail.addClass("in-scrolling");
	                    e.stopPropagation();
	                    e.preventDefault();
	                });
	                $(document).bind("mousemove" + eventClassName, function (e) {
	                    if ($scrollbarXRail.hasClass("in-scrolling")) {
	                        updateContentScrollLeft(currentLeft, e.pageX - currentPageX);
	                        e.stopPropagation();
	                        e.preventDefault();
	                    }
	                });
	                $(document).bind("mouseup" + eventClassName, function (e) {
	                    if ($scrollbarXRail.hasClass("in-scrolling")) {
	                        $scrollbarXRail.removeClass("in-scrolling");
	                    }
	                });
	                currentLeft = currentPageX = null;
	            };
	            var bindMouseScrollYHandler = function bindMouseScrollYHandler() {
	                var currentTop, currentPageY;
	                $scrollbarY.bind("mousedown" + eventClassName, function (e) {
	                    currentPageY = e.pageY;
	                    currentTop = $scrollbarY.position().top;
	                    $scrollbarYRail.addClass("in-scrolling");
	                    e.stopPropagation();
	                    e.preventDefault();
	                });
	                $(document).bind("mousemove" + eventClassName, function (e) {
	                    if ($scrollbarYRail.hasClass("in-scrolling")) {
	                        updateContentScrollTop(currentTop, e.pageY - currentPageY);
	                        e.stopPropagation();
	                        e.preventDefault();
	                    }
	                });
	                $(document).bind("mouseup" + eventClassName, function (e) {
	                    if ($scrollbarYRail.hasClass("in-scrolling")) {
	                        $scrollbarYRail.removeClass("in-scrolling");
	                    }
	                });
	                currentTop = currentPageY = null;
	            };
	            // check if the default scrolling should be prevented.
	            var shouldPreventDefault = function shouldPreventDefault(deltaX, deltaY) {
	                var scrollTop = $this.scrollTop();
	                if (deltaX === 0) {
	                    if (!scrollbarYActive) {
	                        return false;
	                    }
	                    if (scrollTop === 0 && deltaY > 0 || scrollTop >= contentHeight - containerHeight && deltaY < 0) {
	                        return !settings.wheelPropagation;
	                    }
	                }
	                var scrollLeft = $this.scrollLeft();
	                if (deltaY === 0) {
	                    if (!scrollbarXActive) {
	                        return false;
	                    }
	                    if (scrollLeft === 0 && deltaX < 0 || scrollLeft >= contentWidth - containerWidth && deltaX > 0) {
	                        return !settings.wheelPropagation;
	                    }
	                }
	                return true;
	            };
	            // bind handlers
	            var bindMouseWheelHandler = function bindMouseWheelHandler() {
	                var shouldPrevent = false;
	                $this.bind("mousewheel" + eventClassName, function (e, delta, deltaX, deltaY) {
	                    if (!settings.useBothWheelAxes) {
	                        // deltaX will only be used for horizontal scrolling and deltaY will
	                        // only be used for vertical scrolling - this is the default
	                        $this.scrollTop($this.scrollTop() - deltaY * settings.wheelSpeed);
	                        $this.scrollLeft($this.scrollLeft() + deltaX * settings.wheelSpeed);
	                    } else if (scrollbarYActive && !scrollbarXActive) {
	                        // only vertical scrollbar is active and useBothWheelAxes option is
	                        // active, so let's scroll vertical bar using both mouse wheel axes
	                        if (deltaY) {
	                            $this.scrollTop($this.scrollTop() - deltaY * settings.wheelSpeed);
	                        } else {
	                            $this.scrollTop($this.scrollTop() + deltaX * settings.wheelSpeed);
	                        }
	                    } else if (scrollbarXActive && !scrollbarYActive) {
	                        // useBothWheelAxes and only horizontal bar is active, so use both
	                        // wheel axes for horizontal bar
	                        if (deltaX) {
	                            $this.scrollLeft($this.scrollLeft() + deltaX * settings.wheelSpeed);
	                        } else {
	                            $this.scrollLeft($this.scrollLeft() - deltaY * settings.wheelSpeed);
	                        }
	                    }
	                    // update bar position
	                    updateBarSizeAndPosition();
	                    shouldPrevent = shouldPreventDefault(deltaX, deltaY);
	                    if (shouldPrevent) {
	                        e.preventDefault();
	                    }
	                });
	                // fix Firefox scroll problem
	                $this.bind("MozMousePixelScroll" + eventClassName, function (e) {
	                    if (shouldPrevent) {
	                        e.preventDefault();
	                    }
	                });
	            };
	            var bindKeyboardHandler = function bindKeyboardHandler() {
	                var hovered = false;
	                $this.bind("mouseenter" + eventClassName, function (e) {
	                    hovered = true;
	                });
	                $this.bind("mouseleave" + eventClassName, function (e) {
	                    hovered = false;
	                });
	                var shouldPrevent = false;
	                $(document).bind("keydown" + eventClassName, function (e) {
	                    if (!hovered) {
	                        return;
	                    }
	                    var deltaX = 0,
	                        deltaY = 0;
	                    switch (e.which) {
	                        case 37:
	                            // left
	                            deltaX = -3;
	                            break;
	
	                        case 38:
	                            // up
	                            deltaY = 3;
	                            break;
	
	                        case 39:
	                            // right
	                            deltaX = 3;
	                            break;
	
	                        case 40:
	                            // down
	                            deltaY = -3;
	                            break;
	
	                        case 33:
	                            // page up
	                            deltaY = 9;
	                            break;
	
	                        case 32:
	                        // space bar
	                        /* falls through */
	                        case 34:
	                            // page down
	                            deltaY = -9;
	                            break;
	
	                        case 35:
	                            // end
	                            deltaY = -containerHeight;
	                            break;
	
	                        case 36:
	                            // home
	                            deltaY = containerHeight;
	                            break;
	
	                        default:
	                            return;
	                    }
	                    $this.scrollTop($this.scrollTop() - deltaY * settings.wheelSpeed);
	                    $this.scrollLeft($this.scrollLeft() + deltaX * settings.wheelSpeed);
	                    shouldPrevent = shouldPreventDefault(deltaX, deltaY);
	                    if (shouldPrevent) {
	                        e.preventDefault();
	                    }
	                });
	            };
	            var bindRailClickHandler = function bindRailClickHandler() {
	                var stopPropagation = function stopPropagation(e) {
	                    e.stopPropagation();
	                };
	                $scrollbarY.bind("click" + eventClassName, stopPropagation);
	                $scrollbarYRail.bind("click" + eventClassName, function (e) {
	                    var halfOfScrollbarLength = parseInt(scrollbarYHeight / 2, 10),
	                        positionTop = e.pageY - $scrollbarYRail.offset().top - halfOfScrollbarLength,
	                        maxPositionTop = containerHeight - scrollbarYHeight,
	                        positionRatio = positionTop / maxPositionTop;
	                    if (positionRatio < 0) {
	                        positionRatio = 0;
	                    } else if (positionRatio > 1) {
	                        positionRatio = 1;
	                    }
	                    $this.scrollTop((contentHeight - containerHeight) * positionRatio);
	                });
	                $scrollbarX.bind("click" + eventClassName, stopPropagation);
	                $scrollbarXRail.bind("click" + eventClassName, function (e) {
	                    var halfOfScrollbarLength = parseInt(scrollbarXWidth / 2, 10),
	                        positionLeft = e.pageX - $scrollbarXRail.offset().left - halfOfScrollbarLength,
	                        maxPositionLeft = containerWidth - scrollbarXWidth,
	                        positionRatio = positionLeft / maxPositionLeft;
	                    if (positionRatio < 0) {
	                        positionRatio = 0;
	                    } else if (positionRatio > 1) {
	                        positionRatio = 1;
	                    }
	                    $this.scrollLeft((contentWidth - containerWidth) * positionRatio);
	                });
	            };
	            // bind mobile touch handler
	            var bindMobileTouchHandler = function bindMobileTouchHandler() {
	                var applyTouchMove = function applyTouchMove(differenceX, differenceY) {
	                    $this.scrollTop($this.scrollTop() - differenceY);
	                    $this.scrollLeft($this.scrollLeft() - differenceX);
	                    // update bar position
	                    updateBarSizeAndPosition();
	                };
	                var startCoords = {},
	                    startTime = 0,
	                    speed = {},
	                    breakingProcess = null,
	                    inGlobalTouch = false;
	                $(window).bind("touchstart" + eventClassName, function (e) {
	                    inGlobalTouch = true;
	                });
	                $(window).bind("touchend" + eventClassName, function (e) {
	                    inGlobalTouch = false;
	                });
	                $this.bind("touchstart" + eventClassName, function (e) {
	                    var touch = e.originalEvent.targetTouches[0];
	                    startCoords.pageX = touch.pageX;
	                    startCoords.pageY = touch.pageY;
	                    startTime = new Date().getTime();
	                    if (breakingProcess !== null) {
	                        clearInterval(breakingProcess);
	                    }
	                    e.stopPropagation();
	                });
	                $this.bind("touchmove" + eventClassName, function (e) {
	                    if (!inGlobalTouch && e.originalEvent.targetTouches.length === 1) {
	                        var touch = e.originalEvent.targetTouches[0];
	                        var currentCoords = {};
	                        currentCoords.pageX = touch.pageX;
	                        currentCoords.pageY = touch.pageY;
	                        var differenceX = currentCoords.pageX - startCoords.pageX,
	                            differenceY = currentCoords.pageY - startCoords.pageY;
	                        applyTouchMove(differenceX, differenceY);
	                        startCoords = currentCoords;
	                        var currentTime = new Date().getTime();
	                        speed.x = differenceX / (currentTime - startTime);
	                        speed.y = differenceY / (currentTime - startTime);
	                        startTime = currentTime;
	                        e.preventDefault();
	                    }
	                });
	                $this.bind("touchend" + eventClassName, function (e) {
	                    clearInterval(breakingProcess);
	                    breakingProcess = setInterval(function () {
	                        if (Math.abs(speed.x) < .01 && Math.abs(speed.y) < .01) {
	                            clearInterval(breakingProcess);
	                            return;
	                        }
	                        applyTouchMove(speed.x * 30, speed.y * 30);
	                        speed.x *= .8;
	                        speed.y *= .8;
	                    }, 10);
	                });
	            };
	            var bindScrollHandler = function bindScrollHandler() {
	                $this.bind("scroll" + eventClassName, function (e) {
	                    updateBarSizeAndPosition();
	                });
	            };
	            var destroy = function destroy() {
	                $this.unbind(eventClassName);
	                $(window).unbind(eventClassName);
	                $(document).unbind(eventClassName);
	                $this.data("perfect-scrollbar", null);
	                $this.data("perfect-scrollbar-update", null);
	                $this.data("perfect-scrollbar-destroy", null);
	                $scrollbarX.remove();
	                $scrollbarY.remove();
	                $scrollbarXRail.remove();
	                $scrollbarYRail.remove();
	                // clean all variables
	                $scrollbarX = $scrollbarY = containerWidth = containerHeight = contentWidth = contentHeight = scrollbarXWidth = scrollbarXLeft = scrollbarXBottom = scrollbarYHeight = scrollbarYTop = scrollbarYRight = null;
	            };
	            var ieSupport = function ieSupport(version) {
	                $this.addClass("ie").addClass("ie" + version);
	                var bindHoverHandlers = function bindHoverHandlers() {
	                    var mouseenter = function mouseenter() {
	                        $(this).addClass("hover");
	                    };
	                    var mouseleave = function mouseleave() {
	                        $(this).removeClass("hover");
	                    };
	                    $this.bind("mouseenter" + eventClassName, mouseenter).bind("mouseleave" + eventClassName, mouseleave);
	                    $scrollbarXRail.bind("mouseenter" + eventClassName, mouseenter).bind("mouseleave" + eventClassName, mouseleave);
	                    $scrollbarYRail.bind("mouseenter" + eventClassName, mouseenter).bind("mouseleave" + eventClassName, mouseleave);
	                    $scrollbarX.bind("mouseenter" + eventClassName, mouseenter).bind("mouseleave" + eventClassName, mouseleave);
	                    $scrollbarY.bind("mouseenter" + eventClassName, mouseenter).bind("mouseleave" + eventClassName, mouseleave);
	                };
	                var fixIe6ScrollbarPosition = function fixIe6ScrollbarPosition() {
	                    updateScrollbarCss = function updateScrollbarCss() {
	                        $scrollbarX.css({
	                            left: scrollbarXLeft + $this.scrollLeft(),
	                            bottom: scrollbarXBottom,
	                            width: scrollbarXWidth
	                        });
	                        $scrollbarY.css({
	                            top: scrollbarYTop + $this.scrollTop(),
	                            right: scrollbarYRight,
	                            height: scrollbarYHeight
	                        });
	                        $scrollbarX.hide().show();
	                        $scrollbarY.hide().show();
	                    };
	                };
	                if (version === 6) {
	                    bindHoverHandlers();
	                    fixIe6ScrollbarPosition();
	                }
	            };
	            var supportsTouch = "ontouchstart" in window || window.DocumentTouch && document instanceof window.DocumentTouch;
	            var initialize = function initialize() {
	                var ieMatch = navigator.userAgent.toLowerCase().match(/(msie) ([\w.]+)/);
	                if (ieMatch && ieMatch[1] === "msie") {
	                    // must be executed at first, because 'ieSupport' may addClass to the container
	                    ieSupport(parseInt(ieMatch[2], 10));
	                }
	                updateBarSizeAndPosition();
	                bindScrollHandler();
	                bindMouseScrollXHandler();
	                bindMouseScrollYHandler();
	                bindRailClickHandler();
	                if (supportsTouch) {
	                    bindMobileTouchHandler();
	                }
	                if ($this.mousewheel) {
	                    bindMouseWheelHandler();
	                }
	                if (settings.useKeyboard) {
	                    bindKeyboardHandler();
	                }
	                $this.data("perfect-scrollbar", $this);
	                $this.data("perfect-scrollbar-update", updateBarSizeAndPosition);
	                $this.data("perfect-scrollbar-destroy", destroy);
	            };
	            // initialize
	            initialize();
	            return $this;
	        });
	    };
	});

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

/***/ "9d6592e537e3e6d02811":
/***/ (function(module, exports, __webpack_require__) {

	// style-loader: Adds some css to the DOM by adding a <style> tag
	
	// load the styles
	var content = __webpack_require__("9d6592e537e3e6d0281c");
	if(typeof content === 'string') content = [[module.id, content, '']];
	// add the styles to the DOM
	var update = __webpack_require__("bdf3ee7433c7d244da7e")(content, {"insertAt":"top"});
	if(content.locals) module.exports = content.locals;
	// Hot Module Replacement
	if(false) {
		// When the styles change, update the <style> tags
		if(!content.locals) {
			module.hot.accept("!!../../../../../node_modules/css-loader/index.js!./perfect-scrollbar.css", function() {
				var newContent = require("!!../../../../../node_modules/css-loader/index.js!./perfect-scrollbar.css");
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

	module.exports = __webpack_require__("977c745a5284d801809d");


/***/ })

/******/ });
//# sourceMappingURL=perfect-scrollbar.js.map