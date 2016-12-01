/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/build/";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports, __webpack_require__) {

	module.exports = __webpack_require__(4);


/***/ },
/* 1 */,
/* 2 */,
/* 3 */
/***/ function(module, exports) {

	"use strict";

	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

	/*** IMPORTS FROM imports-loader ***/
	var define = false;
	var _module = false;
	var _exports = false;
	(function () {

		/*!
	  * jQuery JavaScript Library v1.12.4
	  * http://jquery.com/
	  *
	  * Includes Sizzle.js
	  * http://sizzlejs.com/
	  *
	  * Copyright jQuery Foundation and other contributors
	  * Released under the MIT license
	  * http://jquery.org/license
	  *
	  * Date: 2016-05-20T17:17Z
	  */

		(function (global, factory) {

			if ((typeof _module === "undefined" ? "undefined" : _typeof(_module)) === "object" && _typeof(_module.exports) === "object") {
				// For CommonJS and CommonJS-like environments where a proper `window`
				// is present, execute the factory and get jQuery.
				// For environments that do not have a `window` with a `document`
				// (such as Node.js), expose a factory as module.exports.
				// This accentuates the need for the creation of a real `window`.
				// e.g. var jQuery = require("jquery")(window);
				// See ticket #14549 for more info.
				_module.exports = global.document ? factory(global, true) : function (w) {
					if (!w.document) {
						throw new Error("jQuery requires a window with a document");
					}
					return factory(w);
				};
			} else {
				factory(global);
			}

			// Pass this if window is not defined yet
		})(typeof window !== "undefined" ? window : this, function (window, noGlobal) {

			// Support: Firefox 18+
			// Can't be in strict mode, several libs including ASP.NET trace
			// the stack via arguments.caller.callee and Firefox dies if
			// you try to trace through "use strict" call chains. (#13335)
			//"use strict";
			var deletedIds = [];

			var document = window.document;

			var _slice = deletedIds.slice;

			var concat = deletedIds.concat;

			var push = deletedIds.push;

			var indexOf = deletedIds.indexOf;

			var class2type = {};

			var toString = class2type.toString;

			var hasOwn = class2type.hasOwnProperty;

			var support = {};

			var version = "1.12.4",


			// Define a local copy of jQuery
			jQuery = function jQuery(selector, context) {

				// The jQuery object is actually just the init constructor 'enhanced'
				// Need init if jQuery is called (just allow error to be thrown if not included)
				return new jQuery.fn.init(selector, context);
			},


			// Support: Android<4.1, IE<9
			// Make sure we trim BOM and NBSP
			rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,


			// Matches dashed string for camelizing
			rmsPrefix = /^-ms-/,
			    rdashAlpha = /-([\da-z])/gi,


			// Used by jQuery.camelCase as callback to replace()
			fcamelCase = function fcamelCase(all, letter) {
				return letter.toUpperCase();
			};

			jQuery.fn = jQuery.prototype = {

				// The current version of jQuery being used
				jquery: version,

				constructor: jQuery,

				// Start with an empty selector
				selector: "",

				// The default length of a jQuery object is 0
				length: 0,

				toArray: function toArray() {
					return _slice.call(this);
				},

				// Get the Nth element in the matched element set OR
				// Get the whole matched element set as a clean array
				get: function get(num) {
					return num != null ?

					// Return just the one element from the set
					num < 0 ? this[num + this.length] : this[num] :

					// Return all the elements in a clean array
					_slice.call(this);
				},

				// Take an array of elements and push it onto the stack
				// (returning the new matched element set)
				pushStack: function pushStack(elems) {

					// Build a new jQuery matched element set
					var ret = jQuery.merge(this.constructor(), elems);

					// Add the old object onto the stack (as a reference)
					ret.prevObject = this;
					ret.context = this.context;

					// Return the newly-formed element set
					return ret;
				},

				// Execute a callback for every element in the matched set.
				each: function each(callback) {
					return jQuery.each(this, callback);
				},

				map: function map(callback) {
					return this.pushStack(jQuery.map(this, function (elem, i) {
						return callback.call(elem, i, elem);
					}));
				},

				slice: function slice() {
					return this.pushStack(_slice.apply(this, arguments));
				},

				first: function first() {
					return this.eq(0);
				},

				last: function last() {
					return this.eq(-1);
				},

				eq: function eq(i) {
					var len = this.length,
					    j = +i + (i < 0 ? len : 0);
					return this.pushStack(j >= 0 && j < len ? [this[j]] : []);
				},

				end: function end() {
					return this.prevObject || this.constructor();
				},

				// For internal use only.
				// Behaves like an Array's method, not like a jQuery method.
				push: push,
				sort: deletedIds.sort,
				splice: deletedIds.splice
			};

			jQuery.extend = jQuery.fn.extend = function () {
				var src,
				    copyIsArray,
				    copy,
				    name,
				    options,
				    clone,
				    target = arguments[0] || {},
				    i = 1,
				    length = arguments.length,
				    deep = false;

				// Handle a deep copy situation
				if (typeof target === "boolean") {
					deep = target;

					// skip the boolean and the target
					target = arguments[i] || {};
					i++;
				}

				// Handle case when target is a string or something (possible in deep copy)
				if ((typeof target === "undefined" ? "undefined" : _typeof(target)) !== "object" && !jQuery.isFunction(target)) {
					target = {};
				}

				// extend jQuery itself if only one argument is passed
				if (i === length) {
					target = this;
					i--;
				}

				for (; i < length; i++) {

					// Only deal with non-null/undefined values
					if ((options = arguments[i]) != null) {

						// Extend the base object
						for (name in options) {
							src = target[name];
							copy = options[name];

							// Prevent never-ending loop
							if (target === copy) {
								continue;
							}

							// Recurse if we're merging plain objects or arrays
							if (deep && copy && (jQuery.isPlainObject(copy) || (copyIsArray = jQuery.isArray(copy)))) {

								if (copyIsArray) {
									copyIsArray = false;
									clone = src && jQuery.isArray(src) ? src : [];
								} else {
									clone = src && jQuery.isPlainObject(src) ? src : {};
								}

								// Never move original objects, clone them
								target[name] = jQuery.extend(deep, clone, copy);

								// Don't bring in undefined values
							} else if (copy !== undefined) {
								target[name] = copy;
							}
						}
					}
				}

				// Return the modified object
				return target;
			};

			jQuery.extend({

				// Unique for each copy of jQuery on the page
				expando: "jQuery" + (version + Math.random()).replace(/\D/g, ""),

				// Assume jQuery is ready without the ready module
				isReady: true,

				error: function error(msg) {
					throw new Error(msg);
				},

				noop: function noop() {},

				// See test/unit/core.js for details concerning isFunction.
				// Since version 1.3, DOM methods and functions like alert
				// aren't supported. They return false on IE (#2968).
				isFunction: function isFunction(obj) {
					return jQuery.type(obj) === "function";
				},

				isArray: Array.isArray || function (obj) {
					return jQuery.type(obj) === "array";
				},

				isWindow: function isWindow(obj) {
					/* jshint eqeqeq: false */
					return obj != null && obj == obj.window;
				},

				isNumeric: function isNumeric(obj) {

					// parseFloat NaNs numeric-cast false positives (null|true|false|"")
					// ...but misinterprets leading-number strings, particularly hex literals ("0x...")
					// subtraction forces infinities to NaN
					// adding 1 corrects loss of precision from parseFloat (#15100)
					var realStringObj = obj && obj.toString();
					return !jQuery.isArray(obj) && realStringObj - parseFloat(realStringObj) + 1 >= 0;
				},

				isEmptyObject: function isEmptyObject(obj) {
					var name;
					for (name in obj) {
						return false;
					}
					return true;
				},

				isPlainObject: function isPlainObject(obj) {
					var key;

					// Must be an Object.
					// Because of IE, we also have to check the presence of the constructor property.
					// Make sure that DOM nodes and window objects don't pass through, as well
					if (!obj || jQuery.type(obj) !== "object" || obj.nodeType || jQuery.isWindow(obj)) {
						return false;
					}

					try {

						// Not own constructor property must be Object
						if (obj.constructor && !hasOwn.call(obj, "constructor") && !hasOwn.call(obj.constructor.prototype, "isPrototypeOf")) {
							return false;
						}
					} catch (e) {

						// IE8,9 Will throw exceptions on certain host objects #9897
						return false;
					}

					// Support: IE<9
					// Handle iteration over inherited properties before own properties.
					if (!support.ownFirst) {
						for (key in obj) {
							return hasOwn.call(obj, key);
						}
					}

					// Own properties are enumerated firstly, so to speed up,
					// if last one is own, then all properties are own.
					for (key in obj) {}

					return key === undefined || hasOwn.call(obj, key);
				},

				type: function type(obj) {
					if (obj == null) {
						return obj + "";
					}
					return (typeof obj === "undefined" ? "undefined" : _typeof(obj)) === "object" || typeof obj === "function" ? class2type[toString.call(obj)] || "object" : typeof obj === "undefined" ? "undefined" : _typeof(obj);
				},

				// Workarounds based on findings by Jim Driscoll
				// http://weblogs.java.net/blog/driscoll/archive/2009/09/08/eval-javascript-global-context
				globalEval: function globalEval(data) {
					if (data && jQuery.trim(data)) {

						// We use execScript on Internet Explorer
						// We use an anonymous function so that context is window
						// rather than jQuery in Firefox
						(window.execScript || function (data) {
							window["eval"].call(window, data); // jscs:ignore requireDotNotation
						})(data);
					}
				},

				// Convert dashed to camelCase; used by the css and data modules
				// Microsoft forgot to hump their vendor prefix (#9572)
				camelCase: function camelCase(string) {
					return string.replace(rmsPrefix, "ms-").replace(rdashAlpha, fcamelCase);
				},

				nodeName: function nodeName(elem, name) {
					return elem.nodeName && elem.nodeName.toLowerCase() === name.toLowerCase();
				},

				each: function each(obj, callback) {
					var length,
					    i = 0;

					if (isArrayLike(obj)) {
						length = obj.length;
						for (; i < length; i++) {
							if (callback.call(obj[i], i, obj[i]) === false) {
								break;
							}
						}
					} else {
						for (i in obj) {
							if (callback.call(obj[i], i, obj[i]) === false) {
								break;
							}
						}
					}

					return obj;
				},

				// Support: Android<4.1, IE<9
				trim: function trim(text) {
					return text == null ? "" : (text + "").replace(rtrim, "");
				},

				// results is for internal usage only
				makeArray: function makeArray(arr, results) {
					var ret = results || [];

					if (arr != null) {
						if (isArrayLike(Object(arr))) {
							jQuery.merge(ret, typeof arr === "string" ? [arr] : arr);
						} else {
							push.call(ret, arr);
						}
					}

					return ret;
				},

				inArray: function inArray(elem, arr, i) {
					var len;

					if (arr) {
						if (indexOf) {
							return indexOf.call(arr, elem, i);
						}

						len = arr.length;
						i = i ? i < 0 ? Math.max(0, len + i) : i : 0;

						for (; i < len; i++) {

							// Skip accessing in sparse arrays
							if (i in arr && arr[i] === elem) {
								return i;
							}
						}
					}

					return -1;
				},

				merge: function merge(first, second) {
					var len = +second.length,
					    j = 0,
					    i = first.length;

					while (j < len) {
						first[i++] = second[j++];
					}

					// Support: IE<9
					// Workaround casting of .length to NaN on otherwise arraylike objects (e.g., NodeLists)
					if (len !== len) {
						while (second[j] !== undefined) {
							first[i++] = second[j++];
						}
					}

					first.length = i;

					return first;
				},

				grep: function grep(elems, callback, invert) {
					var callbackInverse,
					    matches = [],
					    i = 0,
					    length = elems.length,
					    callbackExpect = !invert;

					// Go through the array, only saving the items
					// that pass the validator function
					for (; i < length; i++) {
						callbackInverse = !callback(elems[i], i);
						if (callbackInverse !== callbackExpect) {
							matches.push(elems[i]);
						}
					}

					return matches;
				},

				// arg is for internal usage only
				map: function map(elems, callback, arg) {
					var length,
					    value,
					    i = 0,
					    ret = [];

					// Go through the array, translating each of the items to their new values
					if (isArrayLike(elems)) {
						length = elems.length;
						for (; i < length; i++) {
							value = callback(elems[i], i, arg);

							if (value != null) {
								ret.push(value);
							}
						}

						// Go through every key on the object,
					} else {
						for (i in elems) {
							value = callback(elems[i], i, arg);

							if (value != null) {
								ret.push(value);
							}
						}
					}

					// Flatten any nested arrays
					return concat.apply([], ret);
				},

				// A global GUID counter for objects
				guid: 1,

				// Bind a function to a context, optionally partially applying any
				// arguments.
				proxy: function proxy(fn, context) {
					var args, proxy, tmp;

					if (typeof context === "string") {
						tmp = fn[context];
						context = fn;
						fn = tmp;
					}

					// Quick check to determine if target is callable, in the spec
					// this throws a TypeError, but we will just return undefined.
					if (!jQuery.isFunction(fn)) {
						return undefined;
					}

					// Simulated bind
					args = _slice.call(arguments, 2);
					proxy = function proxy() {
						return fn.apply(context || this, args.concat(_slice.call(arguments)));
					};

					// Set the guid of unique handler to the same of original handler, so it can be removed
					proxy.guid = fn.guid = fn.guid || jQuery.guid++;

					return proxy;
				},

				now: function now() {
					return +new Date();
				},

				// jQuery.support is not used in Core but other projects attach their
				// properties to it so it needs to exist.
				support: support
			});

			// JSHint would error on this code due to the Symbol not being defined in ES5.
			// Defining this global in .jshintrc would create a danger of using the global
			// unguarded in another place, it seems safer to just disable JSHint for these
			// three lines.
			/* jshint ignore: start */
			if (typeof Symbol === "function") {
				jQuery.fn[Symbol.iterator] = deletedIds[Symbol.iterator];
			}
			/* jshint ignore: end */

			// Populate the class2type map
			jQuery.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "), function (i, name) {
				class2type["[object " + name + "]"] = name.toLowerCase();
			});

			function isArrayLike(obj) {

				// Support: iOS 8.2 (not reproducible in simulator)
				// `in` check used to prevent JIT error (gh-2145)
				// hasOwn isn't used here due to false negatives
				// regarding Nodelist length in IE
				var length = !!obj && "length" in obj && obj.length,
				    type = jQuery.type(obj);

				if (type === "function" || jQuery.isWindow(obj)) {
					return false;
				}

				return type === "array" || length === 0 || typeof length === "number" && length > 0 && length - 1 in obj;
			}
			var Sizzle =
			/*!
	   * Sizzle CSS Selector Engine v2.2.1
	   * http://sizzlejs.com/
	   *
	   * Copyright jQuery Foundation and other contributors
	   * Released under the MIT license
	   * http://jquery.org/license
	   *
	   * Date: 2015-10-17
	   */
			function (window) {

				var i,
				    support,
				    Expr,
				    getText,
				    isXML,
				    tokenize,
				    compile,
				    select,
				    outermostContext,
				    sortInput,
				    hasDuplicate,


				// Local document vars
				setDocument,
				    document,
				    docElem,
				    documentIsHTML,
				    rbuggyQSA,
				    rbuggyMatches,
				    matches,
				    contains,


				// Instance-specific data
				expando = "sizzle" + 1 * new Date(),
				    preferredDoc = window.document,
				    dirruns = 0,
				    done = 0,
				    classCache = createCache(),
				    tokenCache = createCache(),
				    compilerCache = createCache(),
				    sortOrder = function sortOrder(a, b) {
					if (a === b) {
						hasDuplicate = true;
					}
					return 0;
				},


				// General-purpose constants
				MAX_NEGATIVE = 1 << 31,


				// Instance methods
				hasOwn = {}.hasOwnProperty,
				    arr = [],
				    pop = arr.pop,
				    push_native = arr.push,
				    push = arr.push,
				    slice = arr.slice,

				// Use a stripped-down indexOf as it's faster than native
				// http://jsperf.com/thor-indexof-vs-for/5
				indexOf = function indexOf(list, elem) {
					var i = 0,
					    len = list.length;
					for (; i < len; i++) {
						if (list[i] === elem) {
							return i;
						}
					}
					return -1;
				},
				    booleans = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",


				// Regular expressions

				// http://www.w3.org/TR/css3-selectors/#whitespace
				whitespace = "[\\x20\\t\\r\\n\\f]",


				// http://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
				identifier = "(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",


				// Attribute selectors: http://www.w3.org/TR/selectors/#attribute-selectors
				attributes = "\\[" + whitespace + "*(" + identifier + ")(?:" + whitespace +
				// Operator (capture 2)
				"*([*^$|!~]?=)" + whitespace +
				// "Attribute values must be CSS identifiers [capture 5] or strings [capture 3 or capture 4]"
				"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + identifier + "))|)" + whitespace + "*\\]",
				    pseudos = ":(" + identifier + ")(?:\\((" +
				// To reduce the number of selectors needing tokenize in the preFilter, prefer arguments:
				// 1. quoted (capture 3; capture 4 or capture 5)
				"('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|" +
				// 2. simple (capture 6)
				"((?:\\\\.|[^\\\\()[\\]]|" + attributes + ")*)|" +
				// 3. anything else (capture 2)
				".*" + ")\\)|)",


				// Leading and non-escaped trailing whitespace, capturing some non-whitespace characters preceding the latter
				rwhitespace = new RegExp(whitespace + "+", "g"),
				    rtrim = new RegExp("^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g"),
				    rcomma = new RegExp("^" + whitespace + "*," + whitespace + "*"),
				    rcombinators = new RegExp("^" + whitespace + "*([>+~]|" + whitespace + ")" + whitespace + "*"),
				    rattributeQuotes = new RegExp("=" + whitespace + "*([^\\]'\"]*?)" + whitespace + "*\\]", "g"),
				    rpseudo = new RegExp(pseudos),
				    ridentifier = new RegExp("^" + identifier + "$"),
				    matchExpr = {
					"ID": new RegExp("^#(" + identifier + ")"),
					"CLASS": new RegExp("^\\.(" + identifier + ")"),
					"TAG": new RegExp("^(" + identifier + "|[*])"),
					"ATTR": new RegExp("^" + attributes),
					"PSEUDO": new RegExp("^" + pseudos),
					"CHILD": new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + whitespace + "*(even|odd|(([+-]|)(\\d*)n|)" + whitespace + "*(?:([+-]|)" + whitespace + "*(\\d+)|))" + whitespace + "*\\)|)", "i"),
					"bool": new RegExp("^(?:" + booleans + ")$", "i"),
					// For use in libraries implementing .is()
					// We use this for POS matching in `select`
					"needsContext": new RegExp("^" + whitespace + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + whitespace + "*((?:-\\d)?\\d*)" + whitespace + "*\\)|)(?=[^-]|$)", "i")
				},
				    rinputs = /^(?:input|select|textarea|button)$/i,
				    rheader = /^h\d$/i,
				    rnative = /^[^{]+\{\s*\[native \w/,


				// Easily-parseable/retrievable ID or TAG or CLASS selectors
				rquickExpr = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,
				    rsibling = /[+~]/,
				    rescape = /'|\\/g,


				// CSS escapes http://www.w3.org/TR/CSS21/syndata.html#escaped-characters
				runescape = new RegExp("\\\\([\\da-f]{1,6}" + whitespace + "?|(" + whitespace + ")|.)", "ig"),
				    funescape = function funescape(_, escaped, escapedWhitespace) {
					var high = "0x" + escaped - 0x10000;
					// NaN means non-codepoint
					// Support: Firefox<24
					// Workaround erroneous numeric interpretation of +"0x"
					return high !== high || escapedWhitespace ? escaped : high < 0 ?
					// BMP codepoint
					String.fromCharCode(high + 0x10000) :
					// Supplemental Plane codepoint (surrogate pair)
					String.fromCharCode(high >> 10 | 0xD800, high & 0x3FF | 0xDC00);
				},


				// Used for iframes
				// See setDocument()
				// Removing the function wrapper causes a "Permission Denied"
				// error in IE
				unloadHandler = function unloadHandler() {
					setDocument();
				};

				// Optimize for push.apply( _, NodeList )
				try {
					push.apply(arr = slice.call(preferredDoc.childNodes), preferredDoc.childNodes);
					// Support: Android<4.0
					// Detect silently failing push.apply
					arr[preferredDoc.childNodes.length].nodeType;
				} catch (e) {
					push = { apply: arr.length ?

						// Leverage slice if possible
						function (target, els) {
							push_native.apply(target, slice.call(els));
						} :

						// Support: IE<9
						// Otherwise append directly
						function (target, els) {
							var j = target.length,
							    i = 0;
							// Can't trust NodeList.length
							while (target[j++] = els[i++]) {}
							target.length = j - 1;
						}
					};
				}

				function Sizzle(selector, context, results, seed) {
					var m,
					    i,
					    elem,
					    nid,
					    nidselect,
					    match,
					    groups,
					    newSelector,
					    newContext = context && context.ownerDocument,


					// nodeType defaults to 9, since context defaults to document
					nodeType = context ? context.nodeType : 9;

					results = results || [];

					// Return early from calls with invalid selector or context
					if (typeof selector !== "string" || !selector || nodeType !== 1 && nodeType !== 9 && nodeType !== 11) {

						return results;
					}

					// Try to shortcut find operations (as opposed to filters) in HTML documents
					if (!seed) {

						if ((context ? context.ownerDocument || context : preferredDoc) !== document) {
							setDocument(context);
						}
						context = context || document;

						if (documentIsHTML) {

							// If the selector is sufficiently simple, try using a "get*By*" DOM method
							// (excepting DocumentFragment context, where the methods don't exist)
							if (nodeType !== 11 && (match = rquickExpr.exec(selector))) {

								// ID selector
								if (m = match[1]) {

									// Document context
									if (nodeType === 9) {
										if (elem = context.getElementById(m)) {

											// Support: IE, Opera, Webkit
											// TODO: identify versions
											// getElementById can match elements by name instead of ID
											if (elem.id === m) {
												results.push(elem);
												return results;
											}
										} else {
											return results;
										}

										// Element context
									} else {

										// Support: IE, Opera, Webkit
										// TODO: identify versions
										// getElementById can match elements by name instead of ID
										if (newContext && (elem = newContext.getElementById(m)) && contains(context, elem) && elem.id === m) {

											results.push(elem);
											return results;
										}
									}

									// Type selector
								} else if (match[2]) {
									push.apply(results, context.getElementsByTagName(selector));
									return results;

									// Class selector
								} else if ((m = match[3]) && support.getElementsByClassName && context.getElementsByClassName) {

									push.apply(results, context.getElementsByClassName(m));
									return results;
								}
							}

							// Take advantage of querySelectorAll
							if (support.qsa && !compilerCache[selector + " "] && (!rbuggyQSA || !rbuggyQSA.test(selector))) {

								if (nodeType !== 1) {
									newContext = context;
									newSelector = selector;

									// qSA looks outside Element context, which is not what we want
									// Thanks to Andrew Dupont for this workaround technique
									// Support: IE <=8
									// Exclude object elements
								} else if (context.nodeName.toLowerCase() !== "object") {

									// Capture the context ID, setting it first if necessary
									if (nid = context.getAttribute("id")) {
										nid = nid.replace(rescape, "\\$&");
									} else {
										context.setAttribute("id", nid = expando);
									}

									// Prefix every selector in the list
									groups = tokenize(selector);
									i = groups.length;
									nidselect = ridentifier.test(nid) ? "#" + nid : "[id='" + nid + "']";
									while (i--) {
										groups[i] = nidselect + " " + toSelector(groups[i]);
									}
									newSelector = groups.join(",");

									// Expand context for sibling selectors
									newContext = rsibling.test(selector) && testContext(context.parentNode) || context;
								}

								if (newSelector) {
									try {
										push.apply(results, newContext.querySelectorAll(newSelector));
										return results;
									} catch (qsaError) {} finally {
										if (nid === expando) {
											context.removeAttribute("id");
										}
									}
								}
							}
						}
					}

					// All others
					return select(selector.replace(rtrim, "$1"), context, results, seed);
				}

				/**
	    * Create key-value caches of limited size
	    * @returns {function(string, object)} Returns the Object data after storing it on itself with
	    *	property name the (space-suffixed) string and (if the cache is larger than Expr.cacheLength)
	    *	deleting the oldest entry
	    */
				function createCache() {
					var keys = [];

					function cache(key, value) {
						// Use (key + " ") to avoid collision with native prototype properties (see Issue #157)
						if (keys.push(key + " ") > Expr.cacheLength) {
							// Only keep the most recent entries
							delete cache[keys.shift()];
						}
						return cache[key + " "] = value;
					}
					return cache;
				}

				/**
	    * Mark a function for special use by Sizzle
	    * @param {Function} fn The function to mark
	    */
				function markFunction(fn) {
					fn[expando] = true;
					return fn;
				}

				/**
	    * Support testing using an element
	    * @param {Function} fn Passed the created div and expects a boolean result
	    */
				function assert(fn) {
					var div = document.createElement("div");

					try {
						return !!fn(div);
					} catch (e) {
						return false;
					} finally {
						// Remove from its parent by default
						if (div.parentNode) {
							div.parentNode.removeChild(div);
						}
						// release memory in IE
						div = null;
					}
				}

				/**
	    * Adds the same handler for all of the specified attrs
	    * @param {String} attrs Pipe-separated list of attributes
	    * @param {Function} handler The method that will be applied
	    */
				function addHandle(attrs, handler) {
					var arr = attrs.split("|"),
					    i = arr.length;

					while (i--) {
						Expr.attrHandle[arr[i]] = handler;
					}
				}

				/**
	    * Checks document order of two siblings
	    * @param {Element} a
	    * @param {Element} b
	    * @returns {Number} Returns less than 0 if a precedes b, greater than 0 if a follows b
	    */
				function siblingCheck(a, b) {
					var cur = b && a,
					    diff = cur && a.nodeType === 1 && b.nodeType === 1 && (~b.sourceIndex || MAX_NEGATIVE) - (~a.sourceIndex || MAX_NEGATIVE);

					// Use IE sourceIndex if available on both nodes
					if (diff) {
						return diff;
					}

					// Check if b follows a
					if (cur) {
						while (cur = cur.nextSibling) {
							if (cur === b) {
								return -1;
							}
						}
					}

					return a ? 1 : -1;
				}

				/**
	    * Returns a function to use in pseudos for input types
	    * @param {String} type
	    */
				function createInputPseudo(type) {
					return function (elem) {
						var name = elem.nodeName.toLowerCase();
						return name === "input" && elem.type === type;
					};
				}

				/**
	    * Returns a function to use in pseudos for buttons
	    * @param {String} type
	    */
				function createButtonPseudo(type) {
					return function (elem) {
						var name = elem.nodeName.toLowerCase();
						return (name === "input" || name === "button") && elem.type === type;
					};
				}

				/**
	    * Returns a function to use in pseudos for positionals
	    * @param {Function} fn
	    */
				function createPositionalPseudo(fn) {
					return markFunction(function (argument) {
						argument = +argument;
						return markFunction(function (seed, matches) {
							var j,
							    matchIndexes = fn([], seed.length, argument),
							    i = matchIndexes.length;

							// Match elements found at the specified indexes
							while (i--) {
								if (seed[j = matchIndexes[i]]) {
									seed[j] = !(matches[j] = seed[j]);
								}
							}
						});
					});
				}

				/**
	    * Checks a node for validity as a Sizzle context
	    * @param {Element|Object=} context
	    * @returns {Element|Object|Boolean} The input node if acceptable, otherwise a falsy value
	    */
				function testContext(context) {
					return context && typeof context.getElementsByTagName !== "undefined" && context;
				}

				// Expose support vars for convenience
				support = Sizzle.support = {};

				/**
	    * Detects XML nodes
	    * @param {Element|Object} elem An element or a document
	    * @returns {Boolean} True iff elem is a non-HTML XML node
	    */
				isXML = Sizzle.isXML = function (elem) {
					// documentElement is verified for cases where it doesn't yet exist
					// (such as loading iframes in IE - #4833)
					var documentElement = elem && (elem.ownerDocument || elem).documentElement;
					return documentElement ? documentElement.nodeName !== "HTML" : false;
				};

				/**
	    * Sets document-related variables once based on the current document
	    * @param {Element|Object} [doc] An element or document object to use to set the document
	    * @returns {Object} Returns the current document
	    */
				setDocument = Sizzle.setDocument = function (node) {
					var hasCompare,
					    parent,
					    doc = node ? node.ownerDocument || node : preferredDoc;

					// Return early if doc is invalid or already selected
					if (doc === document || doc.nodeType !== 9 || !doc.documentElement) {
						return document;
					}

					// Update global variables
					document = doc;
					docElem = document.documentElement;
					documentIsHTML = !isXML(document);

					// Support: IE 9-11, Edge
					// Accessing iframe documents after unload throws "permission denied" errors (jQuery #13936)
					if ((parent = document.defaultView) && parent.top !== parent) {
						// Support: IE 11
						if (parent.addEventListener) {
							parent.addEventListener("unload", unloadHandler, false);

							// Support: IE 9 - 10 only
						} else if (parent.attachEvent) {
							parent.attachEvent("onunload", unloadHandler);
						}
					}

					/* Attributes
	    ---------------------------------------------------------------------- */

					// Support: IE<8
					// Verify that getAttribute really returns attributes and not properties
					// (excepting IE8 booleans)
					support.attributes = assert(function (div) {
						div.className = "i";
						return !div.getAttribute("className");
					});

					/* getElement(s)By*
	    ---------------------------------------------------------------------- */

					// Check if getElementsByTagName("*") returns only elements
					support.getElementsByTagName = assert(function (div) {
						div.appendChild(document.createComment(""));
						return !div.getElementsByTagName("*").length;
					});

					// Support: IE<9
					support.getElementsByClassName = rnative.test(document.getElementsByClassName);

					// Support: IE<10
					// Check if getElementById returns elements by name
					// The broken getElementById methods don't pick up programatically-set names,
					// so use a roundabout getElementsByName test
					support.getById = assert(function (div) {
						docElem.appendChild(div).id = expando;
						return !document.getElementsByName || !document.getElementsByName(expando).length;
					});

					// ID find and filter
					if (support.getById) {
						Expr.find["ID"] = function (id, context) {
							if (typeof context.getElementById !== "undefined" && documentIsHTML) {
								var m = context.getElementById(id);
								return m ? [m] : [];
							}
						};
						Expr.filter["ID"] = function (id) {
							var attrId = id.replace(runescape, funescape);
							return function (elem) {
								return elem.getAttribute("id") === attrId;
							};
						};
					} else {
						// Support: IE6/7
						// getElementById is not reliable as a find shortcut
						delete Expr.find["ID"];

						Expr.filter["ID"] = function (id) {
							var attrId = id.replace(runescape, funescape);
							return function (elem) {
								var node = typeof elem.getAttributeNode !== "undefined" && elem.getAttributeNode("id");
								return node && node.value === attrId;
							};
						};
					}

					// Tag
					Expr.find["TAG"] = support.getElementsByTagName ? function (tag, context) {
						if (typeof context.getElementsByTagName !== "undefined") {
							return context.getElementsByTagName(tag);

							// DocumentFragment nodes don't have gEBTN
						} else if (support.qsa) {
							return context.querySelectorAll(tag);
						}
					} : function (tag, context) {
						var elem,
						    tmp = [],
						    i = 0,

						// By happy coincidence, a (broken) gEBTN appears on DocumentFragment nodes too
						results = context.getElementsByTagName(tag);

						// Filter out possible comments
						if (tag === "*") {
							while (elem = results[i++]) {
								if (elem.nodeType === 1) {
									tmp.push(elem);
								}
							}

							return tmp;
						}
						return results;
					};

					// Class
					Expr.find["CLASS"] = support.getElementsByClassName && function (className, context) {
						if (typeof context.getElementsByClassName !== "undefined" && documentIsHTML) {
							return context.getElementsByClassName(className);
						}
					};

					/* QSA/matchesSelector
	    ---------------------------------------------------------------------- */

					// QSA and matchesSelector support

					// matchesSelector(:active) reports false when true (IE9/Opera 11.5)
					rbuggyMatches = [];

					// qSa(:focus) reports false when true (Chrome 21)
					// We allow this because of a bug in IE8/9 that throws an error
					// whenever `document.activeElement` is accessed on an iframe
					// So, we allow :focus to pass through QSA all the time to avoid the IE error
					// See http://bugs.jquery.com/ticket/13378
					rbuggyQSA = [];

					if (support.qsa = rnative.test(document.querySelectorAll)) {
						// Build QSA regex
						// Regex strategy adopted from Diego Perini
						assert(function (div) {
							// Select is set to empty string on purpose
							// This is to test IE's treatment of not explicitly
							// setting a boolean content attribute,
							// since its presence should be enough
							// http://bugs.jquery.com/ticket/12359
							docElem.appendChild(div).innerHTML = "<a id='" + expando + "'></a>" + "<select id='" + expando + "-\r\\' msallowcapture=''>" + "<option selected=''></option></select>";

							// Support: IE8, Opera 11-12.16
							// Nothing should be selected when empty strings follow ^= or $= or *=
							// The test attribute must be unknown in Opera but "safe" for WinRT
							// http://msdn.microsoft.com/en-us/library/ie/hh465388.aspx#attribute_section
							if (div.querySelectorAll("[msallowcapture^='']").length) {
								rbuggyQSA.push("[*^$]=" + whitespace + "*(?:''|\"\")");
							}

							// Support: IE8
							// Boolean attributes and "value" are not treated correctly
							if (!div.querySelectorAll("[selected]").length) {
								rbuggyQSA.push("\\[" + whitespace + "*(?:value|" + booleans + ")");
							}

							// Support: Chrome<29, Android<4.4, Safari<7.0+, iOS<7.0+, PhantomJS<1.9.8+
							if (!div.querySelectorAll("[id~=" + expando + "-]").length) {
								rbuggyQSA.push("~=");
							}

							// Webkit/Opera - :checked should return selected option elements
							// http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
							// IE8 throws error here and will not see later tests
							if (!div.querySelectorAll(":checked").length) {
								rbuggyQSA.push(":checked");
							}

							// Support: Safari 8+, iOS 8+
							// https://bugs.webkit.org/show_bug.cgi?id=136851
							// In-page `selector#id sibing-combinator selector` fails
							if (!div.querySelectorAll("a#" + expando + "+*").length) {
								rbuggyQSA.push(".#.+[+~]");
							}
						});

						assert(function (div) {
							// Support: Windows 8 Native Apps
							// The type and name attributes are restricted during .innerHTML assignment
							var input = document.createElement("input");
							input.setAttribute("type", "hidden");
							div.appendChild(input).setAttribute("name", "D");

							// Support: IE8
							// Enforce case-sensitivity of name attribute
							if (div.querySelectorAll("[name=d]").length) {
								rbuggyQSA.push("name" + whitespace + "*[*^$|!~]?=");
							}

							// FF 3.5 - :enabled/:disabled and hidden elements (hidden elements are still enabled)
							// IE8 throws error here and will not see later tests
							if (!div.querySelectorAll(":enabled").length) {
								rbuggyQSA.push(":enabled", ":disabled");
							}

							// Opera 10-11 does not throw on post-comma invalid pseudos
							div.querySelectorAll("*,:x");
							rbuggyQSA.push(",.*:");
						});
					}

					if (support.matchesSelector = rnative.test(matches = docElem.matches || docElem.webkitMatchesSelector || docElem.mozMatchesSelector || docElem.oMatchesSelector || docElem.msMatchesSelector)) {

						assert(function (div) {
							// Check to see if it's possible to do matchesSelector
							// on a disconnected node (IE 9)
							support.disconnectedMatch = matches.call(div, "div");

							// This should fail with an exception
							// Gecko does not error, returns false instead
							matches.call(div, "[s!='']:x");
							rbuggyMatches.push("!=", pseudos);
						});
					}

					rbuggyQSA = rbuggyQSA.length && new RegExp(rbuggyQSA.join("|"));
					rbuggyMatches = rbuggyMatches.length && new RegExp(rbuggyMatches.join("|"));

					/* Contains
	    ---------------------------------------------------------------------- */
					hasCompare = rnative.test(docElem.compareDocumentPosition);

					// Element contains another
					// Purposefully self-exclusive
					// As in, an element does not contain itself
					contains = hasCompare || rnative.test(docElem.contains) ? function (a, b) {
						var adown = a.nodeType === 9 ? a.documentElement : a,
						    bup = b && b.parentNode;
						return a === bup || !!(bup && bup.nodeType === 1 && (adown.contains ? adown.contains(bup) : a.compareDocumentPosition && a.compareDocumentPosition(bup) & 16));
					} : function (a, b) {
						if (b) {
							while (b = b.parentNode) {
								if (b === a) {
									return true;
								}
							}
						}
						return false;
					};

					/* Sorting
	    ---------------------------------------------------------------------- */

					// Document order sorting
					sortOrder = hasCompare ? function (a, b) {

						// Flag for duplicate removal
						if (a === b) {
							hasDuplicate = true;
							return 0;
						}

						// Sort on method existence if only one input has compareDocumentPosition
						var compare = !a.compareDocumentPosition - !b.compareDocumentPosition;
						if (compare) {
							return compare;
						}

						// Calculate position if both inputs belong to the same document
						compare = (a.ownerDocument || a) === (b.ownerDocument || b) ? a.compareDocumentPosition(b) :

						// Otherwise we know they are disconnected
						1;

						// Disconnected nodes
						if (compare & 1 || !support.sortDetached && b.compareDocumentPosition(a) === compare) {

							// Choose the first element that is related to our preferred document
							if (a === document || a.ownerDocument === preferredDoc && contains(preferredDoc, a)) {
								return -1;
							}
							if (b === document || b.ownerDocument === preferredDoc && contains(preferredDoc, b)) {
								return 1;
							}

							// Maintain original order
							return sortInput ? indexOf(sortInput, a) - indexOf(sortInput, b) : 0;
						}

						return compare & 4 ? -1 : 1;
					} : function (a, b) {
						// Exit early if the nodes are identical
						if (a === b) {
							hasDuplicate = true;
							return 0;
						}

						var cur,
						    i = 0,
						    aup = a.parentNode,
						    bup = b.parentNode,
						    ap = [a],
						    bp = [b];

						// Parentless nodes are either documents or disconnected
						if (!aup || !bup) {
							return a === document ? -1 : b === document ? 1 : aup ? -1 : bup ? 1 : sortInput ? indexOf(sortInput, a) - indexOf(sortInput, b) : 0;

							// If the nodes are siblings, we can do a quick check
						} else if (aup === bup) {
							return siblingCheck(a, b);
						}

						// Otherwise we need full lists of their ancestors for comparison
						cur = a;
						while (cur = cur.parentNode) {
							ap.unshift(cur);
						}
						cur = b;
						while (cur = cur.parentNode) {
							bp.unshift(cur);
						}

						// Walk down the tree looking for a discrepancy
						while (ap[i] === bp[i]) {
							i++;
						}

						return i ?
						// Do a sibling check if the nodes have a common ancestor
						siblingCheck(ap[i], bp[i]) :

						// Otherwise nodes in our document sort first
						ap[i] === preferredDoc ? -1 : bp[i] === preferredDoc ? 1 : 0;
					};

					return document;
				};

				Sizzle.matches = function (expr, elements) {
					return Sizzle(expr, null, null, elements);
				};

				Sizzle.matchesSelector = function (elem, expr) {
					// Set document vars if needed
					if ((elem.ownerDocument || elem) !== document) {
						setDocument(elem);
					}

					// Make sure that attribute selectors are quoted
					expr = expr.replace(rattributeQuotes, "='$1']");

					if (support.matchesSelector && documentIsHTML && !compilerCache[expr + " "] && (!rbuggyMatches || !rbuggyMatches.test(expr)) && (!rbuggyQSA || !rbuggyQSA.test(expr))) {

						try {
							var ret = matches.call(elem, expr);

							// IE 9's matchesSelector returns false on disconnected nodes
							if (ret || support.disconnectedMatch ||
							// As well, disconnected nodes are said to be in a document
							// fragment in IE 9
							elem.document && elem.document.nodeType !== 11) {
								return ret;
							}
						} catch (e) {}
					}

					return Sizzle(expr, document, null, [elem]).length > 0;
				};

				Sizzle.contains = function (context, elem) {
					// Set document vars if needed
					if ((context.ownerDocument || context) !== document) {
						setDocument(context);
					}
					return contains(context, elem);
				};

				Sizzle.attr = function (elem, name) {
					// Set document vars if needed
					if ((elem.ownerDocument || elem) !== document) {
						setDocument(elem);
					}

					var fn = Expr.attrHandle[name.toLowerCase()],

					// Don't get fooled by Object.prototype properties (jQuery #13807)
					val = fn && hasOwn.call(Expr.attrHandle, name.toLowerCase()) ? fn(elem, name, !documentIsHTML) : undefined;

					return val !== undefined ? val : support.attributes || !documentIsHTML ? elem.getAttribute(name) : (val = elem.getAttributeNode(name)) && val.specified ? val.value : null;
				};

				Sizzle.error = function (msg) {
					throw new Error("Syntax error, unrecognized expression: " + msg);
				};

				/**
	    * Document sorting and removing duplicates
	    * @param {ArrayLike} results
	    */
				Sizzle.uniqueSort = function (results) {
					var elem,
					    duplicates = [],
					    j = 0,
					    i = 0;

					// Unless we *know* we can detect duplicates, assume their presence
					hasDuplicate = !support.detectDuplicates;
					sortInput = !support.sortStable && results.slice(0);
					results.sort(sortOrder);

					if (hasDuplicate) {
						while (elem = results[i++]) {
							if (elem === results[i]) {
								j = duplicates.push(i);
							}
						}
						while (j--) {
							results.splice(duplicates[j], 1);
						}
					}

					// Clear input after sorting to release objects
					// See https://github.com/jquery/sizzle/pull/225
					sortInput = null;

					return results;
				};

				/**
	    * Utility function for retrieving the text value of an array of DOM nodes
	    * @param {Array|Element} elem
	    */
				getText = Sizzle.getText = function (elem) {
					var node,
					    ret = "",
					    i = 0,
					    nodeType = elem.nodeType;

					if (!nodeType) {
						// If no nodeType, this is expected to be an array
						while (node = elem[i++]) {
							// Do not traverse comment nodes
							ret += getText(node);
						}
					} else if (nodeType === 1 || nodeType === 9 || nodeType === 11) {
						// Use textContent for elements
						// innerText usage removed for consistency of new lines (jQuery #11153)
						if (typeof elem.textContent === "string") {
							return elem.textContent;
						} else {
							// Traverse its children
							for (elem = elem.firstChild; elem; elem = elem.nextSibling) {
								ret += getText(elem);
							}
						}
					} else if (nodeType === 3 || nodeType === 4) {
						return elem.nodeValue;
					}
					// Do not include comment or processing instruction nodes

					return ret;
				};

				Expr = Sizzle.selectors = {

					// Can be adjusted by the user
					cacheLength: 50,

					createPseudo: markFunction,

					match: matchExpr,

					attrHandle: {},

					find: {},

					relative: {
						">": { dir: "parentNode", first: true },
						" ": { dir: "parentNode" },
						"+": { dir: "previousSibling", first: true },
						"~": { dir: "previousSibling" }
					},

					preFilter: {
						"ATTR": function ATTR(match) {
							match[1] = match[1].replace(runescape, funescape);

							// Move the given value to match[3] whether quoted or unquoted
							match[3] = (match[3] || match[4] || match[5] || "").replace(runescape, funescape);

							if (match[2] === "~=") {
								match[3] = " " + match[3] + " ";
							}

							return match.slice(0, 4);
						},

						"CHILD": function CHILD(match) {
							/* matches from matchExpr["CHILD"]
	      	1 type (only|nth|...)
	      	2 what (child|of-type)
	      	3 argument (even|odd|\d*|\d*n([+-]\d+)?|...)
	      	4 xn-component of xn+y argument ([+-]?\d*n|)
	      	5 sign of xn-component
	      	6 x of xn-component
	      	7 sign of y-component
	      	8 y of y-component
	      */
							match[1] = match[1].toLowerCase();

							if (match[1].slice(0, 3) === "nth") {
								// nth-* requires argument
								if (!match[3]) {
									Sizzle.error(match[0]);
								}

								// numeric x and y parameters for Expr.filter.CHILD
								// remember that false/true cast respectively to 0/1
								match[4] = +(match[4] ? match[5] + (match[6] || 1) : 2 * (match[3] === "even" || match[3] === "odd"));
								match[5] = +(match[7] + match[8] || match[3] === "odd");

								// other types prohibit arguments
							} else if (match[3]) {
								Sizzle.error(match[0]);
							}

							return match;
						},

						"PSEUDO": function PSEUDO(match) {
							var excess,
							    unquoted = !match[6] && match[2];

							if (matchExpr["CHILD"].test(match[0])) {
								return null;
							}

							// Accept quoted arguments as-is
							if (match[3]) {
								match[2] = match[4] || match[5] || "";

								// Strip excess characters from unquoted arguments
							} else if (unquoted && rpseudo.test(unquoted) && (
							// Get excess from tokenize (recursively)
							excess = tokenize(unquoted, true)) && (
							// advance to the next closing parenthesis
							excess = unquoted.indexOf(")", unquoted.length - excess) - unquoted.length)) {

								// excess is a negative index
								match[0] = match[0].slice(0, excess);
								match[2] = unquoted.slice(0, excess);
							}

							// Return only captures needed by the pseudo filter method (type and argument)
							return match.slice(0, 3);
						}
					},

					filter: {

						"TAG": function TAG(nodeNameSelector) {
							var nodeName = nodeNameSelector.replace(runescape, funescape).toLowerCase();
							return nodeNameSelector === "*" ? function () {
								return true;
							} : function (elem) {
								return elem.nodeName && elem.nodeName.toLowerCase() === nodeName;
							};
						},

						"CLASS": function CLASS(className) {
							var pattern = classCache[className + " "];

							return pattern || (pattern = new RegExp("(^|" + whitespace + ")" + className + "(" + whitespace + "|$)")) && classCache(className, function (elem) {
								return pattern.test(typeof elem.className === "string" && elem.className || typeof elem.getAttribute !== "undefined" && elem.getAttribute("class") || "");
							});
						},

						"ATTR": function ATTR(name, operator, check) {
							return function (elem) {
								var result = Sizzle.attr(elem, name);

								if (result == null) {
									return operator === "!=";
								}
								if (!operator) {
									return true;
								}

								result += "";

								return operator === "=" ? result === check : operator === "!=" ? result !== check : operator === "^=" ? check && result.indexOf(check) === 0 : operator === "*=" ? check && result.indexOf(check) > -1 : operator === "$=" ? check && result.slice(-check.length) === check : operator === "~=" ? (" " + result.replace(rwhitespace, " ") + " ").indexOf(check) > -1 : operator === "|=" ? result === check || result.slice(0, check.length + 1) === check + "-" : false;
							};
						},

						"CHILD": function CHILD(type, what, argument, first, last) {
							var simple = type.slice(0, 3) !== "nth",
							    forward = type.slice(-4) !== "last",
							    ofType = what === "of-type";

							return first === 1 && last === 0 ?

							// Shortcut for :nth-*(n)
							function (elem) {
								return !!elem.parentNode;
							} : function (elem, context, xml) {
								var cache,
								    uniqueCache,
								    outerCache,
								    node,
								    nodeIndex,
								    start,
								    dir = simple !== forward ? "nextSibling" : "previousSibling",
								    parent = elem.parentNode,
								    name = ofType && elem.nodeName.toLowerCase(),
								    useCache = !xml && !ofType,
								    diff = false;

								if (parent) {

									// :(first|last|only)-(child|of-type)
									if (simple) {
										while (dir) {
											node = elem;
											while (node = node[dir]) {
												if (ofType ? node.nodeName.toLowerCase() === name : node.nodeType === 1) {

													return false;
												}
											}
											// Reverse direction for :only-* (if we haven't yet done so)
											start = dir = type === "only" && !start && "nextSibling";
										}
										return true;
									}

									start = [forward ? parent.firstChild : parent.lastChild];

									// non-xml :nth-child(...) stores cache data on `parent`
									if (forward && useCache) {

										// Seek `elem` from a previously-cached index

										// ...in a gzip-friendly way
										node = parent;
										outerCache = node[expando] || (node[expando] = {});

										// Support: IE <9 only
										// Defend against cloned attroperties (jQuery gh-1709)
										uniqueCache = outerCache[node.uniqueID] || (outerCache[node.uniqueID] = {});

										cache = uniqueCache[type] || [];
										nodeIndex = cache[0] === dirruns && cache[1];
										diff = nodeIndex && cache[2];
										node = nodeIndex && parent.childNodes[nodeIndex];

										while (node = ++nodeIndex && node && node[dir] || (

										// Fallback to seeking `elem` from the start
										diff = nodeIndex = 0) || start.pop()) {

											// When found, cache indexes on `parent` and break
											if (node.nodeType === 1 && ++diff && node === elem) {
												uniqueCache[type] = [dirruns, nodeIndex, diff];
												break;
											}
										}
									} else {
										// Use previously-cached element index if available
										if (useCache) {
											// ...in a gzip-friendly way
											node = elem;
											outerCache = node[expando] || (node[expando] = {});

											// Support: IE <9 only
											// Defend against cloned attroperties (jQuery gh-1709)
											uniqueCache = outerCache[node.uniqueID] || (outerCache[node.uniqueID] = {});

											cache = uniqueCache[type] || [];
											nodeIndex = cache[0] === dirruns && cache[1];
											diff = nodeIndex;
										}

										// xml :nth-child(...)
										// or :nth-last-child(...) or :nth(-last)?-of-type(...)
										if (diff === false) {
											// Use the same loop as above to seek `elem` from the start
											while (node = ++nodeIndex && node && node[dir] || (diff = nodeIndex = 0) || start.pop()) {

												if ((ofType ? node.nodeName.toLowerCase() === name : node.nodeType === 1) && ++diff) {

													// Cache the index of each encountered element
													if (useCache) {
														outerCache = node[expando] || (node[expando] = {});

														// Support: IE <9 only
														// Defend against cloned attroperties (jQuery gh-1709)
														uniqueCache = outerCache[node.uniqueID] || (outerCache[node.uniqueID] = {});

														uniqueCache[type] = [dirruns, diff];
													}

													if (node === elem) {
														break;
													}
												}
											}
										}
									}

									// Incorporate the offset, then check against cycle size
									diff -= last;
									return diff === first || diff % first === 0 && diff / first >= 0;
								}
							};
						},

						"PSEUDO": function PSEUDO(pseudo, argument) {
							// pseudo-class names are case-insensitive
							// http://www.w3.org/TR/selectors/#pseudo-classes
							// Prioritize by case sensitivity in case custom pseudos are added with uppercase letters
							// Remember that setFilters inherits from pseudos
							var args,
							    fn = Expr.pseudos[pseudo] || Expr.setFilters[pseudo.toLowerCase()] || Sizzle.error("unsupported pseudo: " + pseudo);

							// The user may use createPseudo to indicate that
							// arguments are needed to create the filter function
							// just as Sizzle does
							if (fn[expando]) {
								return fn(argument);
							}

							// But maintain support for old signatures
							if (fn.length > 1) {
								args = [pseudo, pseudo, "", argument];
								return Expr.setFilters.hasOwnProperty(pseudo.toLowerCase()) ? markFunction(function (seed, matches) {
									var idx,
									    matched = fn(seed, argument),
									    i = matched.length;
									while (i--) {
										idx = indexOf(seed, matched[i]);
										seed[idx] = !(matches[idx] = matched[i]);
									}
								}) : function (elem) {
									return fn(elem, 0, args);
								};
							}

							return fn;
						}
					},

					pseudos: {
						// Potentially complex pseudos
						"not": markFunction(function (selector) {
							// Trim the selector passed to compile
							// to avoid treating leading and trailing
							// spaces as combinators
							var input = [],
							    results = [],
							    matcher = compile(selector.replace(rtrim, "$1"));

							return matcher[expando] ? markFunction(function (seed, matches, context, xml) {
								var elem,
								    unmatched = matcher(seed, null, xml, []),
								    i = seed.length;

								// Match elements unmatched by `matcher`
								while (i--) {
									if (elem = unmatched[i]) {
										seed[i] = !(matches[i] = elem);
									}
								}
							}) : function (elem, context, xml) {
								input[0] = elem;
								matcher(input, null, xml, results);
								// Don't keep the element (issue #299)
								input[0] = null;
								return !results.pop();
							};
						}),

						"has": markFunction(function (selector) {
							return function (elem) {
								return Sizzle(selector, elem).length > 0;
							};
						}),

						"contains": markFunction(function (text) {
							text = text.replace(runescape, funescape);
							return function (elem) {
								return (elem.textContent || elem.innerText || getText(elem)).indexOf(text) > -1;
							};
						}),

						// "Whether an element is represented by a :lang() selector
						// is based solely on the element's language value
						// being equal to the identifier C,
						// or beginning with the identifier C immediately followed by "-".
						// The matching of C against the element's language value is performed case-insensitively.
						// The identifier C does not have to be a valid language name."
						// http://www.w3.org/TR/selectors/#lang-pseudo
						"lang": markFunction(function (lang) {
							// lang value must be a valid identifier
							if (!ridentifier.test(lang || "")) {
								Sizzle.error("unsupported lang: " + lang);
							}
							lang = lang.replace(runescape, funescape).toLowerCase();
							return function (elem) {
								var elemLang;
								do {
									if (elemLang = documentIsHTML ? elem.lang : elem.getAttribute("xml:lang") || elem.getAttribute("lang")) {

										elemLang = elemLang.toLowerCase();
										return elemLang === lang || elemLang.indexOf(lang + "-") === 0;
									}
								} while ((elem = elem.parentNode) && elem.nodeType === 1);
								return false;
							};
						}),

						// Miscellaneous
						"target": function target(elem) {
							var hash = window.location && window.location.hash;
							return hash && hash.slice(1) === elem.id;
						},

						"root": function root(elem) {
							return elem === docElem;
						},

						"focus": function focus(elem) {
							return elem === document.activeElement && (!document.hasFocus || document.hasFocus()) && !!(elem.type || elem.href || ~elem.tabIndex);
						},

						// Boolean properties
						"enabled": function enabled(elem) {
							return elem.disabled === false;
						},

						"disabled": function disabled(elem) {
							return elem.disabled === true;
						},

						"checked": function checked(elem) {
							// In CSS3, :checked should return both checked and selected elements
							// http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
							var nodeName = elem.nodeName.toLowerCase();
							return nodeName === "input" && !!elem.checked || nodeName === "option" && !!elem.selected;
						},

						"selected": function selected(elem) {
							// Accessing this property makes selected-by-default
							// options in Safari work properly
							if (elem.parentNode) {
								elem.parentNode.selectedIndex;
							}

							return elem.selected === true;
						},

						// Contents
						"empty": function empty(elem) {
							// http://www.w3.org/TR/selectors/#empty-pseudo
							// :empty is negated by element (1) or content nodes (text: 3; cdata: 4; entity ref: 5),
							//   but not by others (comment: 8; processing instruction: 7; etc.)
							// nodeType < 6 works because attributes (2) do not appear as children
							for (elem = elem.firstChild; elem; elem = elem.nextSibling) {
								if (elem.nodeType < 6) {
									return false;
								}
							}
							return true;
						},

						"parent": function parent(elem) {
							return !Expr.pseudos["empty"](elem);
						},

						// Element/input types
						"header": function header(elem) {
							return rheader.test(elem.nodeName);
						},

						"input": function input(elem) {
							return rinputs.test(elem.nodeName);
						},

						"button": function button(elem) {
							var name = elem.nodeName.toLowerCase();
							return name === "input" && elem.type === "button" || name === "button";
						},

						"text": function text(elem) {
							var attr;
							return elem.nodeName.toLowerCase() === "input" && elem.type === "text" && (

							// Support: IE<8
							// New HTML5 attribute values (e.g., "search") appear with elem.type === "text"
							(attr = elem.getAttribute("type")) == null || attr.toLowerCase() === "text");
						},

						// Position-in-collection
						"first": createPositionalPseudo(function () {
							return [0];
						}),

						"last": createPositionalPseudo(function (matchIndexes, length) {
							return [length - 1];
						}),

						"eq": createPositionalPseudo(function (matchIndexes, length, argument) {
							return [argument < 0 ? argument + length : argument];
						}),

						"even": createPositionalPseudo(function (matchIndexes, length) {
							var i = 0;
							for (; i < length; i += 2) {
								matchIndexes.push(i);
							}
							return matchIndexes;
						}),

						"odd": createPositionalPseudo(function (matchIndexes, length) {
							var i = 1;
							for (; i < length; i += 2) {
								matchIndexes.push(i);
							}
							return matchIndexes;
						}),

						"lt": createPositionalPseudo(function (matchIndexes, length, argument) {
							var i = argument < 0 ? argument + length : argument;
							for (; --i >= 0;) {
								matchIndexes.push(i);
							}
							return matchIndexes;
						}),

						"gt": createPositionalPseudo(function (matchIndexes, length, argument) {
							var i = argument < 0 ? argument + length : argument;
							for (; ++i < length;) {
								matchIndexes.push(i);
							}
							return matchIndexes;
						})
					}
				};

				Expr.pseudos["nth"] = Expr.pseudos["eq"];

				// Add button/input type pseudos
				for (i in { radio: true, checkbox: true, file: true, password: true, image: true }) {
					Expr.pseudos[i] = createInputPseudo(i);
				}
				for (i in { submit: true, reset: true }) {
					Expr.pseudos[i] = createButtonPseudo(i);
				}

				// Easy API for creating new setFilters
				function setFilters() {}
				setFilters.prototype = Expr.filters = Expr.pseudos;
				Expr.setFilters = new setFilters();

				tokenize = Sizzle.tokenize = function (selector, parseOnly) {
					var matched,
					    match,
					    tokens,
					    type,
					    soFar,
					    groups,
					    preFilters,
					    cached = tokenCache[selector + " "];

					if (cached) {
						return parseOnly ? 0 : cached.slice(0);
					}

					soFar = selector;
					groups = [];
					preFilters = Expr.preFilter;

					while (soFar) {

						// Comma and first run
						if (!matched || (match = rcomma.exec(soFar))) {
							if (match) {
								// Don't consume trailing commas as valid
								soFar = soFar.slice(match[0].length) || soFar;
							}
							groups.push(tokens = []);
						}

						matched = false;

						// Combinators
						if (match = rcombinators.exec(soFar)) {
							matched = match.shift();
							tokens.push({
								value: matched,
								// Cast descendant combinators to space
								type: match[0].replace(rtrim, " ")
							});
							soFar = soFar.slice(matched.length);
						}

						// Filters
						for (type in Expr.filter) {
							if ((match = matchExpr[type].exec(soFar)) && (!preFilters[type] || (match = preFilters[type](match)))) {
								matched = match.shift();
								tokens.push({
									value: matched,
									type: type,
									matches: match
								});
								soFar = soFar.slice(matched.length);
							}
						}

						if (!matched) {
							break;
						}
					}

					// Return the length of the invalid excess
					// if we're just parsing
					// Otherwise, throw an error or return tokens
					return parseOnly ? soFar.length : soFar ? Sizzle.error(selector) :
					// Cache the tokens
					tokenCache(selector, groups).slice(0);
				};

				function toSelector(tokens) {
					var i = 0,
					    len = tokens.length,
					    selector = "";
					for (; i < len; i++) {
						selector += tokens[i].value;
					}
					return selector;
				}

				function addCombinator(matcher, combinator, base) {
					var dir = combinator.dir,
					    checkNonElements = base && dir === "parentNode",
					    doneName = done++;

					return combinator.first ?
					// Check against closest ancestor/preceding element
					function (elem, context, xml) {
						while (elem = elem[dir]) {
							if (elem.nodeType === 1 || checkNonElements) {
								return matcher(elem, context, xml);
							}
						}
					} :

					// Check against all ancestor/preceding elements
					function (elem, context, xml) {
						var oldCache,
						    uniqueCache,
						    outerCache,
						    newCache = [dirruns, doneName];

						// We can't set arbitrary data on XML nodes, so they don't benefit from combinator caching
						if (xml) {
							while (elem = elem[dir]) {
								if (elem.nodeType === 1 || checkNonElements) {
									if (matcher(elem, context, xml)) {
										return true;
									}
								}
							}
						} else {
							while (elem = elem[dir]) {
								if (elem.nodeType === 1 || checkNonElements) {
									outerCache = elem[expando] || (elem[expando] = {});

									// Support: IE <9 only
									// Defend against cloned attroperties (jQuery gh-1709)
									uniqueCache = outerCache[elem.uniqueID] || (outerCache[elem.uniqueID] = {});

									if ((oldCache = uniqueCache[dir]) && oldCache[0] === dirruns && oldCache[1] === doneName) {

										// Assign to newCache so results back-propagate to previous elements
										return newCache[2] = oldCache[2];
									} else {
										// Reuse newcache so results back-propagate to previous elements
										uniqueCache[dir] = newCache;

										// A match means we're done; a fail means we have to keep checking
										if (newCache[2] = matcher(elem, context, xml)) {
											return true;
										}
									}
								}
							}
						}
					};
				}

				function elementMatcher(matchers) {
					return matchers.length > 1 ? function (elem, context, xml) {
						var i = matchers.length;
						while (i--) {
							if (!matchers[i](elem, context, xml)) {
								return false;
							}
						}
						return true;
					} : matchers[0];
				}

				function multipleContexts(selector, contexts, results) {
					var i = 0,
					    len = contexts.length;
					for (; i < len; i++) {
						Sizzle(selector, contexts[i], results);
					}
					return results;
				}

				function condense(unmatched, map, filter, context, xml) {
					var elem,
					    newUnmatched = [],
					    i = 0,
					    len = unmatched.length,
					    mapped = map != null;

					for (; i < len; i++) {
						if (elem = unmatched[i]) {
							if (!filter || filter(elem, context, xml)) {
								newUnmatched.push(elem);
								if (mapped) {
									map.push(i);
								}
							}
						}
					}

					return newUnmatched;
				}

				function setMatcher(preFilter, selector, matcher, postFilter, postFinder, postSelector) {
					if (postFilter && !postFilter[expando]) {
						postFilter = setMatcher(postFilter);
					}
					if (postFinder && !postFinder[expando]) {
						postFinder = setMatcher(postFinder, postSelector);
					}
					return markFunction(function (seed, results, context, xml) {
						var temp,
						    i,
						    elem,
						    preMap = [],
						    postMap = [],
						    preexisting = results.length,


						// Get initial elements from seed or context
						elems = seed || multipleContexts(selector || "*", context.nodeType ? [context] : context, []),


						// Prefilter to get matcher input, preserving a map for seed-results synchronization
						matcherIn = preFilter && (seed || !selector) ? condense(elems, preMap, preFilter, context, xml) : elems,
						    matcherOut = matcher ?
						// If we have a postFinder, or filtered seed, or non-seed postFilter or preexisting results,
						postFinder || (seed ? preFilter : preexisting || postFilter) ?

						// ...intermediate processing is necessary
						[] :

						// ...otherwise use results directly
						results : matcherIn;

						// Find primary matches
						if (matcher) {
							matcher(matcherIn, matcherOut, context, xml);
						}

						// Apply postFilter
						if (postFilter) {
							temp = condense(matcherOut, postMap);
							postFilter(temp, [], context, xml);

							// Un-match failing elements by moving them back to matcherIn
							i = temp.length;
							while (i--) {
								if (elem = temp[i]) {
									matcherOut[postMap[i]] = !(matcherIn[postMap[i]] = elem);
								}
							}
						}

						if (seed) {
							if (postFinder || preFilter) {
								if (postFinder) {
									// Get the final matcherOut by condensing this intermediate into postFinder contexts
									temp = [];
									i = matcherOut.length;
									while (i--) {
										if (elem = matcherOut[i]) {
											// Restore matcherIn since elem is not yet a final match
											temp.push(matcherIn[i] = elem);
										}
									}
									postFinder(null, matcherOut = [], temp, xml);
								}

								// Move matched elements from seed to results to keep them synchronized
								i = matcherOut.length;
								while (i--) {
									if ((elem = matcherOut[i]) && (temp = postFinder ? indexOf(seed, elem) : preMap[i]) > -1) {

										seed[temp] = !(results[temp] = elem);
									}
								}
							}

							// Add elements to results, through postFinder if defined
						} else {
							matcherOut = condense(matcherOut === results ? matcherOut.splice(preexisting, matcherOut.length) : matcherOut);
							if (postFinder) {
								postFinder(null, results, matcherOut, xml);
							} else {
								push.apply(results, matcherOut);
							}
						}
					});
				}

				function matcherFromTokens(tokens) {
					var checkContext,
					    matcher,
					    j,
					    len = tokens.length,
					    leadingRelative = Expr.relative[tokens[0].type],
					    implicitRelative = leadingRelative || Expr.relative[" "],
					    i = leadingRelative ? 1 : 0,


					// The foundational matcher ensures that elements are reachable from top-level context(s)
					matchContext = addCombinator(function (elem) {
						return elem === checkContext;
					}, implicitRelative, true),
					    matchAnyContext = addCombinator(function (elem) {
						return indexOf(checkContext, elem) > -1;
					}, implicitRelative, true),
					    matchers = [function (elem, context, xml) {
						var ret = !leadingRelative && (xml || context !== outermostContext) || ((checkContext = context).nodeType ? matchContext(elem, context, xml) : matchAnyContext(elem, context, xml));
						// Avoid hanging onto element (issue #299)
						checkContext = null;
						return ret;
					}];

					for (; i < len; i++) {
						if (matcher = Expr.relative[tokens[i].type]) {
							matchers = [addCombinator(elementMatcher(matchers), matcher)];
						} else {
							matcher = Expr.filter[tokens[i].type].apply(null, tokens[i].matches);

							// Return special upon seeing a positional matcher
							if (matcher[expando]) {
								// Find the next relative operator (if any) for proper handling
								j = ++i;
								for (; j < len; j++) {
									if (Expr.relative[tokens[j].type]) {
										break;
									}
								}
								return setMatcher(i > 1 && elementMatcher(matchers), i > 1 && toSelector(
								// If the preceding token was a descendant combinator, insert an implicit any-element `*`
								tokens.slice(0, i - 1).concat({ value: tokens[i - 2].type === " " ? "*" : "" })).replace(rtrim, "$1"), matcher, i < j && matcherFromTokens(tokens.slice(i, j)), j < len && matcherFromTokens(tokens = tokens.slice(j)), j < len && toSelector(tokens));
							}
							matchers.push(matcher);
						}
					}

					return elementMatcher(matchers);
				}

				function matcherFromGroupMatchers(elementMatchers, setMatchers) {
					var bySet = setMatchers.length > 0,
					    byElement = elementMatchers.length > 0,
					    superMatcher = function superMatcher(seed, context, xml, results, outermost) {
						var elem,
						    j,
						    matcher,
						    matchedCount = 0,
						    i = "0",
						    unmatched = seed && [],
						    setMatched = [],
						    contextBackup = outermostContext,

						// We must always have either seed elements or outermost context
						elems = seed || byElement && Expr.find["TAG"]("*", outermost),

						// Use integer dirruns iff this is the outermost matcher
						dirrunsUnique = dirruns += contextBackup == null ? 1 : Math.random() || 0.1,
						    len = elems.length;

						if (outermost) {
							outermostContext = context === document || context || outermost;
						}

						// Add elements passing elementMatchers directly to results
						// Support: IE<9, Safari
						// Tolerate NodeList properties (IE: "length"; Safari: <number>) matching elements by id
						for (; i !== len && (elem = elems[i]) != null; i++) {
							if (byElement && elem) {
								j = 0;
								if (!context && elem.ownerDocument !== document) {
									setDocument(elem);
									xml = !documentIsHTML;
								}
								while (matcher = elementMatchers[j++]) {
									if (matcher(elem, context || document, xml)) {
										results.push(elem);
										break;
									}
								}
								if (outermost) {
									dirruns = dirrunsUnique;
								}
							}

							// Track unmatched elements for set filters
							if (bySet) {
								// They will have gone through all possible matchers
								if (elem = !matcher && elem) {
									matchedCount--;
								}

								// Lengthen the array for every element, matched or not
								if (seed) {
									unmatched.push(elem);
								}
							}
						}

						// `i` is now the count of elements visited above, and adding it to `matchedCount`
						// makes the latter nonnegative.
						matchedCount += i;

						// Apply set filters to unmatched elements
						// NOTE: This can be skipped if there are no unmatched elements (i.e., `matchedCount`
						// equals `i`), unless we didn't visit _any_ elements in the above loop because we have
						// no element matchers and no seed.
						// Incrementing an initially-string "0" `i` allows `i` to remain a string only in that
						// case, which will result in a "00" `matchedCount` that differs from `i` but is also
						// numerically zero.
						if (bySet && i !== matchedCount) {
							j = 0;
							while (matcher = setMatchers[j++]) {
								matcher(unmatched, setMatched, context, xml);
							}

							if (seed) {
								// Reintegrate element matches to eliminate the need for sorting
								if (matchedCount > 0) {
									while (i--) {
										if (!(unmatched[i] || setMatched[i])) {
											setMatched[i] = pop.call(results);
										}
									}
								}

								// Discard index placeholder values to get only actual matches
								setMatched = condense(setMatched);
							}

							// Add matches to results
							push.apply(results, setMatched);

							// Seedless set matches succeeding multiple successful matchers stipulate sorting
							if (outermost && !seed && setMatched.length > 0 && matchedCount + setMatchers.length > 1) {

								Sizzle.uniqueSort(results);
							}
						}

						// Override manipulation of globals by nested matchers
						if (outermost) {
							dirruns = dirrunsUnique;
							outermostContext = contextBackup;
						}

						return unmatched;
					};

					return bySet ? markFunction(superMatcher) : superMatcher;
				}

				compile = Sizzle.compile = function (selector, match /* Internal Use Only */) {
					var i,
					    setMatchers = [],
					    elementMatchers = [],
					    cached = compilerCache[selector + " "];

					if (!cached) {
						// Generate a function of recursive functions that can be used to check each element
						if (!match) {
							match = tokenize(selector);
						}
						i = match.length;
						while (i--) {
							cached = matcherFromTokens(match[i]);
							if (cached[expando]) {
								setMatchers.push(cached);
							} else {
								elementMatchers.push(cached);
							}
						}

						// Cache the compiled function
						cached = compilerCache(selector, matcherFromGroupMatchers(elementMatchers, setMatchers));

						// Save selector and tokenization
						cached.selector = selector;
					}
					return cached;
				};

				/**
	    * A low-level selection function that works with Sizzle's compiled
	    *  selector functions
	    * @param {String|Function} selector A selector or a pre-compiled
	    *  selector function built with Sizzle.compile
	    * @param {Element} context
	    * @param {Array} [results]
	    * @param {Array} [seed] A set of elements to match against
	    */
				select = Sizzle.select = function (selector, context, results, seed) {
					var i,
					    tokens,
					    token,
					    type,
					    find,
					    compiled = typeof selector === "function" && selector,
					    match = !seed && tokenize(selector = compiled.selector || selector);

					results = results || [];

					// Try to minimize operations if there is only one selector in the list and no seed
					// (the latter of which guarantees us context)
					if (match.length === 1) {

						// Reduce context if the leading compound selector is an ID
						tokens = match[0] = match[0].slice(0);
						if (tokens.length > 2 && (token = tokens[0]).type === "ID" && support.getById && context.nodeType === 9 && documentIsHTML && Expr.relative[tokens[1].type]) {

							context = (Expr.find["ID"](token.matches[0].replace(runescape, funescape), context) || [])[0];
							if (!context) {
								return results;

								// Precompiled matchers will still verify ancestry, so step up a level
							} else if (compiled) {
								context = context.parentNode;
							}

							selector = selector.slice(tokens.shift().value.length);
						}

						// Fetch a seed set for right-to-left matching
						i = matchExpr["needsContext"].test(selector) ? 0 : tokens.length;
						while (i--) {
							token = tokens[i];

							// Abort if we hit a combinator
							if (Expr.relative[type = token.type]) {
								break;
							}
							if (find = Expr.find[type]) {
								// Search, expanding context for leading sibling combinators
								if (seed = find(token.matches[0].replace(runescape, funescape), rsibling.test(tokens[0].type) && testContext(context.parentNode) || context)) {

									// If seed is empty or no tokens remain, we can return early
									tokens.splice(i, 1);
									selector = seed.length && toSelector(tokens);
									if (!selector) {
										push.apply(results, seed);
										return results;
									}

									break;
								}
							}
						}
					}

					// Compile and execute a filtering function if one is not provided
					// Provide `match` to avoid retokenization if we modified the selector above
					(compiled || compile(selector, match))(seed, context, !documentIsHTML, results, !context || rsibling.test(selector) && testContext(context.parentNode) || context);
					return results;
				};

				// One-time assignments

				// Sort stability
				support.sortStable = expando.split("").sort(sortOrder).join("") === expando;

				// Support: Chrome 14-35+
				// Always assume duplicates if they aren't passed to the comparison function
				support.detectDuplicates = !!hasDuplicate;

				// Initialize against the default document
				setDocument();

				// Support: Webkit<537.32 - Safari 6.0.3/Chrome 25 (fixed in Chrome 27)
				// Detached nodes confoundingly follow *each other*
				support.sortDetached = assert(function (div1) {
					// Should return 1, but returns 4 (following)
					return div1.compareDocumentPosition(document.createElement("div")) & 1;
				});

				// Support: IE<8
				// Prevent attribute/property "interpolation"
				// http://msdn.microsoft.com/en-us/library/ms536429%28VS.85%29.aspx
				if (!assert(function (div) {
					div.innerHTML = "<a href='#'></a>";
					return div.firstChild.getAttribute("href") === "#";
				})) {
					addHandle("type|href|height|width", function (elem, name, isXML) {
						if (!isXML) {
							return elem.getAttribute(name, name.toLowerCase() === "type" ? 1 : 2);
						}
					});
				}

				// Support: IE<9
				// Use defaultValue in place of getAttribute("value")
				if (!support.attributes || !assert(function (div) {
					div.innerHTML = "<input/>";
					div.firstChild.setAttribute("value", "");
					return div.firstChild.getAttribute("value") === "";
				})) {
					addHandle("value", function (elem, name, isXML) {
						if (!isXML && elem.nodeName.toLowerCase() === "input") {
							return elem.defaultValue;
						}
					});
				}

				// Support: IE<9
				// Use getAttributeNode to fetch booleans when getAttribute lies
				if (!assert(function (div) {
					return div.getAttribute("disabled") == null;
				})) {
					addHandle(booleans, function (elem, name, isXML) {
						var val;
						if (!isXML) {
							return elem[name] === true ? name.toLowerCase() : (val = elem.getAttributeNode(name)) && val.specified ? val.value : null;
						}
					});
				}

				return Sizzle;
			}(window);

			jQuery.find = Sizzle;
			jQuery.expr = Sizzle.selectors;
			jQuery.expr[":"] = jQuery.expr.pseudos;
			jQuery.uniqueSort = jQuery.unique = Sizzle.uniqueSort;
			jQuery.text = Sizzle.getText;
			jQuery.isXMLDoc = Sizzle.isXML;
			jQuery.contains = Sizzle.contains;

			var dir = function dir(elem, _dir, until) {
				var matched = [],
				    truncate = until !== undefined;

				while ((elem = elem[_dir]) && elem.nodeType !== 9) {
					if (elem.nodeType === 1) {
						if (truncate && jQuery(elem).is(until)) {
							break;
						}
						matched.push(elem);
					}
				}
				return matched;
			};

			var _siblings = function _siblings(n, elem) {
				var matched = [];

				for (; n; n = n.nextSibling) {
					if (n.nodeType === 1 && n !== elem) {
						matched.push(n);
					}
				}

				return matched;
			};

			var rneedsContext = jQuery.expr.match.needsContext;

			var rsingleTag = /^<([\w-]+)\s*\/?>(?:<\/\1>|)$/;

			var risSimple = /^.[^:#\[\.,]*$/;

			// Implement the identical functionality for filter and not
			function winnow(elements, qualifier, not) {
				if (jQuery.isFunction(qualifier)) {
					return jQuery.grep(elements, function (elem, i) {
						/* jshint -W018 */
						return !!qualifier.call(elem, i, elem) !== not;
					});
				}

				if (qualifier.nodeType) {
					return jQuery.grep(elements, function (elem) {
						return elem === qualifier !== not;
					});
				}

				if (typeof qualifier === "string") {
					if (risSimple.test(qualifier)) {
						return jQuery.filter(qualifier, elements, not);
					}

					qualifier = jQuery.filter(qualifier, elements);
				}

				return jQuery.grep(elements, function (elem) {
					return jQuery.inArray(elem, qualifier) > -1 !== not;
				});
			}

			jQuery.filter = function (expr, elems, not) {
				var elem = elems[0];

				if (not) {
					expr = ":not(" + expr + ")";
				}

				return elems.length === 1 && elem.nodeType === 1 ? jQuery.find.matchesSelector(elem, expr) ? [elem] : [] : jQuery.find.matches(expr, jQuery.grep(elems, function (elem) {
					return elem.nodeType === 1;
				}));
			};

			jQuery.fn.extend({
				find: function find(selector) {
					var i,
					    ret = [],
					    self = this,
					    len = self.length;

					if (typeof selector !== "string") {
						return this.pushStack(jQuery(selector).filter(function () {
							for (i = 0; i < len; i++) {
								if (jQuery.contains(self[i], this)) {
									return true;
								}
							}
						}));
					}

					for (i = 0; i < len; i++) {
						jQuery.find(selector, self[i], ret);
					}

					// Needed because $( selector, context ) becomes $( context ).find( selector )
					ret = this.pushStack(len > 1 ? jQuery.unique(ret) : ret);
					ret.selector = this.selector ? this.selector + " " + selector : selector;
					return ret;
				},
				filter: function filter(selector) {
					return this.pushStack(winnow(this, selector || [], false));
				},
				not: function not(selector) {
					return this.pushStack(winnow(this, selector || [], true));
				},
				is: function is(selector) {
					return !!winnow(this,

					// If this is a positional/relative selector, check membership in the returned set
					// so $("p:first").is("p:last") won't return true for a doc with two "p".
					typeof selector === "string" && rneedsContext.test(selector) ? jQuery(selector) : selector || [], false).length;
				}
			});

			// Initialize a jQuery object


			// A central reference to the root jQuery(document)
			var rootjQuery,


			// A simple way to check for HTML strings
			// Prioritize #id over <tag> to avoid XSS via location.hash (#9521)
			// Strict HTML recognition (#11290: must start with <)
			rquickExpr = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/,
			    init = jQuery.fn.init = function (selector, context, root) {
				var match, elem;

				// HANDLE: $(""), $(null), $(undefined), $(false)
				if (!selector) {
					return this;
				}

				// init accepts an alternate rootjQuery
				// so migrate can support jQuery.sub (gh-2101)
				root = root || rootjQuery;

				// Handle HTML strings
				if (typeof selector === "string") {
					if (selector.charAt(0) === "<" && selector.charAt(selector.length - 1) === ">" && selector.length >= 3) {

						// Assume that strings that start and end with <> are HTML and skip the regex check
						match = [null, selector, null];
					} else {
						match = rquickExpr.exec(selector);
					}

					// Match html or make sure no context is specified for #id
					if (match && (match[1] || !context)) {

						// HANDLE: $(html) -> $(array)
						if (match[1]) {
							context = context instanceof jQuery ? context[0] : context;

							// scripts is true for back-compat
							// Intentionally let the error be thrown if parseHTML is not present
							jQuery.merge(this, jQuery.parseHTML(match[1], context && context.nodeType ? context.ownerDocument || context : document, true));

							// HANDLE: $(html, props)
							if (rsingleTag.test(match[1]) && jQuery.isPlainObject(context)) {
								for (match in context) {

									// Properties of context are called as methods if possible
									if (jQuery.isFunction(this[match])) {
										this[match](context[match]);

										// ...and otherwise set as attributes
									} else {
										this.attr(match, context[match]);
									}
								}
							}

							return this;

							// HANDLE: $(#id)
						} else {
							elem = document.getElementById(match[2]);

							// Check parentNode to catch when Blackberry 4.6 returns
							// nodes that are no longer in the document #6963
							if (elem && elem.parentNode) {

								// Handle the case where IE and Opera return items
								// by name instead of ID
								if (elem.id !== match[2]) {
									return rootjQuery.find(selector);
								}

								// Otherwise, we inject the element directly into the jQuery object
								this.length = 1;
								this[0] = elem;
							}

							this.context = document;
							this.selector = selector;
							return this;
						}

						// HANDLE: $(expr, $(...))
					} else if (!context || context.jquery) {
						return (context || root).find(selector);

						// HANDLE: $(expr, context)
						// (which is just equivalent to: $(context).find(expr)
					} else {
						return this.constructor(context).find(selector);
					}

					// HANDLE: $(DOMElement)
				} else if (selector.nodeType) {
					this.context = this[0] = selector;
					this.length = 1;
					return this;

					// HANDLE: $(function)
					// Shortcut for document ready
				} else if (jQuery.isFunction(selector)) {
					return typeof root.ready !== "undefined" ? root.ready(selector) :

					// Execute immediately if ready is not present
					selector(jQuery);
				}

				if (selector.selector !== undefined) {
					this.selector = selector.selector;
					this.context = selector.context;
				}

				return jQuery.makeArray(selector, this);
			};

			// Give the init function the jQuery prototype for later instantiation
			init.prototype = jQuery.fn;

			// Initialize central reference
			rootjQuery = jQuery(document);

			var rparentsprev = /^(?:parents|prev(?:Until|All))/,


			// methods guaranteed to produce a unique set when starting from a unique set
			guaranteedUnique = {
				children: true,
				contents: true,
				next: true,
				prev: true
			};

			jQuery.fn.extend({
				has: function has(target) {
					var i,
					    targets = jQuery(target, this),
					    len = targets.length;

					return this.filter(function () {
						for (i = 0; i < len; i++) {
							if (jQuery.contains(this, targets[i])) {
								return true;
							}
						}
					});
				},

				closest: function closest(selectors, context) {
					var cur,
					    i = 0,
					    l = this.length,
					    matched = [],
					    pos = rneedsContext.test(selectors) || typeof selectors !== "string" ? jQuery(selectors, context || this.context) : 0;

					for (; i < l; i++) {
						for (cur = this[i]; cur && cur !== context; cur = cur.parentNode) {

							// Always skip document fragments
							if (cur.nodeType < 11 && (pos ? pos.index(cur) > -1 :

							// Don't pass non-elements to Sizzle
							cur.nodeType === 1 && jQuery.find.matchesSelector(cur, selectors))) {

								matched.push(cur);
								break;
							}
						}
					}

					return this.pushStack(matched.length > 1 ? jQuery.uniqueSort(matched) : matched);
				},

				// Determine the position of an element within
				// the matched set of elements
				index: function index(elem) {

					// No argument, return index in parent
					if (!elem) {
						return this[0] && this[0].parentNode ? this.first().prevAll().length : -1;
					}

					// index in selector
					if (typeof elem === "string") {
						return jQuery.inArray(this[0], jQuery(elem));
					}

					// Locate the position of the desired element
					return jQuery.inArray(

					// If it receives a jQuery object, the first element is used
					elem.jquery ? elem[0] : elem, this);
				},

				add: function add(selector, context) {
					return this.pushStack(jQuery.uniqueSort(jQuery.merge(this.get(), jQuery(selector, context))));
				},

				addBack: function addBack(selector) {
					return this.add(selector == null ? this.prevObject : this.prevObject.filter(selector));
				}
			});

			function sibling(cur, dir) {
				do {
					cur = cur[dir];
				} while (cur && cur.nodeType !== 1);

				return cur;
			}

			jQuery.each({
				parent: function parent(elem) {
					var parent = elem.parentNode;
					return parent && parent.nodeType !== 11 ? parent : null;
				},
				parents: function parents(elem) {
					return dir(elem, "parentNode");
				},
				parentsUntil: function parentsUntil(elem, i, until) {
					return dir(elem, "parentNode", until);
				},
				next: function next(elem) {
					return sibling(elem, "nextSibling");
				},
				prev: function prev(elem) {
					return sibling(elem, "previousSibling");
				},
				nextAll: function nextAll(elem) {
					return dir(elem, "nextSibling");
				},
				prevAll: function prevAll(elem) {
					return dir(elem, "previousSibling");
				},
				nextUntil: function nextUntil(elem, i, until) {
					return dir(elem, "nextSibling", until);
				},
				prevUntil: function prevUntil(elem, i, until) {
					return dir(elem, "previousSibling", until);
				},
				siblings: function siblings(elem) {
					return _siblings((elem.parentNode || {}).firstChild, elem);
				},
				children: function children(elem) {
					return _siblings(elem.firstChild);
				},
				contents: function contents(elem) {
					return jQuery.nodeName(elem, "iframe") ? elem.contentDocument || elem.contentWindow.document : jQuery.merge([], elem.childNodes);
				}
			}, function (name, fn) {
				jQuery.fn[name] = function (until, selector) {
					var ret = jQuery.map(this, fn, until);

					if (name.slice(-5) !== "Until") {
						selector = until;
					}

					if (selector && typeof selector === "string") {
						ret = jQuery.filter(selector, ret);
					}

					if (this.length > 1) {

						// Remove duplicates
						if (!guaranteedUnique[name]) {
							ret = jQuery.uniqueSort(ret);
						}

						// Reverse order for parents* and prev-derivatives
						if (rparentsprev.test(name)) {
							ret = ret.reverse();
						}
					}

					return this.pushStack(ret);
				};
			});
			var rnotwhite = /\S+/g;

			// Convert String-formatted options into Object-formatted ones
			function createOptions(options) {
				var object = {};
				jQuery.each(options.match(rnotwhite) || [], function (_, flag) {
					object[flag] = true;
				});
				return object;
			}

			/*
	   * Create a callback list using the following parameters:
	   *
	   *	options: an optional list of space-separated options that will change how
	   *			the callback list behaves or a more traditional option object
	   *
	   * By default a callback list will act like an event callback list and can be
	   * "fired" multiple times.
	   *
	   * Possible options:
	   *
	   *	once:			will ensure the callback list can only be fired once (like a Deferred)
	   *
	   *	memory:			will keep track of previous values and will call any callback added
	   *					after the list has been fired right away with the latest "memorized"
	   *					values (like a Deferred)
	   *
	   *	unique:			will ensure a callback can only be added once (no duplicate in the list)
	   *
	   *	stopOnFalse:	interrupt callings when a callback returns false
	   *
	   */
			jQuery.Callbacks = function (options) {

				// Convert options from String-formatted to Object-formatted if needed
				// (we check in cache first)
				options = typeof options === "string" ? createOptions(options) : jQuery.extend({}, options);

				var // Flag to know if list is currently firing
				firing,


				// Last fire value for non-forgettable lists
				memory,


				// Flag to know if list was already fired
				_fired,


				// Flag to prevent firing
				_locked,


				// Actual callback list
				list = [],


				// Queue of execution data for repeatable lists
				queue = [],


				// Index of currently firing callback (modified by add/remove as needed)
				firingIndex = -1,


				// Fire callbacks
				fire = function fire() {

					// Enforce single-firing
					_locked = options.once;

					// Execute callbacks for all pending executions,
					// respecting firingIndex overrides and runtime changes
					_fired = firing = true;
					for (; queue.length; firingIndex = -1) {
						memory = queue.shift();
						while (++firingIndex < list.length) {

							// Run callback and check for early termination
							if (list[firingIndex].apply(memory[0], memory[1]) === false && options.stopOnFalse) {

								// Jump to end and forget the data so .add doesn't re-fire
								firingIndex = list.length;
								memory = false;
							}
						}
					}

					// Forget the data if we're done with it
					if (!options.memory) {
						memory = false;
					}

					firing = false;

					// Clean up if we're done firing for good
					if (_locked) {

						// Keep an empty list if we have data for future add calls
						if (memory) {
							list = [];

							// Otherwise, this object is spent
						} else {
							list = "";
						}
					}
				},


				// Actual Callbacks object
				self = {

					// Add a callback or a collection of callbacks to the list
					add: function add() {
						if (list) {

							// If we have memory from a past run, we should fire after adding
							if (memory && !firing) {
								firingIndex = list.length - 1;
								queue.push(memory);
							}

							(function add(args) {
								jQuery.each(args, function (_, arg) {
									if (jQuery.isFunction(arg)) {
										if (!options.unique || !self.has(arg)) {
											list.push(arg);
										}
									} else if (arg && arg.length && jQuery.type(arg) !== "string") {

										// Inspect recursively
										add(arg);
									}
								});
							})(arguments);

							if (memory && !firing) {
								fire();
							}
						}
						return this;
					},

					// Remove a callback from the list
					remove: function remove() {
						jQuery.each(arguments, function (_, arg) {
							var index;
							while ((index = jQuery.inArray(arg, list, index)) > -1) {
								list.splice(index, 1);

								// Handle firing indexes
								if (index <= firingIndex) {
									firingIndex--;
								}
							}
						});
						return this;
					},

					// Check if a given callback is in the list.
					// If no argument is given, return whether or not list has callbacks attached.
					has: function has(fn) {
						return fn ? jQuery.inArray(fn, list) > -1 : list.length > 0;
					},

					// Remove all callbacks from the list
					empty: function empty() {
						if (list) {
							list = [];
						}
						return this;
					},

					// Disable .fire and .add
					// Abort any current/pending executions
					// Clear all callbacks and values
					disable: function disable() {
						_locked = queue = [];
						list = memory = "";
						return this;
					},
					disabled: function disabled() {
						return !list;
					},

					// Disable .fire
					// Also disable .add unless we have memory (since it would have no effect)
					// Abort any pending executions
					lock: function lock() {
						_locked = true;
						if (!memory) {
							self.disable();
						}
						return this;
					},
					locked: function locked() {
						return !!_locked;
					},

					// Call all callbacks with the given context and arguments
					fireWith: function fireWith(context, args) {
						if (!_locked) {
							args = args || [];
							args = [context, args.slice ? args.slice() : args];
							queue.push(args);
							if (!firing) {
								fire();
							}
						}
						return this;
					},

					// Call all the callbacks with the given arguments
					fire: function fire() {
						self.fireWith(this, arguments);
						return this;
					},

					// To know if the callbacks have already been called at least once
					fired: function fired() {
						return !!_fired;
					}
				};

				return self;
			};

			jQuery.extend({

				Deferred: function Deferred(func) {
					var tuples = [

					// action, add listener, listener list, final state
					["resolve", "done", jQuery.Callbacks("once memory"), "resolved"], ["reject", "fail", jQuery.Callbacks("once memory"), "rejected"], ["notify", "progress", jQuery.Callbacks("memory")]],
					    _state = "pending",
					    _promise = {
						state: function state() {
							return _state;
						},
						always: function always() {
							deferred.done(arguments).fail(arguments);
							return this;
						},
						then: function then() /* fnDone, fnFail, fnProgress */{
							var fns = arguments;
							return jQuery.Deferred(function (newDefer) {
								jQuery.each(tuples, function (i, tuple) {
									var fn = jQuery.isFunction(fns[i]) && fns[i];

									// deferred[ done | fail | progress ] for forwarding actions to newDefer
									deferred[tuple[1]](function () {
										var returned = fn && fn.apply(this, arguments);
										if (returned && jQuery.isFunction(returned.promise)) {
											returned.promise().progress(newDefer.notify).done(newDefer.resolve).fail(newDefer.reject);
										} else {
											newDefer[tuple[0] + "With"](this === _promise ? newDefer.promise() : this, fn ? [returned] : arguments);
										}
									});
								});
								fns = null;
							}).promise();
						},

						// Get a promise for this deferred
						// If obj is provided, the promise aspect is added to the object
						promise: function promise(obj) {
							return obj != null ? jQuery.extend(obj, _promise) : _promise;
						}
					},
					    deferred = {};

					// Keep pipe for back-compat
					_promise.pipe = _promise.then;

					// Add list-specific methods
					jQuery.each(tuples, function (i, tuple) {
						var list = tuple[2],
						    stateString = tuple[3];

						// promise[ done | fail | progress ] = list.add
						_promise[tuple[1]] = list.add;

						// Handle state
						if (stateString) {
							list.add(function () {

								// state = [ resolved | rejected ]
								_state = stateString;

								// [ reject_list | resolve_list ].disable; progress_list.lock
							}, tuples[i ^ 1][2].disable, tuples[2][2].lock);
						}

						// deferred[ resolve | reject | notify ]
						deferred[tuple[0]] = function () {
							deferred[tuple[0] + "With"](this === deferred ? _promise : this, arguments);
							return this;
						};
						deferred[tuple[0] + "With"] = list.fireWith;
					});

					// Make the deferred a promise
					_promise.promise(deferred);

					// Call given func if any
					if (func) {
						func.call(deferred, deferred);
					}

					// All done!
					return deferred;
				},

				// Deferred helper
				when: function when(subordinate /* , ..., subordinateN */) {
					var i = 0,
					    resolveValues = _slice.call(arguments),
					    length = resolveValues.length,


					// the count of uncompleted subordinates
					remaining = length !== 1 || subordinate && jQuery.isFunction(subordinate.promise) ? length : 0,


					// the master Deferred.
					// If resolveValues consist of only a single Deferred, just use that.
					deferred = remaining === 1 ? subordinate : jQuery.Deferred(),


					// Update function for both resolve and progress values
					updateFunc = function updateFunc(i, contexts, values) {
						return function (value) {
							contexts[i] = this;
							values[i] = arguments.length > 1 ? _slice.call(arguments) : value;
							if (values === progressValues) {
								deferred.notifyWith(contexts, values);
							} else if (! --remaining) {
								deferred.resolveWith(contexts, values);
							}
						};
					},
					    progressValues,
					    progressContexts,
					    resolveContexts;

					// add listeners to Deferred subordinates; treat others as resolved
					if (length > 1) {
						progressValues = new Array(length);
						progressContexts = new Array(length);
						resolveContexts = new Array(length);
						for (; i < length; i++) {
							if (resolveValues[i] && jQuery.isFunction(resolveValues[i].promise)) {
								resolveValues[i].promise().progress(updateFunc(i, progressContexts, progressValues)).done(updateFunc(i, resolveContexts, resolveValues)).fail(deferred.reject);
							} else {
								--remaining;
							}
						}
					}

					// if we're not waiting on anything, resolve the master
					if (!remaining) {
						deferred.resolveWith(resolveContexts, resolveValues);
					}

					return deferred.promise();
				}
			});

			// The deferred used on DOM ready
			var readyList;

			jQuery.fn.ready = function (fn) {

				// Add the callback
				jQuery.ready.promise().done(fn);

				return this;
			};

			jQuery.extend({

				// Is the DOM ready to be used? Set to true once it occurs.
				isReady: false,

				// A counter to track how many items to wait for before
				// the ready event fires. See #6781
				readyWait: 1,

				// Hold (or release) the ready event
				holdReady: function holdReady(hold) {
					if (hold) {
						jQuery.readyWait++;
					} else {
						jQuery.ready(true);
					}
				},

				// Handle when the DOM is ready
				ready: function ready(wait) {

					// Abort if there are pending holds or we're already ready
					if (wait === true ? --jQuery.readyWait : jQuery.isReady) {
						return;
					}

					// Remember that the DOM is ready
					jQuery.isReady = true;

					// If a normal DOM Ready event fired, decrement, and wait if need be
					if (wait !== true && --jQuery.readyWait > 0) {
						return;
					}

					// If there are functions bound, to execute
					readyList.resolveWith(document, [jQuery]);

					// Trigger any bound ready events
					if (jQuery.fn.triggerHandler) {
						jQuery(document).triggerHandler("ready");
						jQuery(document).off("ready");
					}
				}
			});

			/**
	   * Clean-up method for dom ready events
	   */
			function detach() {
				if (document.addEventListener) {
					document.removeEventListener("DOMContentLoaded", completed);
					window.removeEventListener("load", completed);
				} else {
					document.detachEvent("onreadystatechange", completed);
					window.detachEvent("onload", completed);
				}
			}

			/**
	   * The ready event handler and self cleanup method
	   */
			function completed() {

				// readyState === "complete" is good enough for us to call the dom ready in oldIE
				if (document.addEventListener || window.event.type === "load" || document.readyState === "complete") {

					detach();
					jQuery.ready();
				}
			}

			jQuery.ready.promise = function (obj) {
				if (!readyList) {

					readyList = jQuery.Deferred();

					// Catch cases where $(document).ready() is called
					// after the browser event has already occurred.
					// Support: IE6-10
					// Older IE sometimes signals "interactive" too soon
					if (document.readyState === "complete" || document.readyState !== "loading" && !document.documentElement.doScroll) {

						// Handle it asynchronously to allow scripts the opportunity to delay ready
						window.setTimeout(jQuery.ready);

						// Standards-based browsers support DOMContentLoaded
					} else if (document.addEventListener) {

						// Use the handy event callback
						document.addEventListener("DOMContentLoaded", completed);

						// A fallback to window.onload, that will always work
						window.addEventListener("load", completed);

						// If IE event model is used
					} else {

						// Ensure firing before onload, maybe late but safe also for iframes
						document.attachEvent("onreadystatechange", completed);

						// A fallback to window.onload, that will always work
						window.attachEvent("onload", completed);

						// If IE and not a frame
						// continually check to see if the document is ready
						var top = false;

						try {
							top = window.frameElement == null && document.documentElement;
						} catch (e) {}

						if (top && top.doScroll) {
							(function doScrollCheck() {
								if (!jQuery.isReady) {

									try {

										// Use the trick by Diego Perini
										// http://javascript.nwbox.com/IEContentLoaded/
										top.doScroll("left");
									} catch (e) {
										return window.setTimeout(doScrollCheck, 50);
									}

									// detach all dom ready events
									detach();

									// and execute any waiting functions
									jQuery.ready();
								}
							})();
						}
					}
				}
				return readyList.promise(obj);
			};

			// Kick off the DOM ready check even if the user does not
			jQuery.ready.promise();

			// Support: IE<9
			// Iteration over object's inherited properties before its own
			var i;
			for (i in jQuery(support)) {
				break;
			}
			support.ownFirst = i === "0";

			// Note: most support tests are defined in their respective modules.
			// false until the test is run
			support.inlineBlockNeedsLayout = false;

			// Execute ASAP in case we need to set body.style.zoom
			jQuery(function () {

				// Minified: var a,b,c,d
				var val, div, body, container;

				body = document.getElementsByTagName("body")[0];
				if (!body || !body.style) {

					// Return for frameset docs that don't have a body
					return;
				}

				// Setup
				div = document.createElement("div");
				container = document.createElement("div");
				container.style.cssText = "position:absolute;border:0;width:0;height:0;top:0;left:-9999px";
				body.appendChild(container).appendChild(div);

				if (typeof div.style.zoom !== "undefined") {

					// Support: IE<8
					// Check if natively block-level elements act like inline-block
					// elements when setting their display to 'inline' and giving
					// them layout
					div.style.cssText = "display:inline;margin:0;border:0;padding:1px;width:1px;zoom:1";

					support.inlineBlockNeedsLayout = val = div.offsetWidth === 3;
					if (val) {

						// Prevent IE 6 from affecting layout for positioned elements #11048
						// Prevent IE from shrinking the body in IE 7 mode #12869
						// Support: IE<8
						body.style.zoom = 1;
					}
				}

				body.removeChild(container);
			});

			(function () {
				var div = document.createElement("div");

				// Support: IE<9
				support.deleteExpando = true;
				try {
					delete div.test;
				} catch (e) {
					support.deleteExpando = false;
				}

				// Null elements to avoid leaks in IE.
				div = null;
			})();
			var acceptData = function acceptData(elem) {
				var noData = jQuery.noData[(elem.nodeName + " ").toLowerCase()],
				    nodeType = +elem.nodeType || 1;

				// Do not set data on non-element DOM nodes because it will not be cleared (#8335).
				return nodeType !== 1 && nodeType !== 9 ? false :

				// Nodes accept data unless otherwise specified; rejection can be conditional
				!noData || noData !== true && elem.getAttribute("classid") === noData;
			};

			var rbrace = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,
			    rmultiDash = /([A-Z])/g;

			function dataAttr(elem, key, data) {

				// If nothing was found internally, try to fetch any
				// data from the HTML5 data-* attribute
				if (data === undefined && elem.nodeType === 1) {

					var name = "data-" + key.replace(rmultiDash, "-$1").toLowerCase();

					data = elem.getAttribute(name);

					if (typeof data === "string") {
						try {
							data = data === "true" ? true : data === "false" ? false : data === "null" ? null :

							// Only convert to a number if it doesn't change the string
							+data + "" === data ? +data : rbrace.test(data) ? jQuery.parseJSON(data) : data;
						} catch (e) {}

						// Make sure we set the data so it isn't changed later
						jQuery.data(elem, key, data);
					} else {
						data = undefined;
					}
				}

				return data;
			}

			// checks a cache object for emptiness
			function isEmptyDataObject(obj) {
				var name;
				for (name in obj) {

					// if the public data object is empty, the private is still empty
					if (name === "data" && jQuery.isEmptyObject(obj[name])) {
						continue;
					}
					if (name !== "toJSON") {
						return false;
					}
				}

				return true;
			}

			function internalData(elem, name, data, pvt /* Internal Use Only */) {
				if (!acceptData(elem)) {
					return;
				}

				var ret,
				    thisCache,
				    internalKey = jQuery.expando,


				// We have to handle DOM nodes and JS objects differently because IE6-7
				// can't GC object references properly across the DOM-JS boundary
				isNode = elem.nodeType,


				// Only DOM nodes need the global jQuery cache; JS object data is
				// attached directly to the object so GC can occur automatically
				cache = isNode ? jQuery.cache : elem,


				// Only defining an ID for JS objects if its cache already exists allows
				// the code to shortcut on the same path as a DOM node with no cache
				id = isNode ? elem[internalKey] : elem[internalKey] && internalKey;

				// Avoid doing any more work than we need to when trying to get data on an
				// object that has no data at all
				if ((!id || !cache[id] || !pvt && !cache[id].data) && data === undefined && typeof name === "string") {
					return;
				}

				if (!id) {

					// Only DOM nodes need a new unique ID for each element since their data
					// ends up in the global cache
					if (isNode) {
						id = elem[internalKey] = deletedIds.pop() || jQuery.guid++;
					} else {
						id = internalKey;
					}
				}

				if (!cache[id]) {

					// Avoid exposing jQuery metadata on plain JS objects when the object
					// is serialized using JSON.stringify
					cache[id] = isNode ? {} : { toJSON: jQuery.noop };
				}

				// An object can be passed to jQuery.data instead of a key/value pair; this gets
				// shallow copied over onto the existing cache
				if ((typeof name === "undefined" ? "undefined" : _typeof(name)) === "object" || typeof name === "function") {
					if (pvt) {
						cache[id] = jQuery.extend(cache[id], name);
					} else {
						cache[id].data = jQuery.extend(cache[id].data, name);
					}
				}

				thisCache = cache[id];

				// jQuery data() is stored in a separate object inside the object's internal data
				// cache in order to avoid key collisions between internal data and user-defined
				// data.
				if (!pvt) {
					if (!thisCache.data) {
						thisCache.data = {};
					}

					thisCache = thisCache.data;
				}

				if (data !== undefined) {
					thisCache[jQuery.camelCase(name)] = data;
				}

				// Check for both converted-to-camel and non-converted data property names
				// If a data property was specified
				if (typeof name === "string") {

					// First Try to find as-is property data
					ret = thisCache[name];

					// Test for null|undefined property data
					if (ret == null) {

						// Try to find the camelCased property
						ret = thisCache[jQuery.camelCase(name)];
					}
				} else {
					ret = thisCache;
				}

				return ret;
			}

			function internalRemoveData(elem, name, pvt) {
				if (!acceptData(elem)) {
					return;
				}

				var thisCache,
				    i,
				    isNode = elem.nodeType,


				// See jQuery.data for more information
				cache = isNode ? jQuery.cache : elem,
				    id = isNode ? elem[jQuery.expando] : jQuery.expando;

				// If there is already no cache entry for this object, there is no
				// purpose in continuing
				if (!cache[id]) {
					return;
				}

				if (name) {

					thisCache = pvt ? cache[id] : cache[id].data;

					if (thisCache) {

						// Support array or space separated string names for data keys
						if (!jQuery.isArray(name)) {

							// try the string as a key before any manipulation
							if (name in thisCache) {
								name = [name];
							} else {

								// split the camel cased version by spaces unless a key with the spaces exists
								name = jQuery.camelCase(name);
								if (name in thisCache) {
									name = [name];
								} else {
									name = name.split(" ");
								}
							}
						} else {

							// If "name" is an array of keys...
							// When data is initially created, via ("key", "val") signature,
							// keys will be converted to camelCase.
							// Since there is no way to tell _how_ a key was added, remove
							// both plain key and camelCase key. #12786
							// This will only penalize the array argument path.
							name = name.concat(jQuery.map(name, jQuery.camelCase));
						}

						i = name.length;
						while (i--) {
							delete thisCache[name[i]];
						}

						// If there is no data left in the cache, we want to continue
						// and let the cache object itself get destroyed
						if (pvt ? !isEmptyDataObject(thisCache) : !jQuery.isEmptyObject(thisCache)) {
							return;
						}
					}
				}

				// See jQuery.data for more information
				if (!pvt) {
					delete cache[id].data;

					// Don't destroy the parent cache unless the internal data object
					// had been the only thing left in it
					if (!isEmptyDataObject(cache[id])) {
						return;
					}
				}

				// Destroy the cache
				if (isNode) {
					jQuery.cleanData([elem], true);

					// Use delete when supported for expandos or `cache` is not a window per isWindow (#10080)
					/* jshint eqeqeq: false */
				} else if (support.deleteExpando || cache != cache.window) {
					/* jshint eqeqeq: true */
					delete cache[id];

					// When all else fails, undefined
				} else {
					cache[id] = undefined;
				}
			}

			jQuery.extend({
				cache: {},

				// The following elements (space-suffixed to avoid Object.prototype collisions)
				// throw uncatchable exceptions if you attempt to set expando properties
				noData: {
					"applet ": true,
					"embed ": true,

					// ...but Flash objects (which have this classid) *can* handle expandos
					"object ": "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
				},

				hasData: function hasData(elem) {
					elem = elem.nodeType ? jQuery.cache[elem[jQuery.expando]] : elem[jQuery.expando];
					return !!elem && !isEmptyDataObject(elem);
				},

				data: function data(elem, name, _data) {
					return internalData(elem, name, _data);
				},

				removeData: function removeData(elem, name) {
					return internalRemoveData(elem, name);
				},

				// For internal use only.
				_data: function _data(elem, name, data) {
					return internalData(elem, name, data, true);
				},

				_removeData: function _removeData(elem, name) {
					return internalRemoveData(elem, name, true);
				}
			});

			jQuery.fn.extend({
				data: function data(key, value) {
					var i,
					    name,
					    data,
					    elem = this[0],
					    attrs = elem && elem.attributes;

					// Special expections of .data basically thwart jQuery.access,
					// so implement the relevant behavior ourselves

					// Gets all values
					if (key === undefined) {
						if (this.length) {
							data = jQuery.data(elem);

							if (elem.nodeType === 1 && !jQuery._data(elem, "parsedAttrs")) {
								i = attrs.length;
								while (i--) {

									// Support: IE11+
									// The attrs elements can be null (#14894)
									if (attrs[i]) {
										name = attrs[i].name;
										if (name.indexOf("data-") === 0) {
											name = jQuery.camelCase(name.slice(5));
											dataAttr(elem, name, data[name]);
										}
									}
								}
								jQuery._data(elem, "parsedAttrs", true);
							}
						}

						return data;
					}

					// Sets multiple values
					if ((typeof key === "undefined" ? "undefined" : _typeof(key)) === "object") {
						return this.each(function () {
							jQuery.data(this, key);
						});
					}

					return arguments.length > 1 ?

					// Sets one value
					this.each(function () {
						jQuery.data(this, key, value);
					}) :

					// Gets one value
					// Try to fetch any internally stored data first
					elem ? dataAttr(elem, key, jQuery.data(elem, key)) : undefined;
				},

				removeData: function removeData(key) {
					return this.each(function () {
						jQuery.removeData(this, key);
					});
				}
			});

			jQuery.extend({
				queue: function queue(elem, type, data) {
					var queue;

					if (elem) {
						type = (type || "fx") + "queue";
						queue = jQuery._data(elem, type);

						// Speed up dequeue by getting out quickly if this is just a lookup
						if (data) {
							if (!queue || jQuery.isArray(data)) {
								queue = jQuery._data(elem, type, jQuery.makeArray(data));
							} else {
								queue.push(data);
							}
						}
						return queue || [];
					}
				},

				dequeue: function dequeue(elem, type) {
					type = type || "fx";

					var queue = jQuery.queue(elem, type),
					    startLength = queue.length,
					    fn = queue.shift(),
					    hooks = jQuery._queueHooks(elem, type),
					    next = function next() {
						jQuery.dequeue(elem, type);
					};

					// If the fx queue is dequeued, always remove the progress sentinel
					if (fn === "inprogress") {
						fn = queue.shift();
						startLength--;
					}

					if (fn) {

						// Add a progress sentinel to prevent the fx queue from being
						// automatically dequeued
						if (type === "fx") {
							queue.unshift("inprogress");
						}

						// clear up the last queue stop function
						delete hooks.stop;
						fn.call(elem, next, hooks);
					}

					if (!startLength && hooks) {
						hooks.empty.fire();
					}
				},

				// not intended for public consumption - generates a queueHooks object,
				// or returns the current one
				_queueHooks: function _queueHooks(elem, type) {
					var key = type + "queueHooks";
					return jQuery._data(elem, key) || jQuery._data(elem, key, {
						empty: jQuery.Callbacks("once memory").add(function () {
							jQuery._removeData(elem, type + "queue");
							jQuery._removeData(elem, key);
						})
					});
				}
			});

			jQuery.fn.extend({
				queue: function queue(type, data) {
					var setter = 2;

					if (typeof type !== "string") {
						data = type;
						type = "fx";
						setter--;
					}

					if (arguments.length < setter) {
						return jQuery.queue(this[0], type);
					}

					return data === undefined ? this : this.each(function () {
						var queue = jQuery.queue(this, type, data);

						// ensure a hooks for this queue
						jQuery._queueHooks(this, type);

						if (type === "fx" && queue[0] !== "inprogress") {
							jQuery.dequeue(this, type);
						}
					});
				},
				dequeue: function dequeue(type) {
					return this.each(function () {
						jQuery.dequeue(this, type);
					});
				},
				clearQueue: function clearQueue(type) {
					return this.queue(type || "fx", []);
				},

				// Get a promise resolved when queues of a certain type
				// are emptied (fx is the type by default)
				promise: function promise(type, obj) {
					var tmp,
					    count = 1,
					    defer = jQuery.Deferred(),
					    elements = this,
					    i = this.length,
					    resolve = function resolve() {
						if (! --count) {
							defer.resolveWith(elements, [elements]);
						}
					};

					if (typeof type !== "string") {
						obj = type;
						type = undefined;
					}
					type = type || "fx";

					while (i--) {
						tmp = jQuery._data(elements[i], type + "queueHooks");
						if (tmp && tmp.empty) {
							count++;
							tmp.empty.add(resolve);
						}
					}
					resolve();
					return defer.promise(obj);
				}
			});

			(function () {
				var shrinkWrapBlocksVal;

				support.shrinkWrapBlocks = function () {
					if (shrinkWrapBlocksVal != null) {
						return shrinkWrapBlocksVal;
					}

					// Will be changed later if needed.
					shrinkWrapBlocksVal = false;

					// Minified: var b,c,d
					var div, body, container;

					body = document.getElementsByTagName("body")[0];
					if (!body || !body.style) {

						// Test fired too early or in an unsupported environment, exit.
						return;
					}

					// Setup
					div = document.createElement("div");
					container = document.createElement("div");
					container.style.cssText = "position:absolute;border:0;width:0;height:0;top:0;left:-9999px";
					body.appendChild(container).appendChild(div);

					// Support: IE6
					// Check if elements with layout shrink-wrap their children
					if (typeof div.style.zoom !== "undefined") {

						// Reset CSS: box-sizing; display; margin; border
						div.style.cssText =

						// Support: Firefox<29, Android 2.3
						// Vendor-prefix box-sizing
						"-webkit-box-sizing:content-box;-moz-box-sizing:content-box;" + "box-sizing:content-box;display:block;margin:0;border:0;" + "padding:1px;width:1px;zoom:1";
						div.appendChild(document.createElement("div")).style.width = "5px";
						shrinkWrapBlocksVal = div.offsetWidth !== 3;
					}

					body.removeChild(container);

					return shrinkWrapBlocksVal;
				};
			})();
			var pnum = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source;

			var rcssNum = new RegExp("^(?:([+-])=|)(" + pnum + ")([a-z%]*)$", "i");

			var cssExpand = ["Top", "Right", "Bottom", "Left"];

			var isHidden = function isHidden(elem, el) {

				// isHidden might be called from jQuery#filter function;
				// in that case, element will be second argument
				elem = el || elem;
				return jQuery.css(elem, "display") === "none" || !jQuery.contains(elem.ownerDocument, elem);
			};

			function adjustCSS(elem, prop, valueParts, tween) {
				var adjusted,
				    scale = 1,
				    maxIterations = 20,
				    currentValue = tween ? function () {
					return tween.cur();
				} : function () {
					return jQuery.css(elem, prop, "");
				},
				    initial = currentValue(),
				    unit = valueParts && valueParts[3] || (jQuery.cssNumber[prop] ? "" : "px"),


				// Starting value computation is required for potential unit mismatches
				initialInUnit = (jQuery.cssNumber[prop] || unit !== "px" && +initial) && rcssNum.exec(jQuery.css(elem, prop));

				if (initialInUnit && initialInUnit[3] !== unit) {

					// Trust units reported by jQuery.css
					unit = unit || initialInUnit[3];

					// Make sure we update the tween properties later on
					valueParts = valueParts || [];

					// Iteratively approximate from a nonzero starting point
					initialInUnit = +initial || 1;

					do {

						// If previous iteration zeroed out, double until we get *something*.
						// Use string for doubling so we don't accidentally see scale as unchanged below
						scale = scale || ".5";

						// Adjust and apply
						initialInUnit = initialInUnit / scale;
						jQuery.style(elem, prop, initialInUnit + unit);

						// Update scale, tolerating zero or NaN from tween.cur()
						// Break the loop if scale is unchanged or perfect, or if we've just had enough.
					} while (scale !== (scale = currentValue() / initial) && scale !== 1 && --maxIterations);
				}

				if (valueParts) {
					initialInUnit = +initialInUnit || +initial || 0;

					// Apply relative offset (+=/-=) if specified
					adjusted = valueParts[1] ? initialInUnit + (valueParts[1] + 1) * valueParts[2] : +valueParts[2];
					if (tween) {
						tween.unit = unit;
						tween.start = initialInUnit;
						tween.end = adjusted;
					}
				}
				return adjusted;
			}

			// Multifunctional method to get and set values of a collection
			// The value/s can optionally be executed if it's a function
			var access = function access(elems, fn, key, value, chainable, emptyGet, raw) {
				var i = 0,
				    length = elems.length,
				    bulk = key == null;

				// Sets many values
				if (jQuery.type(key) === "object") {
					chainable = true;
					for (i in key) {
						access(elems, fn, i, key[i], true, emptyGet, raw);
					}

					// Sets one value
				} else if (value !== undefined) {
					chainable = true;

					if (!jQuery.isFunction(value)) {
						raw = true;
					}

					if (bulk) {

						// Bulk operations run against the entire set
						if (raw) {
							fn.call(elems, value);
							fn = null;

							// ...except when executing function values
						} else {
							bulk = fn;
							fn = function fn(elem, key, value) {
								return bulk.call(jQuery(elem), value);
							};
						}
					}

					if (fn) {
						for (; i < length; i++) {
							fn(elems[i], key, raw ? value : value.call(elems[i], i, fn(elems[i], key)));
						}
					}
				}

				return chainable ? elems :

				// Gets
				bulk ? fn.call(elems) : length ? fn(elems[0], key) : emptyGet;
			};
			var rcheckableType = /^(?:checkbox|radio)$/i;

			var rtagName = /<([\w:-]+)/;

			var rscriptType = /^$|\/(?:java|ecma)script/i;

			var rleadingWhitespace = /^\s+/;

			var nodeNames = "abbr|article|aside|audio|bdi|canvas|data|datalist|" + "details|dialog|figcaption|figure|footer|header|hgroup|main|" + "mark|meter|nav|output|picture|progress|section|summary|template|time|video";

			function createSafeFragment(document) {
				var list = nodeNames.split("|"),
				    safeFrag = document.createDocumentFragment();

				if (safeFrag.createElement) {
					while (list.length) {
						safeFrag.createElement(list.pop());
					}
				}
				return safeFrag;
			}

			(function () {
				var div = document.createElement("div"),
				    fragment = document.createDocumentFragment(),
				    input = document.createElement("input");

				// Setup
				div.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>";

				// IE strips leading whitespace when .innerHTML is used
				support.leadingWhitespace = div.firstChild.nodeType === 3;

				// Make sure that tbody elements aren't automatically inserted
				// IE will insert them into empty tables
				support.tbody = !div.getElementsByTagName("tbody").length;

				// Make sure that link elements get serialized correctly by innerHTML
				// This requires a wrapper element in IE
				support.htmlSerialize = !!div.getElementsByTagName("link").length;

				// Makes sure cloning an html5 element does not cause problems
				// Where outerHTML is undefined, this still works
				support.html5Clone = document.createElement("nav").cloneNode(true).outerHTML !== "<:nav></:nav>";

				// Check if a disconnected checkbox will retain its checked
				// value of true after appended to the DOM (IE6/7)
				input.type = "checkbox";
				input.checked = true;
				fragment.appendChild(input);
				support.appendChecked = input.checked;

				// Make sure textarea (and checkbox) defaultValue is properly cloned
				// Support: IE6-IE11+
				div.innerHTML = "<textarea>x</textarea>";
				support.noCloneChecked = !!div.cloneNode(true).lastChild.defaultValue;

				// #11217 - WebKit loses check when the name is after the checked attribute
				fragment.appendChild(div);

				// Support: Windows Web Apps (WWA)
				// `name` and `type` must use .setAttribute for WWA (#14901)
				input = document.createElement("input");
				input.setAttribute("type", "radio");
				input.setAttribute("checked", "checked");
				input.setAttribute("name", "t");

				div.appendChild(input);

				// Support: Safari 5.1, iOS 5.1, Android 4.x, Android 2.3
				// old WebKit doesn't clone checked state correctly in fragments
				support.checkClone = div.cloneNode(true).cloneNode(true).lastChild.checked;

				// Support: IE<9
				// Cloned elements keep attachEvent handlers, we use addEventListener on IE9+
				support.noCloneEvent = !!div.addEventListener;

				// Support: IE<9
				// Since attributes and properties are the same in IE,
				// cleanData must set properties to undefined rather than use removeAttribute
				div[jQuery.expando] = 1;
				support.attributes = !div.getAttribute(jQuery.expando);
			})();

			// We have to close these tags to support XHTML (#13200)
			var wrapMap = {
				option: [1, "<select multiple='multiple'>", "</select>"],
				legend: [1, "<fieldset>", "</fieldset>"],
				area: [1, "<map>", "</map>"],

				// Support: IE8
				param: [1, "<object>", "</object>"],
				thead: [1, "<table>", "</table>"],
				tr: [2, "<table><tbody>", "</tbody></table>"],
				col: [2, "<table><tbody></tbody><colgroup>", "</colgroup></table>"],
				td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],

				// IE6-8 can't serialize link, script, style, or any html5 (NoScope) tags,
				// unless wrapped in a div with non-breaking characters in front of it.
				_default: support.htmlSerialize ? [0, "", ""] : [1, "X<div>", "</div>"]
			};

			// Support: IE8-IE9
			wrapMap.optgroup = wrapMap.option;

			wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
			wrapMap.th = wrapMap.td;

			function getAll(context, tag) {
				var elems,
				    elem,
				    i = 0,
				    found = typeof context.getElementsByTagName !== "undefined" ? context.getElementsByTagName(tag || "*") : typeof context.querySelectorAll !== "undefined" ? context.querySelectorAll(tag || "*") : undefined;

				if (!found) {
					for (found = [], elems = context.childNodes || context; (elem = elems[i]) != null; i++) {
						if (!tag || jQuery.nodeName(elem, tag)) {
							found.push(elem);
						} else {
							jQuery.merge(found, getAll(elem, tag));
						}
					}
				}

				return tag === undefined || tag && jQuery.nodeName(context, tag) ? jQuery.merge([context], found) : found;
			}

			// Mark scripts as having already been evaluated
			function setGlobalEval(elems, refElements) {
				var elem,
				    i = 0;
				for (; (elem = elems[i]) != null; i++) {
					jQuery._data(elem, "globalEval", !refElements || jQuery._data(refElements[i], "globalEval"));
				}
			}

			var rhtml = /<|&#?\w+;/,
			    rtbody = /<tbody/i;

			function fixDefaultChecked(elem) {
				if (rcheckableType.test(elem.type)) {
					elem.defaultChecked = elem.checked;
				}
			}

			function buildFragment(elems, context, scripts, selection, ignored) {
				var j,
				    elem,
				    contains,
				    tmp,
				    tag,
				    tbody,
				    wrap,
				    l = elems.length,


				// Ensure a safe fragment
				safe = createSafeFragment(context),
				    nodes = [],
				    i = 0;

				for (; i < l; i++) {
					elem = elems[i];

					if (elem || elem === 0) {

						// Add nodes directly
						if (jQuery.type(elem) === "object") {
							jQuery.merge(nodes, elem.nodeType ? [elem] : elem);

							// Convert non-html into a text node
						} else if (!rhtml.test(elem)) {
							nodes.push(context.createTextNode(elem));

							// Convert html into DOM nodes
						} else {
							tmp = tmp || safe.appendChild(context.createElement("div"));

							// Deserialize a standard representation
							tag = (rtagName.exec(elem) || ["", ""])[1].toLowerCase();
							wrap = wrapMap[tag] || wrapMap._default;

							tmp.innerHTML = wrap[1] + jQuery.htmlPrefilter(elem) + wrap[2];

							// Descend through wrappers to the right content
							j = wrap[0];
							while (j--) {
								tmp = tmp.lastChild;
							}

							// Manually add leading whitespace removed by IE
							if (!support.leadingWhitespace && rleadingWhitespace.test(elem)) {
								nodes.push(context.createTextNode(rleadingWhitespace.exec(elem)[0]));
							}

							// Remove IE's autoinserted <tbody> from table fragments
							if (!support.tbody) {

								// String was a <table>, *may* have spurious <tbody>
								elem = tag === "table" && !rtbody.test(elem) ? tmp.firstChild :

								// String was a bare <thead> or <tfoot>
								wrap[1] === "<table>" && !rtbody.test(elem) ? tmp : 0;

								j = elem && elem.childNodes.length;
								while (j--) {
									if (jQuery.nodeName(tbody = elem.childNodes[j], "tbody") && !tbody.childNodes.length) {

										elem.removeChild(tbody);
									}
								}
							}

							jQuery.merge(nodes, tmp.childNodes);

							// Fix #12392 for WebKit and IE > 9
							tmp.textContent = "";

							// Fix #12392 for oldIE
							while (tmp.firstChild) {
								tmp.removeChild(tmp.firstChild);
							}

							// Remember the top-level container for proper cleanup
							tmp = safe.lastChild;
						}
					}
				}

				// Fix #11356: Clear elements from fragment
				if (tmp) {
					safe.removeChild(tmp);
				}

				// Reset defaultChecked for any radios and checkboxes
				// about to be appended to the DOM in IE 6/7 (#8060)
				if (!support.appendChecked) {
					jQuery.grep(getAll(nodes, "input"), fixDefaultChecked);
				}

				i = 0;
				while (elem = nodes[i++]) {

					// Skip elements already in the context collection (trac-4087)
					if (selection && jQuery.inArray(elem, selection) > -1) {
						if (ignored) {
							ignored.push(elem);
						}

						continue;
					}

					contains = jQuery.contains(elem.ownerDocument, elem);

					// Append to fragment
					tmp = getAll(safe.appendChild(elem), "script");

					// Preserve script evaluation history
					if (contains) {
						setGlobalEval(tmp);
					}

					// Capture executables
					if (scripts) {
						j = 0;
						while (elem = tmp[j++]) {
							if (rscriptType.test(elem.type || "")) {
								scripts.push(elem);
							}
						}
					}
				}

				tmp = null;

				return safe;
			}

			(function () {
				var i,
				    eventName,
				    div = document.createElement("div");

				// Support: IE<9 (lack submit/change bubble), Firefox (lack focus(in | out) events)
				for (i in { submit: true, change: true, focusin: true }) {
					eventName = "on" + i;

					if (!(support[i] = eventName in window)) {

						// Beware of CSP restrictions (https://developer.mozilla.org/en/Security/CSP)
						div.setAttribute(eventName, "t");
						support[i] = div.attributes[eventName].expando === false;
					}
				}

				// Null elements to avoid leaks in IE.
				div = null;
			})();

			var rformElems = /^(?:input|select|textarea)$/i,
			    rkeyEvent = /^key/,
			    rmouseEvent = /^(?:mouse|pointer|contextmenu|drag|drop)|click/,
			    rfocusMorph = /^(?:focusinfocus|focusoutblur)$/,
			    rtypenamespace = /^([^.]*)(?:\.(.+)|)/;

			function returnTrue() {
				return true;
			}

			function returnFalse() {
				return false;
			}

			// Support: IE9
			// See #13393 for more info
			function safeActiveElement() {
				try {
					return document.activeElement;
				} catch (err) {}
			}

			function _on(elem, types, selector, data, fn, one) {
				var origFn, type;

				// Types can be a map of types/handlers
				if ((typeof types === "undefined" ? "undefined" : _typeof(types)) === "object") {

					// ( types-Object, selector, data )
					if (typeof selector !== "string") {

						// ( types-Object, data )
						data = data || selector;
						selector = undefined;
					}
					for (type in types) {
						_on(elem, type, selector, data, types[type], one);
					}
					return elem;
				}

				if (data == null && fn == null) {

					// ( types, fn )
					fn = selector;
					data = selector = undefined;
				} else if (fn == null) {
					if (typeof selector === "string") {

						// ( types, selector, fn )
						fn = data;
						data = undefined;
					} else {

						// ( types, data, fn )
						fn = data;
						data = selector;
						selector = undefined;
					}
				}
				if (fn === false) {
					fn = returnFalse;
				} else if (!fn) {
					return elem;
				}

				if (one === 1) {
					origFn = fn;
					fn = function fn(event) {

						// Can use an empty set, since event contains the info
						jQuery().off(event);
						return origFn.apply(this, arguments);
					};

					// Use same guid so caller can remove using origFn
					fn.guid = origFn.guid || (origFn.guid = jQuery.guid++);
				}
				return elem.each(function () {
					jQuery.event.add(this, types, fn, data, selector);
				});
			}

			/*
	   * Helper functions for managing events -- not part of the public interface.
	   * Props to Dean Edwards' addEvent library for many of the ideas.
	   */
			jQuery.event = {

				global: {},

				add: function add(elem, types, handler, data, selector) {
					var tmp,
					    events,
					    t,
					    handleObjIn,
					    special,
					    eventHandle,
					    handleObj,
					    handlers,
					    type,
					    namespaces,
					    origType,
					    elemData = jQuery._data(elem);

					// Don't attach events to noData or text/comment nodes (but allow plain objects)
					if (!elemData) {
						return;
					}

					// Caller can pass in an object of custom data in lieu of the handler
					if (handler.handler) {
						handleObjIn = handler;
						handler = handleObjIn.handler;
						selector = handleObjIn.selector;
					}

					// Make sure that the handler has a unique ID, used to find/remove it later
					if (!handler.guid) {
						handler.guid = jQuery.guid++;
					}

					// Init the element's event structure and main handler, if this is the first
					if (!(events = elemData.events)) {
						events = elemData.events = {};
					}
					if (!(eventHandle = elemData.handle)) {
						eventHandle = elemData.handle = function (e) {

							// Discard the second event of a jQuery.event.trigger() and
							// when an event is called after a page has unloaded
							return typeof jQuery !== "undefined" && (!e || jQuery.event.triggered !== e.type) ? jQuery.event.dispatch.apply(eventHandle.elem, arguments) : undefined;
						};

						// Add elem as a property of the handle fn to prevent a memory leak
						// with IE non-native events
						eventHandle.elem = elem;
					}

					// Handle multiple events separated by a space
					types = (types || "").match(rnotwhite) || [""];
					t = types.length;
					while (t--) {
						tmp = rtypenamespace.exec(types[t]) || [];
						type = origType = tmp[1];
						namespaces = (tmp[2] || "").split(".").sort();

						// There *must* be a type, no attaching namespace-only handlers
						if (!type) {
							continue;
						}

						// If event changes its type, use the special event handlers for the changed type
						special = jQuery.event.special[type] || {};

						// If selector defined, determine special event api type, otherwise given type
						type = (selector ? special.delegateType : special.bindType) || type;

						// Update special based on newly reset type
						special = jQuery.event.special[type] || {};

						// handleObj is passed to all event handlers
						handleObj = jQuery.extend({
							type: type,
							origType: origType,
							data: data,
							handler: handler,
							guid: handler.guid,
							selector: selector,
							needsContext: selector && jQuery.expr.match.needsContext.test(selector),
							namespace: namespaces.join(".")
						}, handleObjIn);

						// Init the event handler queue if we're the first
						if (!(handlers = events[type])) {
							handlers = events[type] = [];
							handlers.delegateCount = 0;

							// Only use addEventListener/attachEvent if the special events handler returns false
							if (!special.setup || special.setup.call(elem, data, namespaces, eventHandle) === false) {

								// Bind the global event handler to the element
								if (elem.addEventListener) {
									elem.addEventListener(type, eventHandle, false);
								} else if (elem.attachEvent) {
									elem.attachEvent("on" + type, eventHandle);
								}
							}
						}

						if (special.add) {
							special.add.call(elem, handleObj);

							if (!handleObj.handler.guid) {
								handleObj.handler.guid = handler.guid;
							}
						}

						// Add to the element's handler list, delegates in front
						if (selector) {
							handlers.splice(handlers.delegateCount++, 0, handleObj);
						} else {
							handlers.push(handleObj);
						}

						// Keep track of which events have ever been used, for event optimization
						jQuery.event.global[type] = true;
					}

					// Nullify elem to prevent memory leaks in IE
					elem = null;
				},

				// Detach an event or set of events from an element
				remove: function remove(elem, types, handler, selector, mappedTypes) {
					var j,
					    handleObj,
					    tmp,
					    origCount,
					    t,
					    events,
					    special,
					    handlers,
					    type,
					    namespaces,
					    origType,
					    elemData = jQuery.hasData(elem) && jQuery._data(elem);

					if (!elemData || !(events = elemData.events)) {
						return;
					}

					// Once for each type.namespace in types; type may be omitted
					types = (types || "").match(rnotwhite) || [""];
					t = types.length;
					while (t--) {
						tmp = rtypenamespace.exec(types[t]) || [];
						type = origType = tmp[1];
						namespaces = (tmp[2] || "").split(".").sort();

						// Unbind all events (on this namespace, if provided) for the element
						if (!type) {
							for (type in events) {
								jQuery.event.remove(elem, type + types[t], handler, selector, true);
							}
							continue;
						}

						special = jQuery.event.special[type] || {};
						type = (selector ? special.delegateType : special.bindType) || type;
						handlers = events[type] || [];
						tmp = tmp[2] && new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)");

						// Remove matching events
						origCount = j = handlers.length;
						while (j--) {
							handleObj = handlers[j];

							if ((mappedTypes || origType === handleObj.origType) && (!handler || handler.guid === handleObj.guid) && (!tmp || tmp.test(handleObj.namespace)) && (!selector || selector === handleObj.selector || selector === "**" && handleObj.selector)) {
								handlers.splice(j, 1);

								if (handleObj.selector) {
									handlers.delegateCount--;
								}
								if (special.remove) {
									special.remove.call(elem, handleObj);
								}
							}
						}

						// Remove generic event handler if we removed something and no more handlers exist
						// (avoids potential for endless recursion during removal of special event handlers)
						if (origCount && !handlers.length) {
							if (!special.teardown || special.teardown.call(elem, namespaces, elemData.handle) === false) {

								jQuery.removeEvent(elem, type, elemData.handle);
							}

							delete events[type];
						}
					}

					// Remove the expando if it's no longer used
					if (jQuery.isEmptyObject(events)) {
						delete elemData.handle;

						// removeData also checks for emptiness and clears the expando if empty
						// so use it instead of delete
						jQuery._removeData(elem, "events");
					}
				},

				trigger: function trigger(event, data, elem, onlyHandlers) {
					var handle,
					    ontype,
					    cur,
					    bubbleType,
					    special,
					    tmp,
					    i,
					    eventPath = [elem || document],
					    type = hasOwn.call(event, "type") ? event.type : event,
					    namespaces = hasOwn.call(event, "namespace") ? event.namespace.split(".") : [];

					cur = tmp = elem = elem || document;

					// Don't do events on text and comment nodes
					if (elem.nodeType === 3 || elem.nodeType === 8) {
						return;
					}

					// focus/blur morphs to focusin/out; ensure we're not firing them right now
					if (rfocusMorph.test(type + jQuery.event.triggered)) {
						return;
					}

					if (type.indexOf(".") > -1) {

						// Namespaced trigger; create a regexp to match event type in handle()
						namespaces = type.split(".");
						type = namespaces.shift();
						namespaces.sort();
					}
					ontype = type.indexOf(":") < 0 && "on" + type;

					// Caller can pass in a jQuery.Event object, Object, or just an event type string
					event = event[jQuery.expando] ? event : new jQuery.Event(type, (typeof event === "undefined" ? "undefined" : _typeof(event)) === "object" && event);

					// Trigger bitmask: & 1 for native handlers; & 2 for jQuery (always true)
					event.isTrigger = onlyHandlers ? 2 : 3;
					event.namespace = namespaces.join(".");
					event.rnamespace = event.namespace ? new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)") : null;

					// Clean up the event in case it is being reused
					event.result = undefined;
					if (!event.target) {
						event.target = elem;
					}

					// Clone any incoming data and prepend the event, creating the handler arg list
					data = data == null ? [event] : jQuery.makeArray(data, [event]);

					// Allow special events to draw outside the lines
					special = jQuery.event.special[type] || {};
					if (!onlyHandlers && special.trigger && special.trigger.apply(elem, data) === false) {
						return;
					}

					// Determine event propagation path in advance, per W3C events spec (#9951)
					// Bubble up to document, then to window; watch for a global ownerDocument var (#9724)
					if (!onlyHandlers && !special.noBubble && !jQuery.isWindow(elem)) {

						bubbleType = special.delegateType || type;
						if (!rfocusMorph.test(bubbleType + type)) {
							cur = cur.parentNode;
						}
						for (; cur; cur = cur.parentNode) {
							eventPath.push(cur);
							tmp = cur;
						}

						// Only add window if we got to document (e.g., not plain obj or detached DOM)
						if (tmp === (elem.ownerDocument || document)) {
							eventPath.push(tmp.defaultView || tmp.parentWindow || window);
						}
					}

					// Fire handlers on the event path
					i = 0;
					while ((cur = eventPath[i++]) && !event.isPropagationStopped()) {

						event.type = i > 1 ? bubbleType : special.bindType || type;

						// jQuery handler
						handle = (jQuery._data(cur, "events") || {})[event.type] && jQuery._data(cur, "handle");

						if (handle) {
							handle.apply(cur, data);
						}

						// Native handler
						handle = ontype && cur[ontype];
						if (handle && handle.apply && acceptData(cur)) {
							event.result = handle.apply(cur, data);
							if (event.result === false) {
								event.preventDefault();
							}
						}
					}
					event.type = type;

					// If nobody prevented the default action, do it now
					if (!onlyHandlers && !event.isDefaultPrevented()) {

						if ((!special._default || special._default.apply(eventPath.pop(), data) === false) && acceptData(elem)) {

							// Call a native DOM method on the target with the same name name as the event.
							// Can't use an .isFunction() check here because IE6/7 fails that test.
							// Don't do default actions on window, that's where global variables be (#6170)
							if (ontype && elem[type] && !jQuery.isWindow(elem)) {

								// Don't re-trigger an onFOO event when we call its FOO() method
								tmp = elem[ontype];

								if (tmp) {
									elem[ontype] = null;
								}

								// Prevent re-triggering of the same event, since we already bubbled it above
								jQuery.event.triggered = type;
								try {
									elem[type]();
								} catch (e) {

									// IE<9 dies on focus/blur to hidden element (#1486,#12518)
									// only reproducible on winXP IE8 native, not IE9 in IE8 mode
								}
								jQuery.event.triggered = undefined;

								if (tmp) {
									elem[ontype] = tmp;
								}
							}
						}
					}

					return event.result;
				},

				dispatch: function dispatch(event) {

					// Make a writable jQuery.Event from the native event object
					event = jQuery.event.fix(event);

					var i,
					    j,
					    ret,
					    matched,
					    handleObj,
					    handlerQueue = [],
					    args = _slice.call(arguments),
					    handlers = (jQuery._data(this, "events") || {})[event.type] || [],
					    special = jQuery.event.special[event.type] || {};

					// Use the fix-ed jQuery.Event rather than the (read-only) native event
					args[0] = event;
					event.delegateTarget = this;

					// Call the preDispatch hook for the mapped type, and let it bail if desired
					if (special.preDispatch && special.preDispatch.call(this, event) === false) {
						return;
					}

					// Determine handlers
					handlerQueue = jQuery.event.handlers.call(this, event, handlers);

					// Run delegates first; they may want to stop propagation beneath us
					i = 0;
					while ((matched = handlerQueue[i++]) && !event.isPropagationStopped()) {
						event.currentTarget = matched.elem;

						j = 0;
						while ((handleObj = matched.handlers[j++]) && !event.isImmediatePropagationStopped()) {

							// Triggered event must either 1) have no namespace, or 2) have namespace(s)
							// a subset or equal to those in the bound event (both can have no namespace).
							if (!event.rnamespace || event.rnamespace.test(handleObj.namespace)) {

								event.handleObj = handleObj;
								event.data = handleObj.data;

								ret = ((jQuery.event.special[handleObj.origType] || {}).handle || handleObj.handler).apply(matched.elem, args);

								if (ret !== undefined) {
									if ((event.result = ret) === false) {
										event.preventDefault();
										event.stopPropagation();
									}
								}
							}
						}
					}

					// Call the postDispatch hook for the mapped type
					if (special.postDispatch) {
						special.postDispatch.call(this, event);
					}

					return event.result;
				},

				handlers: function handlers(event, _handlers) {
					var i,
					    matches,
					    sel,
					    handleObj,
					    handlerQueue = [],
					    delegateCount = _handlers.delegateCount,
					    cur = event.target;

					// Support (at least): Chrome, IE9
					// Find delegate handlers
					// Black-hole SVG <use> instance trees (#13180)
					//
					// Support: Firefox<=42+
					// Avoid non-left-click in FF but don't block IE radio events (#3861, gh-2343)
					if (delegateCount && cur.nodeType && (event.type !== "click" || isNaN(event.button) || event.button < 1)) {

						/* jshint eqeqeq: false */
						for (; cur != this; cur = cur.parentNode || this) {
							/* jshint eqeqeq: true */

							// Don't check non-elements (#13208)
							// Don't process clicks on disabled elements (#6911, #8165, #11382, #11764)
							if (cur.nodeType === 1 && (cur.disabled !== true || event.type !== "click")) {
								matches = [];
								for (i = 0; i < delegateCount; i++) {
									handleObj = _handlers[i];

									// Don't conflict with Object.prototype properties (#13203)
									sel = handleObj.selector + " ";

									if (matches[sel] === undefined) {
										matches[sel] = handleObj.needsContext ? jQuery(sel, this).index(cur) > -1 : jQuery.find(sel, this, null, [cur]).length;
									}
									if (matches[sel]) {
										matches.push(handleObj);
									}
								}
								if (matches.length) {
									handlerQueue.push({ elem: cur, handlers: matches });
								}
							}
						}
					}

					// Add the remaining (directly-bound) handlers
					if (delegateCount < _handlers.length) {
						handlerQueue.push({ elem: this, handlers: _handlers.slice(delegateCount) });
					}

					return handlerQueue;
				},

				fix: function fix(event) {
					if (event[jQuery.expando]) {
						return event;
					}

					// Create a writable copy of the event object and normalize some properties
					var i,
					    prop,
					    copy,
					    type = event.type,
					    originalEvent = event,
					    fixHook = this.fixHooks[type];

					if (!fixHook) {
						this.fixHooks[type] = fixHook = rmouseEvent.test(type) ? this.mouseHooks : rkeyEvent.test(type) ? this.keyHooks : {};
					}
					copy = fixHook.props ? this.props.concat(fixHook.props) : this.props;

					event = new jQuery.Event(originalEvent);

					i = copy.length;
					while (i--) {
						prop = copy[i];
						event[prop] = originalEvent[prop];
					}

					// Support: IE<9
					// Fix target property (#1925)
					if (!event.target) {
						event.target = originalEvent.srcElement || document;
					}

					// Support: Safari 6-8+
					// Target should not be a text node (#504, #13143)
					if (event.target.nodeType === 3) {
						event.target = event.target.parentNode;
					}

					// Support: IE<9
					// For mouse/key events, metaKey==false if it's undefined (#3368, #11328)
					event.metaKey = !!event.metaKey;

					return fixHook.filter ? fixHook.filter(event, originalEvent) : event;
				},

				// Includes some event props shared by KeyEvent and MouseEvent
				props: ("altKey bubbles cancelable ctrlKey currentTarget detail eventPhase " + "metaKey relatedTarget shiftKey target timeStamp view which").split(" "),

				fixHooks: {},

				keyHooks: {
					props: "char charCode key keyCode".split(" "),
					filter: function filter(event, original) {

						// Add which for key events
						if (event.which == null) {
							event.which = original.charCode != null ? original.charCode : original.keyCode;
						}

						return event;
					}
				},

				mouseHooks: {
					props: ("button buttons clientX clientY fromElement offsetX offsetY " + "pageX pageY screenX screenY toElement").split(" "),
					filter: function filter(event, original) {
						var body,
						    eventDoc,
						    doc,
						    button = original.button,
						    fromElement = original.fromElement;

						// Calculate pageX/Y if missing and clientX/Y available
						if (event.pageX == null && original.clientX != null) {
							eventDoc = event.target.ownerDocument || document;
							doc = eventDoc.documentElement;
							body = eventDoc.body;

							event.pageX = original.clientX + (doc && doc.scrollLeft || body && body.scrollLeft || 0) - (doc && doc.clientLeft || body && body.clientLeft || 0);
							event.pageY = original.clientY + (doc && doc.scrollTop || body && body.scrollTop || 0) - (doc && doc.clientTop || body && body.clientTop || 0);
						}

						// Add relatedTarget, if necessary
						if (!event.relatedTarget && fromElement) {
							event.relatedTarget = fromElement === event.target ? original.toElement : fromElement;
						}

						// Add which for click: 1 === left; 2 === middle; 3 === right
						// Note: button is not normalized, so don't use it
						if (!event.which && button !== undefined) {
							event.which = button & 1 ? 1 : button & 2 ? 3 : button & 4 ? 2 : 0;
						}

						return event;
					}
				},

				special: {
					load: {

						// Prevent triggered image.load events from bubbling to window.load
						noBubble: true
					},
					focus: {

						// Fire native event if possible so blur/focus sequence is correct
						trigger: function trigger() {
							if (this !== safeActiveElement() && this.focus) {
								try {
									this.focus();
									return false;
								} catch (e) {

									// Support: IE<9
									// If we error on focus to hidden element (#1486, #12518),
									// let .trigger() run the handlers
								}
							}
						},
						delegateType: "focusin"
					},
					blur: {
						trigger: function trigger() {
							if (this === safeActiveElement() && this.blur) {
								this.blur();
								return false;
							}
						},
						delegateType: "focusout"
					},
					click: {

						// For checkbox, fire native event so checked state will be right
						trigger: function trigger() {
							if (jQuery.nodeName(this, "input") && this.type === "checkbox" && this.click) {
								this.click();
								return false;
							}
						},

						// For cross-browser consistency, don't fire native .click() on links
						_default: function _default(event) {
							return jQuery.nodeName(event.target, "a");
						}
					},

					beforeunload: {
						postDispatch: function postDispatch(event) {

							// Support: Firefox 20+
							// Firefox doesn't alert if the returnValue field is not set.
							if (event.result !== undefined && event.originalEvent) {
								event.originalEvent.returnValue = event.result;
							}
						}
					}
				},

				// Piggyback on a donor event to simulate a different one
				simulate: function simulate(type, elem, event) {
					var e = jQuery.extend(new jQuery.Event(), event, {
						type: type,
						isSimulated: true

						// Previously, `originalEvent: {}` was set here, so stopPropagation call
						// would not be triggered on donor event, since in our own
						// jQuery.event.stopPropagation function we had a check for existence of
						// originalEvent.stopPropagation method, so, consequently it would be a noop.
						//
						// Guard for simulated events was moved to jQuery.event.stopPropagation function
						// since `originalEvent` should point to the original event for the
						// constancy with other events and for more focused logic
					});

					jQuery.event.trigger(e, null, elem);

					if (e.isDefaultPrevented()) {
						event.preventDefault();
					}
				}
			};

			jQuery.removeEvent = document.removeEventListener ? function (elem, type, handle) {

				// This "if" is needed for plain objects
				if (elem.removeEventListener) {
					elem.removeEventListener(type, handle);
				}
			} : function (elem, type, handle) {
				var name = "on" + type;

				if (elem.detachEvent) {

					// #8545, #7054, preventing memory leaks for custom events in IE6-8
					// detachEvent needed property on element, by name of that event,
					// to properly expose it to GC
					if (typeof elem[name] === "undefined") {
						elem[name] = null;
					}

					elem.detachEvent(name, handle);
				}
			};

			jQuery.Event = function (src, props) {

				// Allow instantiation without the 'new' keyword
				if (!(this instanceof jQuery.Event)) {
					return new jQuery.Event(src, props);
				}

				// Event object
				if (src && src.type) {
					this.originalEvent = src;
					this.type = src.type;

					// Events bubbling up the document may have been marked as prevented
					// by a handler lower down the tree; reflect the correct value.
					this.isDefaultPrevented = src.defaultPrevented || src.defaultPrevented === undefined &&

					// Support: IE < 9, Android < 4.0
					src.returnValue === false ? returnTrue : returnFalse;

					// Event type
				} else {
					this.type = src;
				}

				// Put explicitly provided properties onto the event object
				if (props) {
					jQuery.extend(this, props);
				}

				// Create a timestamp if incoming event doesn't have one
				this.timeStamp = src && src.timeStamp || jQuery.now();

				// Mark it as fixed
				this[jQuery.expando] = true;
			};

			// jQuery.Event is based on DOM3 Events as specified by the ECMAScript Language Binding
			// http://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
			jQuery.Event.prototype = {
				constructor: jQuery.Event,
				isDefaultPrevented: returnFalse,
				isPropagationStopped: returnFalse,
				isImmediatePropagationStopped: returnFalse,

				preventDefault: function preventDefault() {
					var e = this.originalEvent;

					this.isDefaultPrevented = returnTrue;
					if (!e) {
						return;
					}

					// If preventDefault exists, run it on the original event
					if (e.preventDefault) {
						e.preventDefault();

						// Support: IE
						// Otherwise set the returnValue property of the original event to false
					} else {
						e.returnValue = false;
					}
				},
				stopPropagation: function stopPropagation() {
					var e = this.originalEvent;

					this.isPropagationStopped = returnTrue;

					if (!e || this.isSimulated) {
						return;
					}

					// If stopPropagation exists, run it on the original event
					if (e.stopPropagation) {
						e.stopPropagation();
					}

					// Support: IE
					// Set the cancelBubble property of the original event to true
					e.cancelBubble = true;
				},
				stopImmediatePropagation: function stopImmediatePropagation() {
					var e = this.originalEvent;

					this.isImmediatePropagationStopped = returnTrue;

					if (e && e.stopImmediatePropagation) {
						e.stopImmediatePropagation();
					}

					this.stopPropagation();
				}
			};

			// Create mouseenter/leave events using mouseover/out and event-time checks
			// so that event delegation works in jQuery.
			// Do the same for pointerenter/pointerleave and pointerover/pointerout
			//
			// Support: Safari 7 only
			// Safari sends mouseenter too often; see:
			// https://code.google.com/p/chromium/issues/detail?id=470258
			// for the description of the bug (it existed in older Chrome versions as well).
			jQuery.each({
				mouseenter: "mouseover",
				mouseleave: "mouseout",
				pointerenter: "pointerover",
				pointerleave: "pointerout"
			}, function (orig, fix) {
				jQuery.event.special[orig] = {
					delegateType: fix,
					bindType: fix,

					handle: function handle(event) {
						var ret,
						    target = this,
						    related = event.relatedTarget,
						    handleObj = event.handleObj;

						// For mouseenter/leave call the handler if related is outside the target.
						// NB: No relatedTarget if the mouse left/entered the browser window
						if (!related || related !== target && !jQuery.contains(target, related)) {
							event.type = handleObj.origType;
							ret = handleObj.handler.apply(this, arguments);
							event.type = fix;
						}
						return ret;
					}
				};
			});

			// IE submit delegation
			if (!support.submit) {

				jQuery.event.special.submit = {
					setup: function setup() {

						// Only need this for delegated form submit events
						if (jQuery.nodeName(this, "form")) {
							return false;
						}

						// Lazy-add a submit handler when a descendant form may potentially be submitted
						jQuery.event.add(this, "click._submit keypress._submit", function (e) {

							// Node name check avoids a VML-related crash in IE (#9807)
							var elem = e.target,
							    form = jQuery.nodeName(elem, "input") || jQuery.nodeName(elem, "button") ?

							// Support: IE <=8
							// We use jQuery.prop instead of elem.form
							// to allow fixing the IE8 delegated submit issue (gh-2332)
							// by 3rd party polyfills/workarounds.
							jQuery.prop(elem, "form") : undefined;

							if (form && !jQuery._data(form, "submit")) {
								jQuery.event.add(form, "submit._submit", function (event) {
									event._submitBubble = true;
								});
								jQuery._data(form, "submit", true);
							}
						});

						// return undefined since we don't need an event listener
					},

					postDispatch: function postDispatch(event) {

						// If form was submitted by the user, bubble the event up the tree
						if (event._submitBubble) {
							delete event._submitBubble;
							if (this.parentNode && !event.isTrigger) {
								jQuery.event.simulate("submit", this.parentNode, event);
							}
						}
					},

					teardown: function teardown() {

						// Only need this for delegated form submit events
						if (jQuery.nodeName(this, "form")) {
							return false;
						}

						// Remove delegated handlers; cleanData eventually reaps submit handlers attached above
						jQuery.event.remove(this, "._submit");
					}
				};
			}

			// IE change delegation and checkbox/radio fix
			if (!support.change) {

				jQuery.event.special.change = {

					setup: function setup() {

						if (rformElems.test(this.nodeName)) {

							// IE doesn't fire change on a check/radio until blur; trigger it on click
							// after a propertychange. Eat the blur-change in special.change.handle.
							// This still fires onchange a second time for check/radio after blur.
							if (this.type === "checkbox" || this.type === "radio") {
								jQuery.event.add(this, "propertychange._change", function (event) {
									if (event.originalEvent.propertyName === "checked") {
										this._justChanged = true;
									}
								});
								jQuery.event.add(this, "click._change", function (event) {
									if (this._justChanged && !event.isTrigger) {
										this._justChanged = false;
									}

									// Allow triggered, simulated change events (#11500)
									jQuery.event.simulate("change", this, event);
								});
							}
							return false;
						}

						// Delegated event; lazy-add a change handler on descendant inputs
						jQuery.event.add(this, "beforeactivate._change", function (e) {
							var elem = e.target;

							if (rformElems.test(elem.nodeName) && !jQuery._data(elem, "change")) {
								jQuery.event.add(elem, "change._change", function (event) {
									if (this.parentNode && !event.isSimulated && !event.isTrigger) {
										jQuery.event.simulate("change", this.parentNode, event);
									}
								});
								jQuery._data(elem, "change", true);
							}
						});
					},

					handle: function handle(event) {
						var elem = event.target;

						// Swallow native change events from checkbox/radio, we already triggered them above
						if (this !== elem || event.isSimulated || event.isTrigger || elem.type !== "radio" && elem.type !== "checkbox") {

							return event.handleObj.handler.apply(this, arguments);
						}
					},

					teardown: function teardown() {
						jQuery.event.remove(this, "._change");

						return !rformElems.test(this.nodeName);
					}
				};
			}

			// Support: Firefox
			// Firefox doesn't have focus(in | out) events
			// Related ticket - https://bugzilla.mozilla.org/show_bug.cgi?id=687787
			//
			// Support: Chrome, Safari
			// focus(in | out) events fire after focus & blur events,
			// which is spec violation - http://www.w3.org/TR/DOM-Level-3-Events/#events-focusevent-event-order
			// Related ticket - https://code.google.com/p/chromium/issues/detail?id=449857
			if (!support.focusin) {
				jQuery.each({ focus: "focusin", blur: "focusout" }, function (orig, fix) {

					// Attach a single capturing handler on the document while someone wants focusin/focusout
					var handler = function handler(event) {
						jQuery.event.simulate(fix, event.target, jQuery.event.fix(event));
					};

					jQuery.event.special[fix] = {
						setup: function setup() {
							var doc = this.ownerDocument || this,
							    attaches = jQuery._data(doc, fix);

							if (!attaches) {
								doc.addEventListener(orig, handler, true);
							}
							jQuery._data(doc, fix, (attaches || 0) + 1);
						},
						teardown: function teardown() {
							var doc = this.ownerDocument || this,
							    attaches = jQuery._data(doc, fix) - 1;

							if (!attaches) {
								doc.removeEventListener(orig, handler, true);
								jQuery._removeData(doc, fix);
							} else {
								jQuery._data(doc, fix, attaches);
							}
						}
					};
				});
			}

			jQuery.fn.extend({

				on: function on(types, selector, data, fn) {
					return _on(this, types, selector, data, fn);
				},
				one: function one(types, selector, data, fn) {
					return _on(this, types, selector, data, fn, 1);
				},
				off: function off(types, selector, fn) {
					var handleObj, type;
					if (types && types.preventDefault && types.handleObj) {

						// ( event )  dispatched jQuery.Event
						handleObj = types.handleObj;
						jQuery(types.delegateTarget).off(handleObj.namespace ? handleObj.origType + "." + handleObj.namespace : handleObj.origType, handleObj.selector, handleObj.handler);
						return this;
					}
					if ((typeof types === "undefined" ? "undefined" : _typeof(types)) === "object") {

						// ( types-object [, selector] )
						for (type in types) {
							this.off(type, selector, types[type]);
						}
						return this;
					}
					if (selector === false || typeof selector === "function") {

						// ( types [, fn] )
						fn = selector;
						selector = undefined;
					}
					if (fn === false) {
						fn = returnFalse;
					}
					return this.each(function () {
						jQuery.event.remove(this, types, fn, selector);
					});
				},

				trigger: function trigger(type, data) {
					return this.each(function () {
						jQuery.event.trigger(type, data, this);
					});
				},
				triggerHandler: function triggerHandler(type, data) {
					var elem = this[0];
					if (elem) {
						return jQuery.event.trigger(type, data, elem, true);
					}
				}
			});

			var rinlinejQuery = / jQuery\d+="(?:null|\d+)"/g,
			    rnoshimcache = new RegExp("<(?:" + nodeNames + ")[\\s/>]", "i"),
			    rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:-]+)[^>]*)\/>/gi,


			// Support: IE 10-11, Edge 10240+
			// In IE/Edge using regex groups here causes severe slowdowns.
			// See https://connect.microsoft.com/IE/feedback/details/1736512/
			rnoInnerhtml = /<script|<style|<link/i,


			// checked="checked" or checked
			rchecked = /checked\s*(?:[^=]|=\s*.checked.)/i,
			    rscriptTypeMasked = /^true\/(.*)/,
			    rcleanScript = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,
			    safeFragment = createSafeFragment(document),
			    fragmentDiv = safeFragment.appendChild(document.createElement("div"));

			// Support: IE<8
			// Manipulating tables requires a tbody
			function manipulationTarget(elem, content) {
				return jQuery.nodeName(elem, "table") && jQuery.nodeName(content.nodeType !== 11 ? content : content.firstChild, "tr") ? elem.getElementsByTagName("tbody")[0] || elem.appendChild(elem.ownerDocument.createElement("tbody")) : elem;
			}

			// Replace/restore the type attribute of script elements for safe DOM manipulation
			function disableScript(elem) {
				elem.type = (jQuery.find.attr(elem, "type") !== null) + "/" + elem.type;
				return elem;
			}
			function restoreScript(elem) {
				var match = rscriptTypeMasked.exec(elem.type);
				if (match) {
					elem.type = match[1];
				} else {
					elem.removeAttribute("type");
				}
				return elem;
			}

			function cloneCopyEvent(src, dest) {
				if (dest.nodeType !== 1 || !jQuery.hasData(src)) {
					return;
				}

				var type,
				    i,
				    l,
				    oldData = jQuery._data(src),
				    curData = jQuery._data(dest, oldData),
				    events = oldData.events;

				if (events) {
					delete curData.handle;
					curData.events = {};

					for (type in events) {
						for (i = 0, l = events[type].length; i < l; i++) {
							jQuery.event.add(dest, type, events[type][i]);
						}
					}
				}

				// make the cloned public data object a copy from the original
				if (curData.data) {
					curData.data = jQuery.extend({}, curData.data);
				}
			}

			function fixCloneNodeIssues(src, dest) {
				var nodeName, e, data;

				// We do not need to do anything for non-Elements
				if (dest.nodeType !== 1) {
					return;
				}

				nodeName = dest.nodeName.toLowerCase();

				// IE6-8 copies events bound via attachEvent when using cloneNode.
				if (!support.noCloneEvent && dest[jQuery.expando]) {
					data = jQuery._data(dest);

					for (e in data.events) {
						jQuery.removeEvent(dest, e, data.handle);
					}

					// Event data gets referenced instead of copied if the expando gets copied too
					dest.removeAttribute(jQuery.expando);
				}

				// IE blanks contents when cloning scripts, and tries to evaluate newly-set text
				if (nodeName === "script" && dest.text !== src.text) {
					disableScript(dest).text = src.text;
					restoreScript(dest);

					// IE6-10 improperly clones children of object elements using classid.
					// IE10 throws NoModificationAllowedError if parent is null, #12132.
				} else if (nodeName === "object") {
					if (dest.parentNode) {
						dest.outerHTML = src.outerHTML;
					}

					// This path appears unavoidable for IE9. When cloning an object
					// element in IE9, the outerHTML strategy above is not sufficient.
					// If the src has innerHTML and the destination does not,
					// copy the src.innerHTML into the dest.innerHTML. #10324
					if (support.html5Clone && src.innerHTML && !jQuery.trim(dest.innerHTML)) {
						dest.innerHTML = src.innerHTML;
					}
				} else if (nodeName === "input" && rcheckableType.test(src.type)) {

					// IE6-8 fails to persist the checked state of a cloned checkbox
					// or radio button. Worse, IE6-7 fail to give the cloned element
					// a checked appearance if the defaultChecked value isn't also set

					dest.defaultChecked = dest.checked = src.checked;

					// IE6-7 get confused and end up setting the value of a cloned
					// checkbox/radio button to an empty string instead of "on"
					if (dest.value !== src.value) {
						dest.value = src.value;
					}

					// IE6-8 fails to return the selected option to the default selected
					// state when cloning options
				} else if (nodeName === "option") {
					dest.defaultSelected = dest.selected = src.defaultSelected;

					// IE6-8 fails to set the defaultValue to the correct value when
					// cloning other types of input fields
				} else if (nodeName === "input" || nodeName === "textarea") {
					dest.defaultValue = src.defaultValue;
				}
			}

			function domManip(collection, args, callback, ignored) {

				// Flatten any nested arrays
				args = concat.apply([], args);

				var first,
				    node,
				    hasScripts,
				    scripts,
				    doc,
				    fragment,
				    i = 0,
				    l = collection.length,
				    iNoClone = l - 1,
				    value = args[0],
				    isFunction = jQuery.isFunction(value);

				// We can't cloneNode fragments that contain checked, in WebKit
				if (isFunction || l > 1 && typeof value === "string" && !support.checkClone && rchecked.test(value)) {
					return collection.each(function (index) {
						var self = collection.eq(index);
						if (isFunction) {
							args[0] = value.call(this, index, self.html());
						}
						domManip(self, args, callback, ignored);
					});
				}

				if (l) {
					fragment = buildFragment(args, collection[0].ownerDocument, false, collection, ignored);
					first = fragment.firstChild;

					if (fragment.childNodes.length === 1) {
						fragment = first;
					}

					// Require either new content or an interest in ignored elements to invoke the callback
					if (first || ignored) {
						scripts = jQuery.map(getAll(fragment, "script"), disableScript);
						hasScripts = scripts.length;

						// Use the original fragment for the last item
						// instead of the first because it can end up
						// being emptied incorrectly in certain situations (#8070).
						for (; i < l; i++) {
							node = fragment;

							if (i !== iNoClone) {
								node = jQuery.clone(node, true, true);

								// Keep references to cloned scripts for later restoration
								if (hasScripts) {

									// Support: Android<4.1, PhantomJS<2
									// push.apply(_, arraylike) throws on ancient WebKit
									jQuery.merge(scripts, getAll(node, "script"));
								}
							}

							callback.call(collection[i], node, i);
						}

						if (hasScripts) {
							doc = scripts[scripts.length - 1].ownerDocument;

							// Reenable scripts
							jQuery.map(scripts, restoreScript);

							// Evaluate executable scripts on first document insertion
							for (i = 0; i < hasScripts; i++) {
								node = scripts[i];
								if (rscriptType.test(node.type || "") && !jQuery._data(node, "globalEval") && jQuery.contains(doc, node)) {

									if (node.src) {

										// Optional AJAX dependency, but won't run scripts if not present
										if (jQuery._evalUrl) {
											jQuery._evalUrl(node.src);
										}
									} else {
										jQuery.globalEval((node.text || node.textContent || node.innerHTML || "").replace(rcleanScript, ""));
									}
								}
							}
						}

						// Fix #11809: Avoid leaking memory
						fragment = first = null;
					}
				}

				return collection;
			}

			function _remove(elem, selector, keepData) {
				var node,
				    elems = selector ? jQuery.filter(selector, elem) : elem,
				    i = 0;

				for (; (node = elems[i]) != null; i++) {

					if (!keepData && node.nodeType === 1) {
						jQuery.cleanData(getAll(node));
					}

					if (node.parentNode) {
						if (keepData && jQuery.contains(node.ownerDocument, node)) {
							setGlobalEval(getAll(node, "script"));
						}
						node.parentNode.removeChild(node);
					}
				}

				return elem;
			}

			jQuery.extend({
				htmlPrefilter: function htmlPrefilter(html) {
					return html.replace(rxhtmlTag, "<$1></$2>");
				},

				clone: function clone(elem, dataAndEvents, deepDataAndEvents) {
					var destElements,
					    node,
					    clone,
					    i,
					    srcElements,
					    inPage = jQuery.contains(elem.ownerDocument, elem);

					if (support.html5Clone || jQuery.isXMLDoc(elem) || !rnoshimcache.test("<" + elem.nodeName + ">")) {

						clone = elem.cloneNode(true);

						// IE<=8 does not properly clone detached, unknown element nodes
					} else {
						fragmentDiv.innerHTML = elem.outerHTML;
						fragmentDiv.removeChild(clone = fragmentDiv.firstChild);
					}

					if ((!support.noCloneEvent || !support.noCloneChecked) && (elem.nodeType === 1 || elem.nodeType === 11) && !jQuery.isXMLDoc(elem)) {

						// We eschew Sizzle here for performance reasons: http://jsperf.com/getall-vs-sizzle/2
						destElements = getAll(clone);
						srcElements = getAll(elem);

						// Fix all IE cloning issues
						for (i = 0; (node = srcElements[i]) != null; ++i) {

							// Ensure that the destination node is not null; Fixes #9587
							if (destElements[i]) {
								fixCloneNodeIssues(node, destElements[i]);
							}
						}
					}

					// Copy the events from the original to the clone
					if (dataAndEvents) {
						if (deepDataAndEvents) {
							srcElements = srcElements || getAll(elem);
							destElements = destElements || getAll(clone);

							for (i = 0; (node = srcElements[i]) != null; i++) {
								cloneCopyEvent(node, destElements[i]);
							}
						} else {
							cloneCopyEvent(elem, clone);
						}
					}

					// Preserve script evaluation history
					destElements = getAll(clone, "script");
					if (destElements.length > 0) {
						setGlobalEval(destElements, !inPage && getAll(elem, "script"));
					}

					destElements = srcElements = node = null;

					// Return the cloned set
					return clone;
				},

				cleanData: function cleanData(elems, /* internal */forceAcceptData) {
					var elem,
					    type,
					    id,
					    data,
					    i = 0,
					    internalKey = jQuery.expando,
					    cache = jQuery.cache,
					    attributes = support.attributes,
					    special = jQuery.event.special;

					for (; (elem = elems[i]) != null; i++) {
						if (forceAcceptData || acceptData(elem)) {

							id = elem[internalKey];
							data = id && cache[id];

							if (data) {
								if (data.events) {
									for (type in data.events) {
										if (special[type]) {
											jQuery.event.remove(elem, type);

											// This is a shortcut to avoid jQuery.event.remove's overhead
										} else {
											jQuery.removeEvent(elem, type, data.handle);
										}
									}
								}

								// Remove cache only if it was not already removed by jQuery.event.remove
								if (cache[id]) {

									delete cache[id];

									// Support: IE<9
									// IE does not allow us to delete expando properties from nodes
									// IE creates expando attributes along with the property
									// IE does not have a removeAttribute function on Document nodes
									if (!attributes && typeof elem.removeAttribute !== "undefined") {
										elem.removeAttribute(internalKey);

										// Webkit & Blink performance suffers when deleting properties
										// from DOM nodes, so set to undefined instead
										// https://code.google.com/p/chromium/issues/detail?id=378607
									} else {
										elem[internalKey] = undefined;
									}

									deletedIds.push(id);
								}
							}
						}
					}
				}
			});

			jQuery.fn.extend({

				// Keep domManip exposed until 3.0 (gh-2225)
				domManip: domManip,

				detach: function detach(selector) {
					return _remove(this, selector, true);
				},

				remove: function remove(selector) {
					return _remove(this, selector);
				},

				text: function text(value) {
					return access(this, function (value) {
						return value === undefined ? jQuery.text(this) : this.empty().append((this[0] && this[0].ownerDocument || document).createTextNode(value));
					}, null, value, arguments.length);
				},

				append: function append() {
					return domManip(this, arguments, function (elem) {
						if (this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9) {
							var target = manipulationTarget(this, elem);
							target.appendChild(elem);
						}
					});
				},

				prepend: function prepend() {
					return domManip(this, arguments, function (elem) {
						if (this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9) {
							var target = manipulationTarget(this, elem);
							target.insertBefore(elem, target.firstChild);
						}
					});
				},

				before: function before() {
					return domManip(this, arguments, function (elem) {
						if (this.parentNode) {
							this.parentNode.insertBefore(elem, this);
						}
					});
				},

				after: function after() {
					return domManip(this, arguments, function (elem) {
						if (this.parentNode) {
							this.parentNode.insertBefore(elem, this.nextSibling);
						}
					});
				},

				empty: function empty() {
					var elem,
					    i = 0;

					for (; (elem = this[i]) != null; i++) {

						// Remove element nodes and prevent memory leaks
						if (elem.nodeType === 1) {
							jQuery.cleanData(getAll(elem, false));
						}

						// Remove any remaining nodes
						while (elem.firstChild) {
							elem.removeChild(elem.firstChild);
						}

						// If this is a select, ensure that it displays empty (#12336)
						// Support: IE<9
						if (elem.options && jQuery.nodeName(elem, "select")) {
							elem.options.length = 0;
						}
					}

					return this;
				},

				clone: function clone(dataAndEvents, deepDataAndEvents) {
					dataAndEvents = dataAndEvents == null ? false : dataAndEvents;
					deepDataAndEvents = deepDataAndEvents == null ? dataAndEvents : deepDataAndEvents;

					return this.map(function () {
						return jQuery.clone(this, dataAndEvents, deepDataAndEvents);
					});
				},

				html: function html(value) {
					return access(this, function (value) {
						var elem = this[0] || {},
						    i = 0,
						    l = this.length;

						if (value === undefined) {
							return elem.nodeType === 1 ? elem.innerHTML.replace(rinlinejQuery, "") : undefined;
						}

						// See if we can take a shortcut and just use innerHTML
						if (typeof value === "string" && !rnoInnerhtml.test(value) && (support.htmlSerialize || !rnoshimcache.test(value)) && (support.leadingWhitespace || !rleadingWhitespace.test(value)) && !wrapMap[(rtagName.exec(value) || ["", ""])[1].toLowerCase()]) {

							value = jQuery.htmlPrefilter(value);

							try {
								for (; i < l; i++) {

									// Remove element nodes and prevent memory leaks
									elem = this[i] || {};
									if (elem.nodeType === 1) {
										jQuery.cleanData(getAll(elem, false));
										elem.innerHTML = value;
									}
								}

								elem = 0;

								// If using innerHTML throws an exception, use the fallback method
							} catch (e) {}
						}

						if (elem) {
							this.empty().append(value);
						}
					}, null, value, arguments.length);
				},

				replaceWith: function replaceWith() {
					var ignored = [];

					// Make the changes, replacing each non-ignored context element with the new content
					return domManip(this, arguments, function (elem) {
						var parent = this.parentNode;

						if (jQuery.inArray(this, ignored) < 0) {
							jQuery.cleanData(getAll(this));
							if (parent) {
								parent.replaceChild(elem, this);
							}
						}

						// Force callback invocation
					}, ignored);
				}
			});

			jQuery.each({
				appendTo: "append",
				prependTo: "prepend",
				insertBefore: "before",
				insertAfter: "after",
				replaceAll: "replaceWith"
			}, function (name, original) {
				jQuery.fn[name] = function (selector) {
					var elems,
					    i = 0,
					    ret = [],
					    insert = jQuery(selector),
					    last = insert.length - 1;

					for (; i <= last; i++) {
						elems = i === last ? this : this.clone(true);
						jQuery(insert[i])[original](elems);

						// Modern browsers can apply jQuery collections as arrays, but oldIE needs a .get()
						push.apply(ret, elems.get());
					}

					return this.pushStack(ret);
				};
			});

			var iframe,
			    elemdisplay = {

				// Support: Firefox
				// We have to pre-define these values for FF (#10227)
				HTML: "block",
				BODY: "block"
			};

			/**
	   * Retrieve the actual display of a element
	   * @param {String} name nodeName of the element
	   * @param {Object} doc Document object
	   */

			// Called only from within defaultDisplay
			function actualDisplay(name, doc) {
				var elem = jQuery(doc.createElement(name)).appendTo(doc.body),
				    display = jQuery.css(elem[0], "display");

				// We don't have any data stored on the element,
				// so use "detach" method as fast way to get rid of the element
				elem.detach();

				return display;
			}

			/**
	   * Try to determine the default display value of an element
	   * @param {String} nodeName
	   */
			function defaultDisplay(nodeName) {
				var doc = document,
				    display = elemdisplay[nodeName];

				if (!display) {
					display = actualDisplay(nodeName, doc);

					// If the simple way fails, read from inside an iframe
					if (display === "none" || !display) {

						// Use the already-created iframe if possible
						iframe = (iframe || jQuery("<iframe frameborder='0' width='0' height='0'/>")).appendTo(doc.documentElement);

						// Always write a new HTML skeleton so Webkit and Firefox don't choke on reuse
						doc = (iframe[0].contentWindow || iframe[0].contentDocument).document;

						// Support: IE
						doc.write();
						doc.close();

						display = actualDisplay(nodeName, doc);
						iframe.detach();
					}

					// Store the correct default display
					elemdisplay[nodeName] = display;
				}

				return display;
			}
			var rmargin = /^margin/;

			var rnumnonpx = new RegExp("^(" + pnum + ")(?!px)[a-z%]+$", "i");

			var swap = function swap(elem, options, callback, args) {
				var ret,
				    name,
				    old = {};

				// Remember the old values, and insert the new ones
				for (name in options) {
					old[name] = elem.style[name];
					elem.style[name] = options[name];
				}

				ret = callback.apply(elem, args || []);

				// Revert the old values
				for (name in options) {
					elem.style[name] = old[name];
				}

				return ret;
			};

			var documentElement = document.documentElement;

			(function () {
				var pixelPositionVal,
				    pixelMarginRightVal,
				    boxSizingReliableVal,
				    reliableHiddenOffsetsVal,
				    reliableMarginRightVal,
				    reliableMarginLeftVal,
				    container = document.createElement("div"),
				    div = document.createElement("div");

				// Finish early in limited (non-browser) environments
				if (!div.style) {
					return;
				}

				div.style.cssText = "float:left;opacity:.5";

				// Support: IE<9
				// Make sure that element opacity exists (as opposed to filter)
				support.opacity = div.style.opacity === "0.5";

				// Verify style float existence
				// (IE uses styleFloat instead of cssFloat)
				support.cssFloat = !!div.style.cssFloat;

				div.style.backgroundClip = "content-box";
				div.cloneNode(true).style.backgroundClip = "";
				support.clearCloneStyle = div.style.backgroundClip === "content-box";

				container = document.createElement("div");
				container.style.cssText = "border:0;width:8px;height:0;top:0;left:-9999px;" + "padding:0;margin-top:1px;position:absolute";
				div.innerHTML = "";
				container.appendChild(div);

				// Support: Firefox<29, Android 2.3
				// Vendor-prefix box-sizing
				support.boxSizing = div.style.boxSizing === "" || div.style.MozBoxSizing === "" || div.style.WebkitBoxSizing === "";

				jQuery.extend(support, {
					reliableHiddenOffsets: function reliableHiddenOffsets() {
						if (pixelPositionVal == null) {
							computeStyleTests();
						}
						return reliableHiddenOffsetsVal;
					},

					boxSizingReliable: function boxSizingReliable() {

						// We're checking for pixelPositionVal here instead of boxSizingReliableVal
						// since that compresses better and they're computed together anyway.
						if (pixelPositionVal == null) {
							computeStyleTests();
						}
						return boxSizingReliableVal;
					},

					pixelMarginRight: function pixelMarginRight() {

						// Support: Android 4.0-4.3
						if (pixelPositionVal == null) {
							computeStyleTests();
						}
						return pixelMarginRightVal;
					},

					pixelPosition: function pixelPosition() {
						if (pixelPositionVal == null) {
							computeStyleTests();
						}
						return pixelPositionVal;
					},

					reliableMarginRight: function reliableMarginRight() {

						// Support: Android 2.3
						if (pixelPositionVal == null) {
							computeStyleTests();
						}
						return reliableMarginRightVal;
					},

					reliableMarginLeft: function reliableMarginLeft() {

						// Support: IE <=8 only, Android 4.0 - 4.3 only, Firefox <=3 - 37
						if (pixelPositionVal == null) {
							computeStyleTests();
						}
						return reliableMarginLeftVal;
					}
				});

				function computeStyleTests() {
					var contents,
					    divStyle,
					    documentElement = document.documentElement;

					// Setup
					documentElement.appendChild(container);

					div.style.cssText =

					// Support: Android 2.3
					// Vendor-prefix box-sizing
					"-webkit-box-sizing:border-box;box-sizing:border-box;" + "position:relative;display:block;" + "margin:auto;border:1px;padding:1px;" + "top:1%;width:50%";

					// Support: IE<9
					// Assume reasonable values in the absence of getComputedStyle
					pixelPositionVal = boxSizingReliableVal = reliableMarginLeftVal = false;
					pixelMarginRightVal = reliableMarginRightVal = true;

					// Check for getComputedStyle so that this code is not run in IE<9.
					if (window.getComputedStyle) {
						divStyle = window.getComputedStyle(div);
						pixelPositionVal = (divStyle || {}).top !== "1%";
						reliableMarginLeftVal = (divStyle || {}).marginLeft === "2px";
						boxSizingReliableVal = (divStyle || { width: "4px" }).width === "4px";

						// Support: Android 4.0 - 4.3 only
						// Some styles come back with percentage values, even though they shouldn't
						div.style.marginRight = "50%";
						pixelMarginRightVal = (divStyle || { marginRight: "4px" }).marginRight === "4px";

						// Support: Android 2.3 only
						// Div with explicit width and no margin-right incorrectly
						// gets computed margin-right based on width of container (#3333)
						// WebKit Bug 13343 - getComputedStyle returns wrong value for margin-right
						contents = div.appendChild(document.createElement("div"));

						// Reset CSS: box-sizing; display; margin; border; padding
						contents.style.cssText = div.style.cssText =

						// Support: Android 2.3
						// Vendor-prefix box-sizing
						"-webkit-box-sizing:content-box;-moz-box-sizing:content-box;" + "box-sizing:content-box;display:block;margin:0;border:0;padding:0";
						contents.style.marginRight = contents.style.width = "0";
						div.style.width = "1px";

						reliableMarginRightVal = !parseFloat((window.getComputedStyle(contents) || {}).marginRight);

						div.removeChild(contents);
					}

					// Support: IE6-8
					// First check that getClientRects works as expected
					// Check if table cells still have offsetWidth/Height when they are set
					// to display:none and there are still other visible table cells in a
					// table row; if so, offsetWidth/Height are not reliable for use when
					// determining if an element has been hidden directly using
					// display:none (it is still safe to use offsets if a parent element is
					// hidden; don safety goggles and see bug #4512 for more information).
					div.style.display = "none";
					reliableHiddenOffsetsVal = div.getClientRects().length === 0;
					if (reliableHiddenOffsetsVal) {
						div.style.display = "";
						div.innerHTML = "<table><tr><td></td><td>t</td></tr></table>";
						div.childNodes[0].style.borderCollapse = "separate";
						contents = div.getElementsByTagName("td");
						contents[0].style.cssText = "margin:0;border:0;padding:0;display:none";
						reliableHiddenOffsetsVal = contents[0].offsetHeight === 0;
						if (reliableHiddenOffsetsVal) {
							contents[0].style.display = "";
							contents[1].style.display = "none";
							reliableHiddenOffsetsVal = contents[0].offsetHeight === 0;
						}
					}

					// Teardown
					documentElement.removeChild(container);
				}
			})();

			var getStyles,
			    curCSS,
			    rposition = /^(top|right|bottom|left)$/;

			if (window.getComputedStyle) {
				getStyles = function getStyles(elem) {

					// Support: IE<=11+, Firefox<=30+ (#15098, #14150)
					// IE throws on elements created in popups
					// FF meanwhile throws on frame elements through "defaultView.getComputedStyle"
					var view = elem.ownerDocument.defaultView;

					if (!view || !view.opener) {
						view = window;
					}

					return view.getComputedStyle(elem);
				};

				curCSS = function curCSS(elem, name, computed) {
					var width,
					    minWidth,
					    maxWidth,
					    ret,
					    style = elem.style;

					computed = computed || getStyles(elem);

					// getPropertyValue is only needed for .css('filter') in IE9, see #12537
					ret = computed ? computed.getPropertyValue(name) || computed[name] : undefined;

					// Support: Opera 12.1x only
					// Fall back to style even without computed
					// computed is undefined for elems on document fragments
					if ((ret === "" || ret === undefined) && !jQuery.contains(elem.ownerDocument, elem)) {
						ret = jQuery.style(elem, name);
					}

					if (computed) {

						// A tribute to the "awesome hack by Dean Edwards"
						// Chrome < 17 and Safari 5.0 uses "computed value"
						// instead of "used value" for margin-right
						// Safari 5.1.7 (at least) returns percentage for a larger set of values,
						// but width seems to be reliably pixels
						// this is against the CSSOM draft spec:
						// http://dev.w3.org/csswg/cssom/#resolved-values
						if (!support.pixelMarginRight() && rnumnonpx.test(ret) && rmargin.test(name)) {

							// Remember the original values
							width = style.width;
							minWidth = style.minWidth;
							maxWidth = style.maxWidth;

							// Put in the new values to get a computed value out
							style.minWidth = style.maxWidth = style.width = ret;
							ret = computed.width;

							// Revert the changed values
							style.width = width;
							style.minWidth = minWidth;
							style.maxWidth = maxWidth;
						}
					}

					// Support: IE
					// IE returns zIndex value as an integer.
					return ret === undefined ? ret : ret + "";
				};
			} else if (documentElement.currentStyle) {
				getStyles = function getStyles(elem) {
					return elem.currentStyle;
				};

				curCSS = function curCSS(elem, name, computed) {
					var left,
					    rs,
					    rsLeft,
					    ret,
					    style = elem.style;

					computed = computed || getStyles(elem);
					ret = computed ? computed[name] : undefined;

					// Avoid setting ret to empty string here
					// so we don't default to auto
					if (ret == null && style && style[name]) {
						ret = style[name];
					}

					// From the awesome hack by Dean Edwards
					// http://erik.eae.net/archives/2007/07/27/18.54.15/#comment-102291

					// If we're not dealing with a regular pixel number
					// but a number that has a weird ending, we need to convert it to pixels
					// but not position css attributes, as those are
					// proportional to the parent element instead
					// and we can't measure the parent instead because it
					// might trigger a "stacking dolls" problem
					if (rnumnonpx.test(ret) && !rposition.test(name)) {

						// Remember the original values
						left = style.left;
						rs = elem.runtimeStyle;
						rsLeft = rs && rs.left;

						// Put in the new values to get a computed value out
						if (rsLeft) {
							rs.left = elem.currentStyle.left;
						}
						style.left = name === "fontSize" ? "1em" : ret;
						ret = style.pixelLeft + "px";

						// Revert the changed values
						style.left = left;
						if (rsLeft) {
							rs.left = rsLeft;
						}
					}

					// Support: IE
					// IE returns zIndex value as an integer.
					return ret === undefined ? ret : ret + "" || "auto";
				};
			}

			function addGetHookIf(conditionFn, hookFn) {

				// Define the hook, we'll check on the first run if it's really needed.
				return {
					get: function get() {
						if (conditionFn()) {

							// Hook not needed (or it's not possible to use it due
							// to missing dependency), remove it.
							delete this.get;
							return;
						}

						// Hook needed; redefine it so that the support test is not executed again.
						return (this.get = hookFn).apply(this, arguments);
					}
				};
			}

			var ralpha = /alpha\([^)]*\)/i,
			    ropacity = /opacity\s*=\s*([^)]*)/i,


			// swappable if display is none or starts with table except
			// "table", "table-cell", or "table-caption"
			// see here for display values:
			// https://developer.mozilla.org/en-US/docs/CSS/display
			rdisplayswap = /^(none|table(?!-c[ea]).+)/,
			    rnumsplit = new RegExp("^(" + pnum + ")(.*)$", "i"),
			    cssShow = { position: "absolute", visibility: "hidden", display: "block" },
			    cssNormalTransform = {
				letterSpacing: "0",
				fontWeight: "400"
			},
			    cssPrefixes = ["Webkit", "O", "Moz", "ms"],
			    emptyStyle = document.createElement("div").style;

			// return a css property mapped to a potentially vendor prefixed property
			function vendorPropName(name) {

				// shortcut for names that are not vendor prefixed
				if (name in emptyStyle) {
					return name;
				}

				// check for vendor prefixed names
				var capName = name.charAt(0).toUpperCase() + name.slice(1),
				    i = cssPrefixes.length;

				while (i--) {
					name = cssPrefixes[i] + capName;
					if (name in emptyStyle) {
						return name;
					}
				}
			}

			function showHide(elements, show) {
				var display,
				    elem,
				    hidden,
				    values = [],
				    index = 0,
				    length = elements.length;

				for (; index < length; index++) {
					elem = elements[index];
					if (!elem.style) {
						continue;
					}

					values[index] = jQuery._data(elem, "olddisplay");
					display = elem.style.display;
					if (show) {

						// Reset the inline display of this element to learn if it is
						// being hidden by cascaded rules or not
						if (!values[index] && display === "none") {
							elem.style.display = "";
						}

						// Set elements which have been overridden with display: none
						// in a stylesheet to whatever the default browser style is
						// for such an element
						if (elem.style.display === "" && isHidden(elem)) {
							values[index] = jQuery._data(elem, "olddisplay", defaultDisplay(elem.nodeName));
						}
					} else {
						hidden = isHidden(elem);

						if (display && display !== "none" || !hidden) {
							jQuery._data(elem, "olddisplay", hidden ? display : jQuery.css(elem, "display"));
						}
					}
				}

				// Set the display of most of the elements in a second loop
				// to avoid the constant reflow
				for (index = 0; index < length; index++) {
					elem = elements[index];
					if (!elem.style) {
						continue;
					}
					if (!show || elem.style.display === "none" || elem.style.display === "") {
						elem.style.display = show ? values[index] || "" : "none";
					}
				}

				return elements;
			}

			function setPositiveNumber(elem, value, subtract) {
				var matches = rnumsplit.exec(value);
				return matches ?

				// Guard against undefined "subtract", e.g., when used as in cssHooks
				Math.max(0, matches[1] - (subtract || 0)) + (matches[2] || "px") : value;
			}

			function augmentWidthOrHeight(elem, name, extra, isBorderBox, styles) {
				var i = extra === (isBorderBox ? "border" : "content") ?

				// If we already have the right measurement, avoid augmentation
				4 :

				// Otherwise initialize for horizontal or vertical properties
				name === "width" ? 1 : 0,
				    val = 0;

				for (; i < 4; i += 2) {

					// both box models exclude margin, so add it if we want it
					if (extra === "margin") {
						val += jQuery.css(elem, extra + cssExpand[i], true, styles);
					}

					if (isBorderBox) {

						// border-box includes padding, so remove it if we want content
						if (extra === "content") {
							val -= jQuery.css(elem, "padding" + cssExpand[i], true, styles);
						}

						// at this point, extra isn't border nor margin, so remove border
						if (extra !== "margin") {
							val -= jQuery.css(elem, "border" + cssExpand[i] + "Width", true, styles);
						}
					} else {

						// at this point, extra isn't content, so add padding
						val += jQuery.css(elem, "padding" + cssExpand[i], true, styles);

						// at this point, extra isn't content nor padding, so add border
						if (extra !== "padding") {
							val += jQuery.css(elem, "border" + cssExpand[i] + "Width", true, styles);
						}
					}
				}

				return val;
			}

			function getWidthOrHeight(elem, name, extra) {

				// Start with offset property, which is equivalent to the border-box value
				var valueIsBorderBox = true,
				    val = name === "width" ? elem.offsetWidth : elem.offsetHeight,
				    styles = getStyles(elem),
				    isBorderBox = support.boxSizing && jQuery.css(elem, "boxSizing", false, styles) === "border-box";

				// some non-html elements return undefined for offsetWidth, so check for null/undefined
				// svg - https://bugzilla.mozilla.org/show_bug.cgi?id=649285
				// MathML - https://bugzilla.mozilla.org/show_bug.cgi?id=491668
				if (val <= 0 || val == null) {

					// Fall back to computed then uncomputed css if necessary
					val = curCSS(elem, name, styles);
					if (val < 0 || val == null) {
						val = elem.style[name];
					}

					// Computed unit is not pixels. Stop here and return.
					if (rnumnonpx.test(val)) {
						return val;
					}

					// we need the check for style in case a browser which returns unreliable values
					// for getComputedStyle silently falls back to the reliable elem.style
					valueIsBorderBox = isBorderBox && (support.boxSizingReliable() || val === elem.style[name]);

					// Normalize "", auto, and prepare for extra
					val = parseFloat(val) || 0;
				}

				// use the active box-sizing model to add/subtract irrelevant styles
				return val + augmentWidthOrHeight(elem, name, extra || (isBorderBox ? "border" : "content"), valueIsBorderBox, styles) + "px";
			}

			jQuery.extend({

				// Add in style property hooks for overriding the default
				// behavior of getting and setting a style property
				cssHooks: {
					opacity: {
						get: function get(elem, computed) {
							if (computed) {

								// We should always get a number back from opacity
								var ret = curCSS(elem, "opacity");
								return ret === "" ? "1" : ret;
							}
						}
					}
				},

				// Don't automatically add "px" to these possibly-unitless properties
				cssNumber: {
					"animationIterationCount": true,
					"columnCount": true,
					"fillOpacity": true,
					"flexGrow": true,
					"flexShrink": true,
					"fontWeight": true,
					"lineHeight": true,
					"opacity": true,
					"order": true,
					"orphans": true,
					"widows": true,
					"zIndex": true,
					"zoom": true
				},

				// Add in properties whose names you wish to fix before
				// setting or getting the value
				cssProps: {

					// normalize float css property
					"float": support.cssFloat ? "cssFloat" : "styleFloat"
				},

				// Get and set the style property on a DOM Node
				style: function style(elem, name, value, extra) {

					// Don't set styles on text and comment nodes
					if (!elem || elem.nodeType === 3 || elem.nodeType === 8 || !elem.style) {
						return;
					}

					// Make sure that we're working with the right name
					var ret,
					    type,
					    hooks,
					    origName = jQuery.camelCase(name),
					    style = elem.style;

					name = jQuery.cssProps[origName] || (jQuery.cssProps[origName] = vendorPropName(origName) || origName);

					// gets hook for the prefixed version
					// followed by the unprefixed version
					hooks = jQuery.cssHooks[name] || jQuery.cssHooks[origName];

					// Check if we're setting a value
					if (value !== undefined) {
						type = typeof value === "undefined" ? "undefined" : _typeof(value);

						// Convert "+=" or "-=" to relative numbers (#7345)
						if (type === "string" && (ret = rcssNum.exec(value)) && ret[1]) {
							value = adjustCSS(elem, name, ret);

							// Fixes bug #9237
							type = "number";
						}

						// Make sure that null and NaN values aren't set. See: #7116
						if (value == null || value !== value) {
							return;
						}

						// If a number was passed in, add the unit (except for certain CSS properties)
						if (type === "number") {
							value += ret && ret[3] || (jQuery.cssNumber[origName] ? "" : "px");
						}

						// Fixes #8908, it can be done more correctly by specifing setters in cssHooks,
						// but it would mean to define eight
						// (for every problematic property) identical functions
						if (!support.clearCloneStyle && value === "" && name.indexOf("background") === 0) {
							style[name] = "inherit";
						}

						// If a hook was provided, use that value, otherwise just set the specified value
						if (!hooks || !("set" in hooks) || (value = hooks.set(elem, value, extra)) !== undefined) {

							// Support: IE
							// Swallow errors from 'invalid' CSS values (#5509)
							try {
								style[name] = value;
							} catch (e) {}
						}
					} else {

						// If a hook was provided get the non-computed value from there
						if (hooks && "get" in hooks && (ret = hooks.get(elem, false, extra)) !== undefined) {

							return ret;
						}

						// Otherwise just get the value from the style object
						return style[name];
					}
				},

				css: function css(elem, name, extra, styles) {
					var num,
					    val,
					    hooks,
					    origName = jQuery.camelCase(name);

					// Make sure that we're working with the right name
					name = jQuery.cssProps[origName] || (jQuery.cssProps[origName] = vendorPropName(origName) || origName);

					// gets hook for the prefixed version
					// followed by the unprefixed version
					hooks = jQuery.cssHooks[name] || jQuery.cssHooks[origName];

					// If a hook was provided get the computed value from there
					if (hooks && "get" in hooks) {
						val = hooks.get(elem, true, extra);
					}

					// Otherwise, if a way to get the computed value exists, use that
					if (val === undefined) {
						val = curCSS(elem, name, styles);
					}

					//convert "normal" to computed value
					if (val === "normal" && name in cssNormalTransform) {
						val = cssNormalTransform[name];
					}

					// Return, converting to number if forced or a qualifier was provided and val looks numeric
					if (extra === "" || extra) {
						num = parseFloat(val);
						return extra === true || isFinite(num) ? num || 0 : val;
					}
					return val;
				}
			});

			jQuery.each(["height", "width"], function (i, name) {
				jQuery.cssHooks[name] = {
					get: function get(elem, computed, extra) {
						if (computed) {

							// certain elements can have dimension info if we invisibly show them
							// however, it must have a current display style that would benefit from this
							return rdisplayswap.test(jQuery.css(elem, "display")) && elem.offsetWidth === 0 ? swap(elem, cssShow, function () {
								return getWidthOrHeight(elem, name, extra);
							}) : getWidthOrHeight(elem, name, extra);
						}
					},

					set: function set(elem, value, extra) {
						var styles = extra && getStyles(elem);
						return setPositiveNumber(elem, value, extra ? augmentWidthOrHeight(elem, name, extra, support.boxSizing && jQuery.css(elem, "boxSizing", false, styles) === "border-box", styles) : 0);
					}
				};
			});

			if (!support.opacity) {
				jQuery.cssHooks.opacity = {
					get: function get(elem, computed) {

						// IE uses filters for opacity
						return ropacity.test((computed && elem.currentStyle ? elem.currentStyle.filter : elem.style.filter) || "") ? 0.01 * parseFloat(RegExp.$1) + "" : computed ? "1" : "";
					},

					set: function set(elem, value) {
						var style = elem.style,
						    currentStyle = elem.currentStyle,
						    opacity = jQuery.isNumeric(value) ? "alpha(opacity=" + value * 100 + ")" : "",
						    filter = currentStyle && currentStyle.filter || style.filter || "";

						// IE has trouble with opacity if it does not have layout
						// Force it by setting the zoom level
						style.zoom = 1;

						// if setting opacity to 1, and no other filters exist -
						// attempt to remove filter attribute #6652
						// if value === "", then remove inline opacity #12685
						if ((value >= 1 || value === "") && jQuery.trim(filter.replace(ralpha, "")) === "" && style.removeAttribute) {

							// Setting style.filter to null, "" & " " still leave "filter:" in the cssText
							// if "filter:" is present at all, clearType is disabled, we want to avoid this
							// style.removeAttribute is IE Only, but so apparently is this code path...
							style.removeAttribute("filter");

							// if there is no filter style applied in a css rule
							// or unset inline opacity, we are done
							if (value === "" || currentStyle && !currentStyle.filter) {
								return;
							}
						}

						// otherwise, set new filter values
						style.filter = ralpha.test(filter) ? filter.replace(ralpha, opacity) : filter + " " + opacity;
					}
				};
			}

			jQuery.cssHooks.marginRight = addGetHookIf(support.reliableMarginRight, function (elem, computed) {
				if (computed) {
					return swap(elem, { "display": "inline-block" }, curCSS, [elem, "marginRight"]);
				}
			});

			jQuery.cssHooks.marginLeft = addGetHookIf(support.reliableMarginLeft, function (elem, computed) {
				if (computed) {
					return (parseFloat(curCSS(elem, "marginLeft")) || (

					// Support: IE<=11+
					// Running getBoundingClientRect on a disconnected node in IE throws an error
					// Support: IE8 only
					// getClientRects() errors on disconnected elems
					jQuery.contains(elem.ownerDocument, elem) ? elem.getBoundingClientRect().left - swap(elem, { marginLeft: 0 }, function () {
						return elem.getBoundingClientRect().left;
					}) : 0)) + "px";
				}
			});

			// These hooks are used by animate to expand properties
			jQuery.each({
				margin: "",
				padding: "",
				border: "Width"
			}, function (prefix, suffix) {
				jQuery.cssHooks[prefix + suffix] = {
					expand: function expand(value) {
						var i = 0,
						    expanded = {},


						// assumes a single number if not a string
						parts = typeof value === "string" ? value.split(" ") : [value];

						for (; i < 4; i++) {
							expanded[prefix + cssExpand[i] + suffix] = parts[i] || parts[i - 2] || parts[0];
						}

						return expanded;
					}
				};

				if (!rmargin.test(prefix)) {
					jQuery.cssHooks[prefix + suffix].set = setPositiveNumber;
				}
			});

			jQuery.fn.extend({
				css: function css(name, value) {
					return access(this, function (elem, name, value) {
						var styles,
						    len,
						    map = {},
						    i = 0;

						if (jQuery.isArray(name)) {
							styles = getStyles(elem);
							len = name.length;

							for (; i < len; i++) {
								map[name[i]] = jQuery.css(elem, name[i], false, styles);
							}

							return map;
						}

						return value !== undefined ? jQuery.style(elem, name, value) : jQuery.css(elem, name);
					}, name, value, arguments.length > 1);
				},
				show: function show() {
					return showHide(this, true);
				},
				hide: function hide() {
					return showHide(this);
				},
				toggle: function toggle(state) {
					if (typeof state === "boolean") {
						return state ? this.show() : this.hide();
					}

					return this.each(function () {
						if (isHidden(this)) {
							jQuery(this).show();
						} else {
							jQuery(this).hide();
						}
					});
				}
			});

			function Tween(elem, options, prop, end, easing) {
				return new Tween.prototype.init(elem, options, prop, end, easing);
			}
			jQuery.Tween = Tween;

			Tween.prototype = {
				constructor: Tween,
				init: function init(elem, options, prop, end, easing, unit) {
					this.elem = elem;
					this.prop = prop;
					this.easing = easing || jQuery.easing._default;
					this.options = options;
					this.start = this.now = this.cur();
					this.end = end;
					this.unit = unit || (jQuery.cssNumber[prop] ? "" : "px");
				},
				cur: function cur() {
					var hooks = Tween.propHooks[this.prop];

					return hooks && hooks.get ? hooks.get(this) : Tween.propHooks._default.get(this);
				},
				run: function run(percent) {
					var eased,
					    hooks = Tween.propHooks[this.prop];

					if (this.options.duration) {
						this.pos = eased = jQuery.easing[this.easing](percent, this.options.duration * percent, 0, 1, this.options.duration);
					} else {
						this.pos = eased = percent;
					}
					this.now = (this.end - this.start) * eased + this.start;

					if (this.options.step) {
						this.options.step.call(this.elem, this.now, this);
					}

					if (hooks && hooks.set) {
						hooks.set(this);
					} else {
						Tween.propHooks._default.set(this);
					}
					return this;
				}
			};

			Tween.prototype.init.prototype = Tween.prototype;

			Tween.propHooks = {
				_default: {
					get: function get(tween) {
						var result;

						// Use a property on the element directly when it is not a DOM element,
						// or when there is no matching style property that exists.
						if (tween.elem.nodeType !== 1 || tween.elem[tween.prop] != null && tween.elem.style[tween.prop] == null) {
							return tween.elem[tween.prop];
						}

						// passing an empty string as a 3rd parameter to .css will automatically
						// attempt a parseFloat and fallback to a string if the parse fails
						// so, simple values such as "10px" are parsed to Float.
						// complex values such as "rotate(1rad)" are returned as is.
						result = jQuery.css(tween.elem, tween.prop, "");

						// Empty strings, null, undefined and "auto" are converted to 0.
						return !result || result === "auto" ? 0 : result;
					},
					set: function set(tween) {

						// use step hook for back compat - use cssHook if its there - use .style if its
						// available and use plain properties where available
						if (jQuery.fx.step[tween.prop]) {
							jQuery.fx.step[tween.prop](tween);
						} else if (tween.elem.nodeType === 1 && (tween.elem.style[jQuery.cssProps[tween.prop]] != null || jQuery.cssHooks[tween.prop])) {
							jQuery.style(tween.elem, tween.prop, tween.now + tween.unit);
						} else {
							tween.elem[tween.prop] = tween.now;
						}
					}
				}
			};

			// Support: IE <=9
			// Panic based approach to setting things on disconnected nodes

			Tween.propHooks.scrollTop = Tween.propHooks.scrollLeft = {
				set: function set(tween) {
					if (tween.elem.nodeType && tween.elem.parentNode) {
						tween.elem[tween.prop] = tween.now;
					}
				}
			};

			jQuery.easing = {
				linear: function linear(p) {
					return p;
				},
				swing: function swing(p) {
					return 0.5 - Math.cos(p * Math.PI) / 2;
				},
				_default: "swing"
			};

			jQuery.fx = Tween.prototype.init;

			// Back Compat <1.8 extension point
			jQuery.fx.step = {};

			var fxNow,
			    timerId,
			    rfxtypes = /^(?:toggle|show|hide)$/,
			    rrun = /queueHooks$/;

			// Animations created synchronously will run synchronously
			function createFxNow() {
				window.setTimeout(function () {
					fxNow = undefined;
				});
				return fxNow = jQuery.now();
			}

			// Generate parameters to create a standard animation
			function genFx(type, includeWidth) {
				var which,
				    attrs = { height: type },
				    i = 0;

				// if we include width, step value is 1 to do all cssExpand values,
				// if we don't include width, step value is 2 to skip over Left and Right
				includeWidth = includeWidth ? 1 : 0;
				for (; i < 4; i += 2 - includeWidth) {
					which = cssExpand[i];
					attrs["margin" + which] = attrs["padding" + which] = type;
				}

				if (includeWidth) {
					attrs.opacity = attrs.width = type;
				}

				return attrs;
			}

			function createTween(value, prop, animation) {
				var tween,
				    collection = (Animation.tweeners[prop] || []).concat(Animation.tweeners["*"]),
				    index = 0,
				    length = collection.length;
				for (; index < length; index++) {
					if (tween = collection[index].call(animation, prop, value)) {

						// we're done with this property
						return tween;
					}
				}
			}

			function defaultPrefilter(elem, props, opts) {
				/* jshint validthis: true */
				var prop,
				    value,
				    toggle,
				    tween,
				    hooks,
				    oldfire,
				    display,
				    checkDisplay,
				    anim = this,
				    orig = {},
				    style = elem.style,
				    hidden = elem.nodeType && isHidden(elem),
				    dataShow = jQuery._data(elem, "fxshow");

				// handle queue: false promises
				if (!opts.queue) {
					hooks = jQuery._queueHooks(elem, "fx");
					if (hooks.unqueued == null) {
						hooks.unqueued = 0;
						oldfire = hooks.empty.fire;
						hooks.empty.fire = function () {
							if (!hooks.unqueued) {
								oldfire();
							}
						};
					}
					hooks.unqueued++;

					anim.always(function () {

						// doing this makes sure that the complete handler will be called
						// before this completes
						anim.always(function () {
							hooks.unqueued--;
							if (!jQuery.queue(elem, "fx").length) {
								hooks.empty.fire();
							}
						});
					});
				}

				// height/width overflow pass
				if (elem.nodeType === 1 && ("height" in props || "width" in props)) {

					// Make sure that nothing sneaks out
					// Record all 3 overflow attributes because IE does not
					// change the overflow attribute when overflowX and
					// overflowY are set to the same value
					opts.overflow = [style.overflow, style.overflowX, style.overflowY];

					// Set display property to inline-block for height/width
					// animations on inline elements that are having width/height animated
					display = jQuery.css(elem, "display");

					// Test default display if display is currently "none"
					checkDisplay = display === "none" ? jQuery._data(elem, "olddisplay") || defaultDisplay(elem.nodeName) : display;

					if (checkDisplay === "inline" && jQuery.css(elem, "float") === "none") {

						// inline-level elements accept inline-block;
						// block-level elements need to be inline with layout
						if (!support.inlineBlockNeedsLayout || defaultDisplay(elem.nodeName) === "inline") {
							style.display = "inline-block";
						} else {
							style.zoom = 1;
						}
					}
				}

				if (opts.overflow) {
					style.overflow = "hidden";
					if (!support.shrinkWrapBlocks()) {
						anim.always(function () {
							style.overflow = opts.overflow[0];
							style.overflowX = opts.overflow[1];
							style.overflowY = opts.overflow[2];
						});
					}
				}

				// show/hide pass
				for (prop in props) {
					value = props[prop];
					if (rfxtypes.exec(value)) {
						delete props[prop];
						toggle = toggle || value === "toggle";
						if (value === (hidden ? "hide" : "show")) {

							// If there is dataShow left over from a stopped hide or show
							// and we are going to proceed with show, we should pretend to be hidden
							if (value === "show" && dataShow && dataShow[prop] !== undefined) {
								hidden = true;
							} else {
								continue;
							}
						}
						orig[prop] = dataShow && dataShow[prop] || jQuery.style(elem, prop);

						// Any non-fx value stops us from restoring the original display value
					} else {
						display = undefined;
					}
				}

				if (!jQuery.isEmptyObject(orig)) {
					if (dataShow) {
						if ("hidden" in dataShow) {
							hidden = dataShow.hidden;
						}
					} else {
						dataShow = jQuery._data(elem, "fxshow", {});
					}

					// store state if its toggle - enables .stop().toggle() to "reverse"
					if (toggle) {
						dataShow.hidden = !hidden;
					}
					if (hidden) {
						jQuery(elem).show();
					} else {
						anim.done(function () {
							jQuery(elem).hide();
						});
					}
					anim.done(function () {
						var prop;
						jQuery._removeData(elem, "fxshow");
						for (prop in orig) {
							jQuery.style(elem, prop, orig[prop]);
						}
					});
					for (prop in orig) {
						tween = createTween(hidden ? dataShow[prop] : 0, prop, anim);

						if (!(prop in dataShow)) {
							dataShow[prop] = tween.start;
							if (hidden) {
								tween.end = tween.start;
								tween.start = prop === "width" || prop === "height" ? 1 : 0;
							}
						}
					}

					// If this is a noop like .hide().hide(), restore an overwritten display value
				} else if ((display === "none" ? defaultDisplay(elem.nodeName) : display) === "inline") {
					style.display = display;
				}
			}

			function propFilter(props, specialEasing) {
				var index, name, easing, value, hooks;

				// camelCase, specialEasing and expand cssHook pass
				for (index in props) {
					name = jQuery.camelCase(index);
					easing = specialEasing[name];
					value = props[index];
					if (jQuery.isArray(value)) {
						easing = value[1];
						value = props[index] = value[0];
					}

					if (index !== name) {
						props[name] = value;
						delete props[index];
					}

					hooks = jQuery.cssHooks[name];
					if (hooks && "expand" in hooks) {
						value = hooks.expand(value);
						delete props[name];

						// not quite $.extend, this wont overwrite keys already present.
						// also - reusing 'index' from above because we have the correct "name"
						for (index in value) {
							if (!(index in props)) {
								props[index] = value[index];
								specialEasing[index] = easing;
							}
						}
					} else {
						specialEasing[name] = easing;
					}
				}
			}

			function Animation(elem, properties, options) {
				var result,
				    stopped,
				    index = 0,
				    length = Animation.prefilters.length,
				    deferred = jQuery.Deferred().always(function () {

					// don't match elem in the :animated selector
					delete tick.elem;
				}),
				    tick = function tick() {
					if (stopped) {
						return false;
					}
					var currentTime = fxNow || createFxNow(),
					    remaining = Math.max(0, animation.startTime + animation.duration - currentTime),


					// Support: Android 2.3
					// Archaic crash bug won't allow us to use `1 - ( 0.5 || 0 )` (#12497)
					temp = remaining / animation.duration || 0,
					    percent = 1 - temp,
					    index = 0,
					    length = animation.tweens.length;

					for (; index < length; index++) {
						animation.tweens[index].run(percent);
					}

					deferred.notifyWith(elem, [animation, percent, remaining]);

					if (percent < 1 && length) {
						return remaining;
					} else {
						deferred.resolveWith(elem, [animation]);
						return false;
					}
				},
				    animation = deferred.promise({
					elem: elem,
					props: jQuery.extend({}, properties),
					opts: jQuery.extend(true, {
						specialEasing: {},
						easing: jQuery.easing._default
					}, options),
					originalProperties: properties,
					originalOptions: options,
					startTime: fxNow || createFxNow(),
					duration: options.duration,
					tweens: [],
					createTween: function createTween(prop, end) {
						var tween = jQuery.Tween(elem, animation.opts, prop, end, animation.opts.specialEasing[prop] || animation.opts.easing);
						animation.tweens.push(tween);
						return tween;
					},
					stop: function stop(gotoEnd) {
						var index = 0,


						// if we are going to the end, we want to run all the tweens
						// otherwise we skip this part
						length = gotoEnd ? animation.tweens.length : 0;
						if (stopped) {
							return this;
						}
						stopped = true;
						for (; index < length; index++) {
							animation.tweens[index].run(1);
						}

						// resolve when we played the last frame
						// otherwise, reject
						if (gotoEnd) {
							deferred.notifyWith(elem, [animation, 1, 0]);
							deferred.resolveWith(elem, [animation, gotoEnd]);
						} else {
							deferred.rejectWith(elem, [animation, gotoEnd]);
						}
						return this;
					}
				}),
				    props = animation.props;

				propFilter(props, animation.opts.specialEasing);

				for (; index < length; index++) {
					result = Animation.prefilters[index].call(animation, elem, props, animation.opts);
					if (result) {
						if (jQuery.isFunction(result.stop)) {
							jQuery._queueHooks(animation.elem, animation.opts.queue).stop = jQuery.proxy(result.stop, result);
						}
						return result;
					}
				}

				jQuery.map(props, createTween, animation);

				if (jQuery.isFunction(animation.opts.start)) {
					animation.opts.start.call(elem, animation);
				}

				jQuery.fx.timer(jQuery.extend(tick, {
					elem: elem,
					anim: animation,
					queue: animation.opts.queue
				}));

				// attach callbacks from options
				return animation.progress(animation.opts.progress).done(animation.opts.done, animation.opts.complete).fail(animation.opts.fail).always(animation.opts.always);
			}

			jQuery.Animation = jQuery.extend(Animation, {

				tweeners: {
					"*": [function (prop, value) {
						var tween = this.createTween(prop, value);
						adjustCSS(tween.elem, prop, rcssNum.exec(value), tween);
						return tween;
					}]
				},

				tweener: function tweener(props, callback) {
					if (jQuery.isFunction(props)) {
						callback = props;
						props = ["*"];
					} else {
						props = props.match(rnotwhite);
					}

					var prop,
					    index = 0,
					    length = props.length;

					for (; index < length; index++) {
						prop = props[index];
						Animation.tweeners[prop] = Animation.tweeners[prop] || [];
						Animation.tweeners[prop].unshift(callback);
					}
				},

				prefilters: [defaultPrefilter],

				prefilter: function prefilter(callback, prepend) {
					if (prepend) {
						Animation.prefilters.unshift(callback);
					} else {
						Animation.prefilters.push(callback);
					}
				}
			});

			jQuery.speed = function (speed, easing, fn) {
				var opt = speed && (typeof speed === "undefined" ? "undefined" : _typeof(speed)) === "object" ? jQuery.extend({}, speed) : {
					complete: fn || !fn && easing || jQuery.isFunction(speed) && speed,
					duration: speed,
					easing: fn && easing || easing && !jQuery.isFunction(easing) && easing
				};

				opt.duration = jQuery.fx.off ? 0 : typeof opt.duration === "number" ? opt.duration : opt.duration in jQuery.fx.speeds ? jQuery.fx.speeds[opt.duration] : jQuery.fx.speeds._default;

				// normalize opt.queue - true/undefined/null -> "fx"
				if (opt.queue == null || opt.queue === true) {
					opt.queue = "fx";
				}

				// Queueing
				opt.old = opt.complete;

				opt.complete = function () {
					if (jQuery.isFunction(opt.old)) {
						opt.old.call(this);
					}

					if (opt.queue) {
						jQuery.dequeue(this, opt.queue);
					}
				};

				return opt;
			};

			jQuery.fn.extend({
				fadeTo: function fadeTo(speed, to, easing, callback) {

					// show any hidden elements after setting opacity to 0
					return this.filter(isHidden).css("opacity", 0).show()

					// animate to the value specified
					.end().animate({ opacity: to }, speed, easing, callback);
				},
				animate: function animate(prop, speed, easing, callback) {
					var empty = jQuery.isEmptyObject(prop),
					    optall = jQuery.speed(speed, easing, callback),
					    doAnimation = function doAnimation() {

						// Operate on a copy of prop so per-property easing won't be lost
						var anim = Animation(this, jQuery.extend({}, prop), optall);

						// Empty animations, or finishing resolves immediately
						if (empty || jQuery._data(this, "finish")) {
							anim.stop(true);
						}
					};
					doAnimation.finish = doAnimation;

					return empty || optall.queue === false ? this.each(doAnimation) : this.queue(optall.queue, doAnimation);
				},
				stop: function stop(type, clearQueue, gotoEnd) {
					var stopQueue = function stopQueue(hooks) {
						var stop = hooks.stop;
						delete hooks.stop;
						stop(gotoEnd);
					};

					if (typeof type !== "string") {
						gotoEnd = clearQueue;
						clearQueue = type;
						type = undefined;
					}
					if (clearQueue && type !== false) {
						this.queue(type || "fx", []);
					}

					return this.each(function () {
						var dequeue = true,
						    index = type != null && type + "queueHooks",
						    timers = jQuery.timers,
						    data = jQuery._data(this);

						if (index) {
							if (data[index] && data[index].stop) {
								stopQueue(data[index]);
							}
						} else {
							for (index in data) {
								if (data[index] && data[index].stop && rrun.test(index)) {
									stopQueue(data[index]);
								}
							}
						}

						for (index = timers.length; index--;) {
							if (timers[index].elem === this && (type == null || timers[index].queue === type)) {

								timers[index].anim.stop(gotoEnd);
								dequeue = false;
								timers.splice(index, 1);
							}
						}

						// start the next in the queue if the last step wasn't forced
						// timers currently will call their complete callbacks, which will dequeue
						// but only if they were gotoEnd
						if (dequeue || !gotoEnd) {
							jQuery.dequeue(this, type);
						}
					});
				},
				finish: function finish(type) {
					if (type !== false) {
						type = type || "fx";
					}
					return this.each(function () {
						var index,
						    data = jQuery._data(this),
						    queue = data[type + "queue"],
						    hooks = data[type + "queueHooks"],
						    timers = jQuery.timers,
						    length = queue ? queue.length : 0;

						// enable finishing flag on private data
						data.finish = true;

						// empty the queue first
						jQuery.queue(this, type, []);

						if (hooks && hooks.stop) {
							hooks.stop.call(this, true);
						}

						// look for any active animations, and finish them
						for (index = timers.length; index--;) {
							if (timers[index].elem === this && timers[index].queue === type) {
								timers[index].anim.stop(true);
								timers.splice(index, 1);
							}
						}

						// look for any animations in the old queue and finish them
						for (index = 0; index < length; index++) {
							if (queue[index] && queue[index].finish) {
								queue[index].finish.call(this);
							}
						}

						// turn off finishing flag
						delete data.finish;
					});
				}
			});

			jQuery.each(["toggle", "show", "hide"], function (i, name) {
				var cssFn = jQuery.fn[name];
				jQuery.fn[name] = function (speed, easing, callback) {
					return speed == null || typeof speed === "boolean" ? cssFn.apply(this, arguments) : this.animate(genFx(name, true), speed, easing, callback);
				};
			});

			// Generate shortcuts for custom animations
			jQuery.each({
				slideDown: genFx("show"),
				slideUp: genFx("hide"),
				slideToggle: genFx("toggle"),
				fadeIn: { opacity: "show" },
				fadeOut: { opacity: "hide" },
				fadeToggle: { opacity: "toggle" }
			}, function (name, props) {
				jQuery.fn[name] = function (speed, easing, callback) {
					return this.animate(props, speed, easing, callback);
				};
			});

			jQuery.timers = [];
			jQuery.fx.tick = function () {
				var timer,
				    timers = jQuery.timers,
				    i = 0;

				fxNow = jQuery.now();

				for (; i < timers.length; i++) {
					timer = timers[i];

					// Checks the timer has not already been removed
					if (!timer() && timers[i] === timer) {
						timers.splice(i--, 1);
					}
				}

				if (!timers.length) {
					jQuery.fx.stop();
				}
				fxNow = undefined;
			};

			jQuery.fx.timer = function (timer) {
				jQuery.timers.push(timer);
				if (timer()) {
					jQuery.fx.start();
				} else {
					jQuery.timers.pop();
				}
			};

			jQuery.fx.interval = 13;

			jQuery.fx.start = function () {
				if (!timerId) {
					timerId = window.setInterval(jQuery.fx.tick, jQuery.fx.interval);
				}
			};

			jQuery.fx.stop = function () {
				window.clearInterval(timerId);
				timerId = null;
			};

			jQuery.fx.speeds = {
				slow: 600,
				fast: 200,

				// Default speed
				_default: 400
			};

			// Based off of the plugin by Clint Helfers, with permission.
			// http://web.archive.org/web/20100324014747/http://blindsignals.com/index.php/2009/07/jquery-delay/
			jQuery.fn.delay = function (time, type) {
				time = jQuery.fx ? jQuery.fx.speeds[time] || time : time;
				type = type || "fx";

				return this.queue(type, function (next, hooks) {
					var timeout = window.setTimeout(next, time);
					hooks.stop = function () {
						window.clearTimeout(timeout);
					};
				});
			};

			(function () {
				var a,
				    input = document.createElement("input"),
				    div = document.createElement("div"),
				    select = document.createElement("select"),
				    opt = select.appendChild(document.createElement("option"));

				// Setup
				div = document.createElement("div");
				div.setAttribute("className", "t");
				div.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>";
				a = div.getElementsByTagName("a")[0];

				// Support: Windows Web Apps (WWA)
				// `type` must use .setAttribute for WWA (#14901)
				input.setAttribute("type", "checkbox");
				div.appendChild(input);

				a = div.getElementsByTagName("a")[0];

				// First batch of tests.
				a.style.cssText = "top:1px";

				// Test setAttribute on camelCase class.
				// If it works, we need attrFixes when doing get/setAttribute (ie6/7)
				support.getSetAttribute = div.className !== "t";

				// Get the style information from getAttribute
				// (IE uses .cssText instead)
				support.style = /top/.test(a.getAttribute("style"));

				// Make sure that URLs aren't manipulated
				// (IE normalizes it by default)
				support.hrefNormalized = a.getAttribute("href") === "/a";

				// Check the default checkbox/radio value ("" on WebKit; "on" elsewhere)
				support.checkOn = !!input.value;

				// Make sure that a selected-by-default option has a working selected property.
				// (WebKit defaults to false instead of true, IE too, if it's in an optgroup)
				support.optSelected = opt.selected;

				// Tests for enctype support on a form (#6743)
				support.enctype = !!document.createElement("form").enctype;

				// Make sure that the options inside disabled selects aren't marked as disabled
				// (WebKit marks them as disabled)
				select.disabled = true;
				support.optDisabled = !opt.disabled;

				// Support: IE8 only
				// Check if we can trust getAttribute("value")
				input = document.createElement("input");
				input.setAttribute("value", "");
				support.input = input.getAttribute("value") === "";

				// Check if an input maintains its value after becoming a radio
				input.value = "t";
				input.setAttribute("type", "radio");
				support.radioValue = input.value === "t";
			})();

			var rreturn = /\r/g,
			    rspaces = /[\x20\t\r\n\f]+/g;

			jQuery.fn.extend({
				val: function val(value) {
					var hooks,
					    ret,
					    isFunction,
					    elem = this[0];

					if (!arguments.length) {
						if (elem) {
							hooks = jQuery.valHooks[elem.type] || jQuery.valHooks[elem.nodeName.toLowerCase()];

							if (hooks && "get" in hooks && (ret = hooks.get(elem, "value")) !== undefined) {
								return ret;
							}

							ret = elem.value;

							return typeof ret === "string" ?

							// handle most common string cases
							ret.replace(rreturn, "") :

							// handle cases where value is null/undef or number
							ret == null ? "" : ret;
						}

						return;
					}

					isFunction = jQuery.isFunction(value);

					return this.each(function (i) {
						var val;

						if (this.nodeType !== 1) {
							return;
						}

						if (isFunction) {
							val = value.call(this, i, jQuery(this).val());
						} else {
							val = value;
						}

						// Treat null/undefined as ""; convert numbers to string
						if (val == null) {
							val = "";
						} else if (typeof val === "number") {
							val += "";
						} else if (jQuery.isArray(val)) {
							val = jQuery.map(val, function (value) {
								return value == null ? "" : value + "";
							});
						}

						hooks = jQuery.valHooks[this.type] || jQuery.valHooks[this.nodeName.toLowerCase()];

						// If set returns undefined, fall back to normal setting
						if (!hooks || !("set" in hooks) || hooks.set(this, val, "value") === undefined) {
							this.value = val;
						}
					});
				}
			});

			jQuery.extend({
				valHooks: {
					option: {
						get: function get(elem) {
							var val = jQuery.find.attr(elem, "value");
							return val != null ? val :

							// Support: IE10-11+
							// option.text throws exceptions (#14686, #14858)
							// Strip and collapse whitespace
							// https://html.spec.whatwg.org/#strip-and-collapse-whitespace
							jQuery.trim(jQuery.text(elem)).replace(rspaces, " ");
						}
					},
					select: {
						get: function get(elem) {
							var value,
							    option,
							    options = elem.options,
							    index = elem.selectedIndex,
							    one = elem.type === "select-one" || index < 0,
							    values = one ? null : [],
							    max = one ? index + 1 : options.length,
							    i = index < 0 ? max : one ? index : 0;

							// Loop through all the selected options
							for (; i < max; i++) {
								option = options[i];

								// oldIE doesn't update selected after form reset (#2551)
								if ((option.selected || i === index) && (

								// Don't return options that are disabled or in a disabled optgroup
								support.optDisabled ? !option.disabled : option.getAttribute("disabled") === null) && (!option.parentNode.disabled || !jQuery.nodeName(option.parentNode, "optgroup"))) {

									// Get the specific value for the option
									value = jQuery(option).val();

									// We don't need an array for one selects
									if (one) {
										return value;
									}

									// Multi-Selects return an array
									values.push(value);
								}
							}

							return values;
						},

						set: function set(elem, value) {
							var optionSet,
							    option,
							    options = elem.options,
							    values = jQuery.makeArray(value),
							    i = options.length;

							while (i--) {
								option = options[i];

								if (jQuery.inArray(jQuery.valHooks.option.get(option), values) > -1) {

									// Support: IE6
									// When new option element is added to select box we need to
									// force reflow of newly added node in order to workaround delay
									// of initialization properties
									try {
										option.selected = optionSet = true;
									} catch (_) {

										// Will be executed only in IE6
										option.scrollHeight;
									}
								} else {
									option.selected = false;
								}
							}

							// Force browsers to behave consistently when non-matching value is set
							if (!optionSet) {
								elem.selectedIndex = -1;
							}

							return options;
						}
					}
				}
			});

			// Radios and checkboxes getter/setter
			jQuery.each(["radio", "checkbox"], function () {
				jQuery.valHooks[this] = {
					set: function set(elem, value) {
						if (jQuery.isArray(value)) {
							return elem.checked = jQuery.inArray(jQuery(elem).val(), value) > -1;
						}
					}
				};
				if (!support.checkOn) {
					jQuery.valHooks[this].get = function (elem) {
						return elem.getAttribute("value") === null ? "on" : elem.value;
					};
				}
			});

			var nodeHook,
			    boolHook,
			    attrHandle = jQuery.expr.attrHandle,
			    ruseDefault = /^(?:checked|selected)$/i,
			    getSetAttribute = support.getSetAttribute,
			    getSetInput = support.input;

			jQuery.fn.extend({
				attr: function attr(name, value) {
					return access(this, jQuery.attr, name, value, arguments.length > 1);
				},

				removeAttr: function removeAttr(name) {
					return this.each(function () {
						jQuery.removeAttr(this, name);
					});
				}
			});

			jQuery.extend({
				attr: function attr(elem, name, value) {
					var ret,
					    hooks,
					    nType = elem.nodeType;

					// Don't get/set attributes on text, comment and attribute nodes
					if (nType === 3 || nType === 8 || nType === 2) {
						return;
					}

					// Fallback to prop when attributes are not supported
					if (typeof elem.getAttribute === "undefined") {
						return jQuery.prop(elem, name, value);
					}

					// All attributes are lowercase
					// Grab necessary hook if one is defined
					if (nType !== 1 || !jQuery.isXMLDoc(elem)) {
						name = name.toLowerCase();
						hooks = jQuery.attrHooks[name] || (jQuery.expr.match.bool.test(name) ? boolHook : nodeHook);
					}

					if (value !== undefined) {
						if (value === null) {
							jQuery.removeAttr(elem, name);
							return;
						}

						if (hooks && "set" in hooks && (ret = hooks.set(elem, value, name)) !== undefined) {
							return ret;
						}

						elem.setAttribute(name, value + "");
						return value;
					}

					if (hooks && "get" in hooks && (ret = hooks.get(elem, name)) !== null) {
						return ret;
					}

					ret = jQuery.find.attr(elem, name);

					// Non-existent attributes return null, we normalize to undefined
					return ret == null ? undefined : ret;
				},

				attrHooks: {
					type: {
						set: function set(elem, value) {
							if (!support.radioValue && value === "radio" && jQuery.nodeName(elem, "input")) {

								// Setting the type on a radio button after the value resets the value in IE8-9
								// Reset value to default in case type is set after value during creation
								var val = elem.value;
								elem.setAttribute("type", value);
								if (val) {
									elem.value = val;
								}
								return value;
							}
						}
					}
				},

				removeAttr: function removeAttr(elem, value) {
					var name,
					    propName,
					    i = 0,
					    attrNames = value && value.match(rnotwhite);

					if (attrNames && elem.nodeType === 1) {
						while (name = attrNames[i++]) {
							propName = jQuery.propFix[name] || name;

							// Boolean attributes get special treatment (#10870)
							if (jQuery.expr.match.bool.test(name)) {

								// Set corresponding property to false
								if (getSetInput && getSetAttribute || !ruseDefault.test(name)) {
									elem[propName] = false;

									// Support: IE<9
									// Also clear defaultChecked/defaultSelected (if appropriate)
								} else {
									elem[jQuery.camelCase("default-" + name)] = elem[propName] = false;
								}

								// See #9699 for explanation of this approach (setting first, then removal)
							} else {
								jQuery.attr(elem, name, "");
							}

							elem.removeAttribute(getSetAttribute ? name : propName);
						}
					}
				}
			});

			// Hooks for boolean attributes
			boolHook = {
				set: function set(elem, value, name) {
					if (value === false) {

						// Remove boolean attributes when set to false
						jQuery.removeAttr(elem, name);
					} else if (getSetInput && getSetAttribute || !ruseDefault.test(name)) {

						// IE<8 needs the *property* name
						elem.setAttribute(!getSetAttribute && jQuery.propFix[name] || name, name);
					} else {

						// Support: IE<9
						// Use defaultChecked and defaultSelected for oldIE
						elem[jQuery.camelCase("default-" + name)] = elem[name] = true;
					}
					return name;
				}
			};

			jQuery.each(jQuery.expr.match.bool.source.match(/\w+/g), function (i, name) {
				var getter = attrHandle[name] || jQuery.find.attr;

				if (getSetInput && getSetAttribute || !ruseDefault.test(name)) {
					attrHandle[name] = function (elem, name, isXML) {
						var ret, handle;
						if (!isXML) {

							// Avoid an infinite loop by temporarily removing this function from the getter
							handle = attrHandle[name];
							attrHandle[name] = ret;
							ret = getter(elem, name, isXML) != null ? name.toLowerCase() : null;
							attrHandle[name] = handle;
						}
						return ret;
					};
				} else {
					attrHandle[name] = function (elem, name, isXML) {
						if (!isXML) {
							return elem[jQuery.camelCase("default-" + name)] ? name.toLowerCase() : null;
						}
					};
				}
			});

			// fix oldIE attroperties
			if (!getSetInput || !getSetAttribute) {
				jQuery.attrHooks.value = {
					set: function set(elem, value, name) {
						if (jQuery.nodeName(elem, "input")) {

							// Does not return so that setAttribute is also used
							elem.defaultValue = value;
						} else {

							// Use nodeHook if defined (#1954); otherwise setAttribute is fine
							return nodeHook && nodeHook.set(elem, value, name);
						}
					}
				};
			}

			// IE6/7 do not support getting/setting some attributes with get/setAttribute
			if (!getSetAttribute) {

				// Use this for any attribute in IE6/7
				// This fixes almost every IE6/7 issue
				nodeHook = {
					set: function set(elem, value, name) {

						// Set the existing or create a new attribute node
						var ret = elem.getAttributeNode(name);
						if (!ret) {
							elem.setAttributeNode(ret = elem.ownerDocument.createAttribute(name));
						}

						ret.value = value += "";

						// Break association with cloned elements by also using setAttribute (#9646)
						if (name === "value" || value === elem.getAttribute(name)) {
							return value;
						}
					}
				};

				// Some attributes are constructed with empty-string values when not defined
				attrHandle.id = attrHandle.name = attrHandle.coords = function (elem, name, isXML) {
					var ret;
					if (!isXML) {
						return (ret = elem.getAttributeNode(name)) && ret.value !== "" ? ret.value : null;
					}
				};

				// Fixing value retrieval on a button requires this module
				jQuery.valHooks.button = {
					get: function get(elem, name) {
						var ret = elem.getAttributeNode(name);
						if (ret && ret.specified) {
							return ret.value;
						}
					},
					set: nodeHook.set
				};

				// Set contenteditable to false on removals(#10429)
				// Setting to empty string throws an error as an invalid value
				jQuery.attrHooks.contenteditable = {
					set: function set(elem, value, name) {
						nodeHook.set(elem, value === "" ? false : value, name);
					}
				};

				// Set width and height to auto instead of 0 on empty string( Bug #8150 )
				// This is for removals
				jQuery.each(["width", "height"], function (i, name) {
					jQuery.attrHooks[name] = {
						set: function set(elem, value) {
							if (value === "") {
								elem.setAttribute(name, "auto");
								return value;
							}
						}
					};
				});
			}

			if (!support.style) {
				jQuery.attrHooks.style = {
					get: function get(elem) {

						// Return undefined in the case of empty string
						// Note: IE uppercases css property names, but if we were to .toLowerCase()
						// .cssText, that would destroy case sensitivity in URL's, like in "background"
						return elem.style.cssText || undefined;
					},
					set: function set(elem, value) {
						return elem.style.cssText = value + "";
					}
				};
			}

			var rfocusable = /^(?:input|select|textarea|button|object)$/i,
			    rclickable = /^(?:a|area)$/i;

			jQuery.fn.extend({
				prop: function prop(name, value) {
					return access(this, jQuery.prop, name, value, arguments.length > 1);
				},

				removeProp: function removeProp(name) {
					name = jQuery.propFix[name] || name;
					return this.each(function () {

						// try/catch handles cases where IE balks (such as removing a property on window)
						try {
							this[name] = undefined;
							delete this[name];
						} catch (e) {}
					});
				}
			});

			jQuery.extend({
				prop: function prop(elem, name, value) {
					var ret,
					    hooks,
					    nType = elem.nodeType;

					// Don't get/set properties on text, comment and attribute nodes
					if (nType === 3 || nType === 8 || nType === 2) {
						return;
					}

					if (nType !== 1 || !jQuery.isXMLDoc(elem)) {

						// Fix name and attach hooks
						name = jQuery.propFix[name] || name;
						hooks = jQuery.propHooks[name];
					}

					if (value !== undefined) {
						if (hooks && "set" in hooks && (ret = hooks.set(elem, value, name)) !== undefined) {
							return ret;
						}

						return elem[name] = value;
					}

					if (hooks && "get" in hooks && (ret = hooks.get(elem, name)) !== null) {
						return ret;
					}

					return elem[name];
				},

				propHooks: {
					tabIndex: {
						get: function get(elem) {

							// elem.tabIndex doesn't always return the
							// correct value when it hasn't been explicitly set
							// http://fluidproject.org/blog/2008/01/09/getting-setting-and-removing-tabindex-values-with-javascript/
							// Use proper attribute retrieval(#12072)
							var tabindex = jQuery.find.attr(elem, "tabindex");

							return tabindex ? parseInt(tabindex, 10) : rfocusable.test(elem.nodeName) || rclickable.test(elem.nodeName) && elem.href ? 0 : -1;
						}
					}
				},

				propFix: {
					"for": "htmlFor",
					"class": "className"
				}
			});

			// Some attributes require a special call on IE
			// http://msdn.microsoft.com/en-us/library/ms536429%28VS.85%29.aspx
			if (!support.hrefNormalized) {

				// href/src property should get the full normalized URL (#10299/#12915)
				jQuery.each(["href", "src"], function (i, name) {
					jQuery.propHooks[name] = {
						get: function get(elem) {
							return elem.getAttribute(name, 4);
						}
					};
				});
			}

			// Support: Safari, IE9+
			// Accessing the selectedIndex property
			// forces the browser to respect setting selected
			// on the option
			// The getter ensures a default option is selected
			// when in an optgroup
			if (!support.optSelected) {
				jQuery.propHooks.selected = {
					get: function get(elem) {
						var parent = elem.parentNode;

						if (parent) {
							parent.selectedIndex;

							// Make sure that it also works with optgroups, see #5701
							if (parent.parentNode) {
								parent.parentNode.selectedIndex;
							}
						}
						return null;
					},
					set: function set(elem) {
						var parent = elem.parentNode;
						if (parent) {
							parent.selectedIndex;

							if (parent.parentNode) {
								parent.parentNode.selectedIndex;
							}
						}
					}
				};
			}

			jQuery.each(["tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable"], function () {
				jQuery.propFix[this.toLowerCase()] = this;
			});

			// IE6/7 call enctype encoding
			if (!support.enctype) {
				jQuery.propFix.enctype = "encoding";
			}

			var rclass = /[\t\r\n\f]/g;

			function getClass(elem) {
				return jQuery.attr(elem, "class") || "";
			}

			jQuery.fn.extend({
				addClass: function addClass(value) {
					var classes,
					    elem,
					    cur,
					    curValue,
					    clazz,
					    j,
					    finalValue,
					    i = 0;

					if (jQuery.isFunction(value)) {
						return this.each(function (j) {
							jQuery(this).addClass(value.call(this, j, getClass(this)));
						});
					}

					if (typeof value === "string" && value) {
						classes = value.match(rnotwhite) || [];

						while (elem = this[i++]) {
							curValue = getClass(elem);
							cur = elem.nodeType === 1 && (" " + curValue + " ").replace(rclass, " ");

							if (cur) {
								j = 0;
								while (clazz = classes[j++]) {
									if (cur.indexOf(" " + clazz + " ") < 0) {
										cur += clazz + " ";
									}
								}

								// only assign if different to avoid unneeded rendering.
								finalValue = jQuery.trim(cur);
								if (curValue !== finalValue) {
									jQuery.attr(elem, "class", finalValue);
								}
							}
						}
					}

					return this;
				},

				removeClass: function removeClass(value) {
					var classes,
					    elem,
					    cur,
					    curValue,
					    clazz,
					    j,
					    finalValue,
					    i = 0;

					if (jQuery.isFunction(value)) {
						return this.each(function (j) {
							jQuery(this).removeClass(value.call(this, j, getClass(this)));
						});
					}

					if (!arguments.length) {
						return this.attr("class", "");
					}

					if (typeof value === "string" && value) {
						classes = value.match(rnotwhite) || [];

						while (elem = this[i++]) {
							curValue = getClass(elem);

							// This expression is here for better compressibility (see addClass)
							cur = elem.nodeType === 1 && (" " + curValue + " ").replace(rclass, " ");

							if (cur) {
								j = 0;
								while (clazz = classes[j++]) {

									// Remove *all* instances
									while (cur.indexOf(" " + clazz + " ") > -1) {
										cur = cur.replace(" " + clazz + " ", " ");
									}
								}

								// Only assign if different to avoid unneeded rendering.
								finalValue = jQuery.trim(cur);
								if (curValue !== finalValue) {
									jQuery.attr(elem, "class", finalValue);
								}
							}
						}
					}

					return this;
				},

				toggleClass: function toggleClass(value, stateVal) {
					var type = typeof value === "undefined" ? "undefined" : _typeof(value);

					if (typeof stateVal === "boolean" && type === "string") {
						return stateVal ? this.addClass(value) : this.removeClass(value);
					}

					if (jQuery.isFunction(value)) {
						return this.each(function (i) {
							jQuery(this).toggleClass(value.call(this, i, getClass(this), stateVal), stateVal);
						});
					}

					return this.each(function () {
						var className, i, self, classNames;

						if (type === "string") {

							// Toggle individual class names
							i = 0;
							self = jQuery(this);
							classNames = value.match(rnotwhite) || [];

							while (className = classNames[i++]) {

								// Check each className given, space separated list
								if (self.hasClass(className)) {
									self.removeClass(className);
								} else {
									self.addClass(className);
								}
							}

							// Toggle whole class name
						} else if (value === undefined || type === "boolean") {
							className = getClass(this);
							if (className) {

								// store className if set
								jQuery._data(this, "__className__", className);
							}

							// If the element has a class name or if we're passed "false",
							// then remove the whole classname (if there was one, the above saved it).
							// Otherwise bring back whatever was previously saved (if anything),
							// falling back to the empty string if nothing was stored.
							jQuery.attr(this, "class", className || value === false ? "" : jQuery._data(this, "__className__") || "");
						}
					});
				},

				hasClass: function hasClass(selector) {
					var className,
					    elem,
					    i = 0;

					className = " " + selector + " ";
					while (elem = this[i++]) {
						if (elem.nodeType === 1 && (" " + getClass(elem) + " ").replace(rclass, " ").indexOf(className) > -1) {
							return true;
						}
					}

					return false;
				}
			});

			// Return jQuery for attributes-only inclusion


			jQuery.each(("blur focus focusin focusout load resize scroll unload click dblclick " + "mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " + "change select submit keydown keypress keyup error contextmenu").split(" "), function (i, name) {

				// Handle event binding
				jQuery.fn[name] = function (data, fn) {
					return arguments.length > 0 ? this.on(name, null, data, fn) : this.trigger(name);
				};
			});

			jQuery.fn.extend({
				hover: function hover(fnOver, fnOut) {
					return this.mouseenter(fnOver).mouseleave(fnOut || fnOver);
				}
			});

			var location = window.location;

			var nonce = jQuery.now();

			var rquery = /\?/;

			var rvalidtokens = /(,)|(\[|{)|(}|])|"(?:[^"\\\r\n]|\\["\\\/bfnrt]|\\u[\da-fA-F]{4})*"\s*:?|true|false|null|-?(?!0\d)\d+(?:\.\d+|)(?:[eE][+-]?\d+|)/g;

			jQuery.parseJSON = function (data) {

				// Attempt to parse using the native JSON parser first
				if (window.JSON && window.JSON.parse) {

					// Support: Android 2.3
					// Workaround failure to string-cast null input
					return window.JSON.parse(data + "");
				}

				var requireNonComma,
				    depth = null,
				    str = jQuery.trim(data + "");

				// Guard against invalid (and possibly dangerous) input by ensuring that nothing remains
				// after removing valid tokens
				return str && !jQuery.trim(str.replace(rvalidtokens, function (token, comma, open, close) {

					// Force termination if we see a misplaced comma
					if (requireNonComma && comma) {
						depth = 0;
					}

					// Perform no more replacements after returning to outermost depth
					if (depth === 0) {
						return token;
					}

					// Commas must not follow "[", "{", or ","
					requireNonComma = open || comma;

					// Determine new depth
					// array/object open ("[" or "{"): depth += true - false (increment)
					// array/object close ("]" or "}"): depth += false - true (decrement)
					// other cases ("," or primitive): depth += true - true (numeric cast)
					depth += !close - !open;

					// Remove this token
					return "";
				})) ? Function("return " + str)() : jQuery.error("Invalid JSON: " + data);
			};

			// Cross-browser xml parsing
			jQuery.parseXML = function (data) {
				var xml, tmp;
				if (!data || typeof data !== "string") {
					return null;
				}
				try {
					if (window.DOMParser) {
						// Standard
						tmp = new window.DOMParser();
						xml = tmp.parseFromString(data, "text/xml");
					} else {
						// IE
						xml = new window.ActiveXObject("Microsoft.XMLDOM");
						xml.async = "false";
						xml.loadXML(data);
					}
				} catch (e) {
					xml = undefined;
				}
				if (!xml || !xml.documentElement || xml.getElementsByTagName("parsererror").length) {
					jQuery.error("Invalid XML: " + data);
				}
				return xml;
			};

			var rhash = /#.*$/,
			    rts = /([?&])_=[^&]*/,


			// IE leaves an \r character at EOL
			rheaders = /^(.*?):[ \t]*([^\r\n]*)\r?$/mg,


			// #7653, #8125, #8152: local protocol detection
			rlocalProtocol = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/,
			    rnoContent = /^(?:GET|HEAD)$/,
			    rprotocol = /^\/\//,
			    rurl = /^([\w.+-]+:)(?:\/\/(?:[^\/?#]*@|)([^\/?#:]*)(?::(\d+)|)|)/,


			/* Prefilters
	   * 1) They are useful to introduce custom dataTypes (see ajax/jsonp.js for an example)
	   * 2) These are called:
	   *    - BEFORE asking for a transport
	   *    - AFTER param serialization (s.data is a string if s.processData is true)
	   * 3) key is the dataType
	   * 4) the catchall symbol "*" can be used
	   * 5) execution will start with transport dataType and THEN continue down to "*" if needed
	   */
			prefilters = {},


			/* Transports bindings
	   * 1) key is the dataType
	   * 2) the catchall symbol "*" can be used
	   * 3) selection will start with transport dataType and THEN go to "*" if needed
	   */
			transports = {},


			// Avoid comment-prolog char sequence (#10098); must appease lint and evade compression
			allTypes = "*/".concat("*"),


			// Document location
			ajaxLocation = location.href,


			// Segment location into parts
			ajaxLocParts = rurl.exec(ajaxLocation.toLowerCase()) || [];

			// Base "constructor" for jQuery.ajaxPrefilter and jQuery.ajaxTransport
			function addToPrefiltersOrTransports(structure) {

				// dataTypeExpression is optional and defaults to "*"
				return function (dataTypeExpression, func) {

					if (typeof dataTypeExpression !== "string") {
						func = dataTypeExpression;
						dataTypeExpression = "*";
					}

					var dataType,
					    i = 0,
					    dataTypes = dataTypeExpression.toLowerCase().match(rnotwhite) || [];

					if (jQuery.isFunction(func)) {

						// For each dataType in the dataTypeExpression
						while (dataType = dataTypes[i++]) {

							// Prepend if requested
							if (dataType.charAt(0) === "+") {
								dataType = dataType.slice(1) || "*";
								(structure[dataType] = structure[dataType] || []).unshift(func);

								// Otherwise append
							} else {
								(structure[dataType] = structure[dataType] || []).push(func);
							}
						}
					}
				};
			}

			// Base inspection function for prefilters and transports
			function inspectPrefiltersOrTransports(structure, options, originalOptions, jqXHR) {

				var inspected = {},
				    seekingTransport = structure === transports;

				function inspect(dataType) {
					var selected;
					inspected[dataType] = true;
					jQuery.each(structure[dataType] || [], function (_, prefilterOrFactory) {
						var dataTypeOrTransport = prefilterOrFactory(options, originalOptions, jqXHR);
						if (typeof dataTypeOrTransport === "string" && !seekingTransport && !inspected[dataTypeOrTransport]) {

							options.dataTypes.unshift(dataTypeOrTransport);
							inspect(dataTypeOrTransport);
							return false;
						} else if (seekingTransport) {
							return !(selected = dataTypeOrTransport);
						}
					});
					return selected;
				}

				return inspect(options.dataTypes[0]) || !inspected["*"] && inspect("*");
			}

			// A special extend for ajax options
			// that takes "flat" options (not to be deep extended)
			// Fixes #9887
			function ajaxExtend(target, src) {
				var deep,
				    key,
				    flatOptions = jQuery.ajaxSettings.flatOptions || {};

				for (key in src) {
					if (src[key] !== undefined) {
						(flatOptions[key] ? target : deep || (deep = {}))[key] = src[key];
					}
				}
				if (deep) {
					jQuery.extend(true, target, deep);
				}

				return target;
			}

			/* Handles responses to an ajax request:
	   * - finds the right dataType (mediates between content-type and expected dataType)
	   * - returns the corresponding response
	   */
			function ajaxHandleResponses(s, jqXHR, responses) {
				var firstDataType,
				    ct,
				    finalDataType,
				    type,
				    contents = s.contents,
				    dataTypes = s.dataTypes;

				// Remove auto dataType and get content-type in the process
				while (dataTypes[0] === "*") {
					dataTypes.shift();
					if (ct === undefined) {
						ct = s.mimeType || jqXHR.getResponseHeader("Content-Type");
					}
				}

				// Check if we're dealing with a known content-type
				if (ct) {
					for (type in contents) {
						if (contents[type] && contents[type].test(ct)) {
							dataTypes.unshift(type);
							break;
						}
					}
				}

				// Check to see if we have a response for the expected dataType
				if (dataTypes[0] in responses) {
					finalDataType = dataTypes[0];
				} else {

					// Try convertible dataTypes
					for (type in responses) {
						if (!dataTypes[0] || s.converters[type + " " + dataTypes[0]]) {
							finalDataType = type;
							break;
						}
						if (!firstDataType) {
							firstDataType = type;
						}
					}

					// Or just use first one
					finalDataType = finalDataType || firstDataType;
				}

				// If we found a dataType
				// We add the dataType to the list if needed
				// and return the corresponding response
				if (finalDataType) {
					if (finalDataType !== dataTypes[0]) {
						dataTypes.unshift(finalDataType);
					}
					return responses[finalDataType];
				}
			}

			/* Chain conversions given the request and the original response
	   * Also sets the responseXXX fields on the jqXHR instance
	   */
			function ajaxConvert(s, response, jqXHR, isSuccess) {
				var conv2,
				    current,
				    conv,
				    tmp,
				    prev,
				    converters = {},


				// Work with a copy of dataTypes in case we need to modify it for conversion
				dataTypes = s.dataTypes.slice();

				// Create converters map with lowercased keys
				if (dataTypes[1]) {
					for (conv in s.converters) {
						converters[conv.toLowerCase()] = s.converters[conv];
					}
				}

				current = dataTypes.shift();

				// Convert to each sequential dataType
				while (current) {

					if (s.responseFields[current]) {
						jqXHR[s.responseFields[current]] = response;
					}

					// Apply the dataFilter if provided
					if (!prev && isSuccess && s.dataFilter) {
						response = s.dataFilter(response, s.dataType);
					}

					prev = current;
					current = dataTypes.shift();

					if (current) {

						// There's only work to do if current dataType is non-auto
						if (current === "*") {

							current = prev;

							// Convert response if prev dataType is non-auto and differs from current
						} else if (prev !== "*" && prev !== current) {

							// Seek a direct converter
							conv = converters[prev + " " + current] || converters["* " + current];

							// If none found, seek a pair
							if (!conv) {
								for (conv2 in converters) {

									// If conv2 outputs current
									tmp = conv2.split(" ");
									if (tmp[1] === current) {

										// If prev can be converted to accepted input
										conv = converters[prev + " " + tmp[0]] || converters["* " + tmp[0]];
										if (conv) {

											// Condense equivalence converters
											if (conv === true) {
												conv = converters[conv2];

												// Otherwise, insert the intermediate dataType
											} else if (converters[conv2] !== true) {
												current = tmp[0];
												dataTypes.unshift(tmp[1]);
											}
											break;
										}
									}
								}
							}

							// Apply converter (if not an equivalence)
							if (conv !== true) {

								// Unless errors are allowed to bubble, catch and return them
								if (conv && s["throws"]) {
									// jscs:ignore requireDotNotation
									response = conv(response);
								} else {
									try {
										response = conv(response);
									} catch (e) {
										return {
											state: "parsererror",
											error: conv ? e : "No conversion from " + prev + " to " + current
										};
									}
								}
							}
						}
					}
				}

				return { state: "success", data: response };
			}

			jQuery.extend({

				// Counter for holding the number of active queries
				active: 0,

				// Last-Modified header cache for next request
				lastModified: {},
				etag: {},

				ajaxSettings: {
					url: ajaxLocation,
					type: "GET",
					isLocal: rlocalProtocol.test(ajaxLocParts[1]),
					global: true,
					processData: true,
					async: true,
					contentType: "application/x-www-form-urlencoded; charset=UTF-8",
					/*
	    timeout: 0,
	    data: null,
	    dataType: null,
	    username: null,
	    password: null,
	    cache: null,
	    throws: false,
	    traditional: false,
	    headers: {},
	    */

					accepts: {
						"*": allTypes,
						text: "text/plain",
						html: "text/html",
						xml: "application/xml, text/xml",
						json: "application/json, text/javascript"
					},

					contents: {
						xml: /\bxml\b/,
						html: /\bhtml/,
						json: /\bjson\b/
					},

					responseFields: {
						xml: "responseXML",
						text: "responseText",
						json: "responseJSON"
					},

					// Data converters
					// Keys separate source (or catchall "*") and destination types with a single space
					converters: {

						// Convert anything to text
						"* text": String,

						// Text to html (true = no transformation)
						"text html": true,

						// Evaluate text as a json expression
						"text json": jQuery.parseJSON,

						// Parse text as xml
						"text xml": jQuery.parseXML
					},

					// For options that shouldn't be deep extended:
					// you can add your own custom options here if
					// and when you create one that shouldn't be
					// deep extended (see ajaxExtend)
					flatOptions: {
						url: true,
						context: true
					}
				},

				// Creates a full fledged settings object into target
				// with both ajaxSettings and settings fields.
				// If target is omitted, writes into ajaxSettings.
				ajaxSetup: function ajaxSetup(target, settings) {
					return settings ?

					// Building a settings object
					ajaxExtend(ajaxExtend(target, jQuery.ajaxSettings), settings) :

					// Extending ajaxSettings
					ajaxExtend(jQuery.ajaxSettings, target);
				},

				ajaxPrefilter: addToPrefiltersOrTransports(prefilters),
				ajaxTransport: addToPrefiltersOrTransports(transports),

				// Main method
				ajax: function ajax(url, options) {

					// If url is an object, simulate pre-1.5 signature
					if ((typeof url === "undefined" ? "undefined" : _typeof(url)) === "object") {
						options = url;
						url = undefined;
					}

					// Force options to be an object
					options = options || {};

					var

					// Cross-domain detection vars
					parts,


					// Loop variable
					i,


					// URL without anti-cache param
					cacheURL,


					// Response headers as string
					responseHeadersString,


					// timeout handle
					timeoutTimer,


					// To know if global events are to be dispatched
					fireGlobals,
					    transport,


					// Response headers
					responseHeaders,


					// Create the final options object
					s = jQuery.ajaxSetup({}, options),


					// Callbacks context
					callbackContext = s.context || s,


					// Context for global events is callbackContext if it is a DOM node or jQuery collection
					globalEventContext = s.context && (callbackContext.nodeType || callbackContext.jquery) ? jQuery(callbackContext) : jQuery.event,


					// Deferreds
					deferred = jQuery.Deferred(),
					    completeDeferred = jQuery.Callbacks("once memory"),


					// Status-dependent callbacks
					_statusCode = s.statusCode || {},


					// Headers (they are sent all at once)
					requestHeaders = {},
					    requestHeadersNames = {},


					// The jqXHR state
					state = 0,


					// Default abort message
					strAbort = "canceled",


					// Fake xhr
					jqXHR = {
						readyState: 0,

						// Builds headers hashtable if needed
						getResponseHeader: function getResponseHeader(key) {
							var match;
							if (state === 2) {
								if (!responseHeaders) {
									responseHeaders = {};
									while (match = rheaders.exec(responseHeadersString)) {
										responseHeaders[match[1].toLowerCase()] = match[2];
									}
								}
								match = responseHeaders[key.toLowerCase()];
							}
							return match == null ? null : match;
						},

						// Raw string
						getAllResponseHeaders: function getAllResponseHeaders() {
							return state === 2 ? responseHeadersString : null;
						},

						// Caches the header
						setRequestHeader: function setRequestHeader(name, value) {
							var lname = name.toLowerCase();
							if (!state) {
								name = requestHeadersNames[lname] = requestHeadersNames[lname] || name;
								requestHeaders[name] = value;
							}
							return this;
						},

						// Overrides response content-type header
						overrideMimeType: function overrideMimeType(type) {
							if (!state) {
								s.mimeType = type;
							}
							return this;
						},

						// Status-dependent callbacks
						statusCode: function statusCode(map) {
							var code;
							if (map) {
								if (state < 2) {
									for (code in map) {

										// Lazy-add the new callback in a way that preserves old ones
										_statusCode[code] = [_statusCode[code], map[code]];
									}
								} else {

									// Execute the appropriate callbacks
									jqXHR.always(map[jqXHR.status]);
								}
							}
							return this;
						},

						// Cancel the request
						abort: function abort(statusText) {
							var finalText = statusText || strAbort;
							if (transport) {
								transport.abort(finalText);
							}
							done(0, finalText);
							return this;
						}
					};

					// Attach deferreds
					deferred.promise(jqXHR).complete = completeDeferred.add;
					jqXHR.success = jqXHR.done;
					jqXHR.error = jqXHR.fail;

					// Remove hash character (#7531: and string promotion)
					// Add protocol if not provided (#5866: IE7 issue with protocol-less urls)
					// Handle falsy url in the settings object (#10093: consistency with old signature)
					// We also use the url parameter if available
					s.url = ((url || s.url || ajaxLocation) + "").replace(rhash, "").replace(rprotocol, ajaxLocParts[1] + "//");

					// Alias method option to type as per ticket #12004
					s.type = options.method || options.type || s.method || s.type;

					// Extract dataTypes list
					s.dataTypes = jQuery.trim(s.dataType || "*").toLowerCase().match(rnotwhite) || [""];

					// A cross-domain request is in order when we have a protocol:host:port mismatch
					if (s.crossDomain == null) {
						parts = rurl.exec(s.url.toLowerCase());
						s.crossDomain = !!(parts && (parts[1] !== ajaxLocParts[1] || parts[2] !== ajaxLocParts[2] || (parts[3] || (parts[1] === "http:" ? "80" : "443")) !== (ajaxLocParts[3] || (ajaxLocParts[1] === "http:" ? "80" : "443"))));
					}

					// Convert data if not already a string
					if (s.data && s.processData && typeof s.data !== "string") {
						s.data = jQuery.param(s.data, s.traditional);
					}

					// Apply prefilters
					inspectPrefiltersOrTransports(prefilters, s, options, jqXHR);

					// If request was aborted inside a prefilter, stop there
					if (state === 2) {
						return jqXHR;
					}

					// We can fire global events as of now if asked to
					// Don't fire events if jQuery.event is undefined in an AMD-usage scenario (#15118)
					fireGlobals = jQuery.event && s.global;

					// Watch for a new set of requests
					if (fireGlobals && jQuery.active++ === 0) {
						jQuery.event.trigger("ajaxStart");
					}

					// Uppercase the type
					s.type = s.type.toUpperCase();

					// Determine if request has content
					s.hasContent = !rnoContent.test(s.type);

					// Save the URL in case we're toying with the If-Modified-Since
					// and/or If-None-Match header later on
					cacheURL = s.url;

					// More options handling for requests with no content
					if (!s.hasContent) {

						// If data is available, append data to url
						if (s.data) {
							cacheURL = s.url += (rquery.test(cacheURL) ? "&" : "?") + s.data;

							// #9682: remove data so that it's not used in an eventual retry
							delete s.data;
						}

						// Add anti-cache in url if needed
						if (s.cache === false) {
							s.url = rts.test(cacheURL) ?

							// If there is already a '_' parameter, set its value
							cacheURL.replace(rts, "$1_=" + nonce++) :

							// Otherwise add one to the end
							cacheURL + (rquery.test(cacheURL) ? "&" : "?") + "_=" + nonce++;
						}
					}

					// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
					if (s.ifModified) {
						if (jQuery.lastModified[cacheURL]) {
							jqXHR.setRequestHeader("If-Modified-Since", jQuery.lastModified[cacheURL]);
						}
						if (jQuery.etag[cacheURL]) {
							jqXHR.setRequestHeader("If-None-Match", jQuery.etag[cacheURL]);
						}
					}

					// Set the correct header, if data is being sent
					if (s.data && s.hasContent && s.contentType !== false || options.contentType) {
						jqXHR.setRequestHeader("Content-Type", s.contentType);
					}

					// Set the Accepts header for the server, depending on the dataType
					jqXHR.setRequestHeader("Accept", s.dataTypes[0] && s.accepts[s.dataTypes[0]] ? s.accepts[s.dataTypes[0]] + (s.dataTypes[0] !== "*" ? ", " + allTypes + "; q=0.01" : "") : s.accepts["*"]);

					// Check for headers option
					for (i in s.headers) {
						jqXHR.setRequestHeader(i, s.headers[i]);
					}

					// Allow custom headers/mimetypes and early abort
					if (s.beforeSend && (s.beforeSend.call(callbackContext, jqXHR, s) === false || state === 2)) {

						// Abort if not done already and return
						return jqXHR.abort();
					}

					// aborting is no longer a cancellation
					strAbort = "abort";

					// Install callbacks on deferreds
					for (i in { success: 1, error: 1, complete: 1 }) {
						jqXHR[i](s[i]);
					}

					// Get transport
					transport = inspectPrefiltersOrTransports(transports, s, options, jqXHR);

					// If no transport, we auto-abort
					if (!transport) {
						done(-1, "No Transport");
					} else {
						jqXHR.readyState = 1;

						// Send global event
						if (fireGlobals) {
							globalEventContext.trigger("ajaxSend", [jqXHR, s]);
						}

						// If request was aborted inside ajaxSend, stop there
						if (state === 2) {
							return jqXHR;
						}

						// Timeout
						if (s.async && s.timeout > 0) {
							timeoutTimer = window.setTimeout(function () {
								jqXHR.abort("timeout");
							}, s.timeout);
						}

						try {
							state = 1;
							transport.send(requestHeaders, done);
						} catch (e) {

							// Propagate exception as error if not done
							if (state < 2) {
								done(-1, e);

								// Simply rethrow otherwise
							} else {
								throw e;
							}
						}
					}

					// Callback for when everything is done
					function done(status, nativeStatusText, responses, headers) {
						var isSuccess,
						    success,
						    error,
						    response,
						    modified,
						    statusText = nativeStatusText;

						// Called once
						if (state === 2) {
							return;
						}

						// State is "done" now
						state = 2;

						// Clear timeout if it exists
						if (timeoutTimer) {
							window.clearTimeout(timeoutTimer);
						}

						// Dereference transport for early garbage collection
						// (no matter how long the jqXHR object will be used)
						transport = undefined;

						// Cache response headers
						responseHeadersString = headers || "";

						// Set readyState
						jqXHR.readyState = status > 0 ? 4 : 0;

						// Determine if successful
						isSuccess = status >= 200 && status < 300 || status === 304;

						// Get response data
						if (responses) {
							response = ajaxHandleResponses(s, jqXHR, responses);
						}

						// Convert no matter what (that way responseXXX fields are always set)
						response = ajaxConvert(s, response, jqXHR, isSuccess);

						// If successful, handle type chaining
						if (isSuccess) {

							// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
							if (s.ifModified) {
								modified = jqXHR.getResponseHeader("Last-Modified");
								if (modified) {
									jQuery.lastModified[cacheURL] = modified;
								}
								modified = jqXHR.getResponseHeader("etag");
								if (modified) {
									jQuery.etag[cacheURL] = modified;
								}
							}

							// if no content
							if (status === 204 || s.type === "HEAD") {
								statusText = "nocontent";

								// if not modified
							} else if (status === 304) {
								statusText = "notmodified";

								// If we have data, let's convert it
							} else {
								statusText = response.state;
								success = response.data;
								error = response.error;
								isSuccess = !error;
							}
						} else {

							// We extract error from statusText
							// then normalize statusText and status for non-aborts
							error = statusText;
							if (status || !statusText) {
								statusText = "error";
								if (status < 0) {
									status = 0;
								}
							}
						}

						// Set data for the fake xhr object
						jqXHR.status = status;
						jqXHR.statusText = (nativeStatusText || statusText) + "";

						// Success/Error
						if (isSuccess) {
							deferred.resolveWith(callbackContext, [success, statusText, jqXHR]);
						} else {
							deferred.rejectWith(callbackContext, [jqXHR, statusText, error]);
						}

						// Status-dependent callbacks
						jqXHR.statusCode(_statusCode);
						_statusCode = undefined;

						if (fireGlobals) {
							globalEventContext.trigger(isSuccess ? "ajaxSuccess" : "ajaxError", [jqXHR, s, isSuccess ? success : error]);
						}

						// Complete
						completeDeferred.fireWith(callbackContext, [jqXHR, statusText]);

						if (fireGlobals) {
							globalEventContext.trigger("ajaxComplete", [jqXHR, s]);

							// Handle the global AJAX counter
							if (! --jQuery.active) {
								jQuery.event.trigger("ajaxStop");
							}
						}
					}

					return jqXHR;
				},

				getJSON: function getJSON(url, data, callback) {
					return jQuery.get(url, data, callback, "json");
				},

				getScript: function getScript(url, callback) {
					return jQuery.get(url, undefined, callback, "script");
				}
			});

			jQuery.each(["get", "post"], function (i, method) {
				jQuery[method] = function (url, data, callback, type) {

					// shift arguments if data argument was omitted
					if (jQuery.isFunction(data)) {
						type = type || callback;
						callback = data;
						data = undefined;
					}

					// The url can be an options object (which then must have .url)
					return jQuery.ajax(jQuery.extend({
						url: url,
						type: method,
						dataType: type,
						data: data,
						success: callback
					}, jQuery.isPlainObject(url) && url));
				};
			});

			jQuery._evalUrl = function (url) {
				return jQuery.ajax({
					url: url,

					// Make this explicit, since user can override this through ajaxSetup (#11264)
					type: "GET",
					dataType: "script",
					cache: true,
					async: false,
					global: false,
					"throws": true
				});
			};

			jQuery.fn.extend({
				wrapAll: function wrapAll(html) {
					if (jQuery.isFunction(html)) {
						return this.each(function (i) {
							jQuery(this).wrapAll(html.call(this, i));
						});
					}

					if (this[0]) {

						// The elements to wrap the target around
						var wrap = jQuery(html, this[0].ownerDocument).eq(0).clone(true);

						if (this[0].parentNode) {
							wrap.insertBefore(this[0]);
						}

						wrap.map(function () {
							var elem = this;

							while (elem.firstChild && elem.firstChild.nodeType === 1) {
								elem = elem.firstChild;
							}

							return elem;
						}).append(this);
					}

					return this;
				},

				wrapInner: function wrapInner(html) {
					if (jQuery.isFunction(html)) {
						return this.each(function (i) {
							jQuery(this).wrapInner(html.call(this, i));
						});
					}

					return this.each(function () {
						var self = jQuery(this),
						    contents = self.contents();

						if (contents.length) {
							contents.wrapAll(html);
						} else {
							self.append(html);
						}
					});
				},

				wrap: function wrap(html) {
					var isFunction = jQuery.isFunction(html);

					return this.each(function (i) {
						jQuery(this).wrapAll(isFunction ? html.call(this, i) : html);
					});
				},

				unwrap: function unwrap() {
					return this.parent().each(function () {
						if (!jQuery.nodeName(this, "body")) {
							jQuery(this).replaceWith(this.childNodes);
						}
					}).end();
				}
			});

			function getDisplay(elem) {
				return elem.style && elem.style.display || jQuery.css(elem, "display");
			}

			function filterHidden(elem) {

				// Disconnected elements are considered hidden
				if (!jQuery.contains(elem.ownerDocument || document, elem)) {
					return true;
				}
				while (elem && elem.nodeType === 1) {
					if (getDisplay(elem) === "none" || elem.type === "hidden") {
						return true;
					}
					elem = elem.parentNode;
				}
				return false;
			}

			jQuery.expr.filters.hidden = function (elem) {

				// Support: Opera <= 12.12
				// Opera reports offsetWidths and offsetHeights less than zero on some elements
				return support.reliableHiddenOffsets() ? elem.offsetWidth <= 0 && elem.offsetHeight <= 0 && !elem.getClientRects().length : filterHidden(elem);
			};

			jQuery.expr.filters.visible = function (elem) {
				return !jQuery.expr.filters.hidden(elem);
			};

			var r20 = /%20/g,
			    rbracket = /\[\]$/,
			    rCRLF = /\r?\n/g,
			    rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i,
			    rsubmittable = /^(?:input|select|textarea|keygen)/i;

			function buildParams(prefix, obj, traditional, add) {
				var name;

				if (jQuery.isArray(obj)) {

					// Serialize array item.
					jQuery.each(obj, function (i, v) {
						if (traditional || rbracket.test(prefix)) {

							// Treat each array item as a scalar.
							add(prefix, v);
						} else {

							// Item is non-scalar (array or object), encode its numeric index.
							buildParams(prefix + "[" + ((typeof v === "undefined" ? "undefined" : _typeof(v)) === "object" && v != null ? i : "") + "]", v, traditional, add);
						}
					});
				} else if (!traditional && jQuery.type(obj) === "object") {

					// Serialize object item.
					for (name in obj) {
						buildParams(prefix + "[" + name + "]", obj[name], traditional, add);
					}
				} else {

					// Serialize scalar item.
					add(prefix, obj);
				}
			}

			// Serialize an array of form elements or a set of
			// key/values into a query string
			jQuery.param = function (a, traditional) {
				var prefix,
				    s = [],
				    add = function add(key, value) {

					// If value is a function, invoke it and return its value
					value = jQuery.isFunction(value) ? value() : value == null ? "" : value;
					s[s.length] = encodeURIComponent(key) + "=" + encodeURIComponent(value);
				};

				// Set traditional to true for jQuery <= 1.3.2 behavior.
				if (traditional === undefined) {
					traditional = jQuery.ajaxSettings && jQuery.ajaxSettings.traditional;
				}

				// If an array was passed in, assume that it is an array of form elements.
				if (jQuery.isArray(a) || a.jquery && !jQuery.isPlainObject(a)) {

					// Serialize the form elements
					jQuery.each(a, function () {
						add(this.name, this.value);
					});
				} else {

					// If traditional, encode the "old" way (the way 1.3.2 or older
					// did it), otherwise encode params recursively.
					for (prefix in a) {
						buildParams(prefix, a[prefix], traditional, add);
					}
				}

				// Return the resulting serialization
				return s.join("&").replace(r20, "+");
			};

			jQuery.fn.extend({
				serialize: function serialize() {
					return jQuery.param(this.serializeArray());
				},
				serializeArray: function serializeArray() {
					return this.map(function () {

						// Can add propHook for "elements" to filter or add form elements
						var elements = jQuery.prop(this, "elements");
						return elements ? jQuery.makeArray(elements) : this;
					}).filter(function () {
						var type = this.type;

						// Use .is(":disabled") so that fieldset[disabled] works
						return this.name && !jQuery(this).is(":disabled") && rsubmittable.test(this.nodeName) && !rsubmitterTypes.test(type) && (this.checked || !rcheckableType.test(type));
					}).map(function (i, elem) {
						var val = jQuery(this).val();

						return val == null ? null : jQuery.isArray(val) ? jQuery.map(val, function (val) {
							return { name: elem.name, value: val.replace(rCRLF, "\r\n") };
						}) : { name: elem.name, value: val.replace(rCRLF, "\r\n") };
					}).get();
				}
			});

			// Create the request object
			// (This is still attached to ajaxSettings for backward compatibility)
			jQuery.ajaxSettings.xhr = window.ActiveXObject !== undefined ?

			// Support: IE6-IE8
			function () {

				// XHR cannot access local files, always use ActiveX for that case
				if (this.isLocal) {
					return createActiveXHR();
				}

				// Support: IE 9-11
				// IE seems to error on cross-domain PATCH requests when ActiveX XHR
				// is used. In IE 9+ always use the native XHR.
				// Note: this condition won't catch Edge as it doesn't define
				// document.documentMode but it also doesn't support ActiveX so it won't
				// reach this code.
				if (document.documentMode > 8) {
					return createStandardXHR();
				}

				// Support: IE<9
				// oldIE XHR does not support non-RFC2616 methods (#13240)
				// See http://msdn.microsoft.com/en-us/library/ie/ms536648(v=vs.85).aspx
				// and http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9
				// Although this check for six methods instead of eight
				// since IE also does not support "trace" and "connect"
				return (/^(get|post|head|put|delete|options)$/i.test(this.type) && createStandardXHR() || createActiveXHR()
				);
			} :

			// For all other browsers, use the standard XMLHttpRequest object
			createStandardXHR;

			var xhrId = 0,
			    xhrCallbacks = {},
			    xhrSupported = jQuery.ajaxSettings.xhr();

			// Support: IE<10
			// Open requests must be manually aborted on unload (#5280)
			// See https://support.microsoft.com/kb/2856746 for more info
			if (window.attachEvent) {
				window.attachEvent("onunload", function () {
					for (var key in xhrCallbacks) {
						xhrCallbacks[key](undefined, true);
					}
				});
			}

			// Determine support properties
			support.cors = !!xhrSupported && "withCredentials" in xhrSupported;
			xhrSupported = support.ajax = !!xhrSupported;

			// Create transport if the browser can provide an xhr
			if (xhrSupported) {

				jQuery.ajaxTransport(function (options) {

					// Cross domain only allowed if supported through XMLHttpRequest
					if (!options.crossDomain || support.cors) {

						var _callback;

						return {
							send: function send(headers, complete) {
								var i,
								    xhr = options.xhr(),
								    id = ++xhrId;

								// Open the socket
								xhr.open(options.type, options.url, options.async, options.username, options.password);

								// Apply custom fields if provided
								if (options.xhrFields) {
									for (i in options.xhrFields) {
										xhr[i] = options.xhrFields[i];
									}
								}

								// Override mime type if needed
								if (options.mimeType && xhr.overrideMimeType) {
									xhr.overrideMimeType(options.mimeType);
								}

								// X-Requested-With header
								// For cross-domain requests, seeing as conditions for a preflight are
								// akin to a jigsaw puzzle, we simply never set it to be sure.
								// (it can always be set on a per-request basis or even using ajaxSetup)
								// For same-domain requests, won't change header if already provided.
								if (!options.crossDomain && !headers["X-Requested-With"]) {
									headers["X-Requested-With"] = "XMLHttpRequest";
								}

								// Set headers
								for (i in headers) {

									// Support: IE<9
									// IE's ActiveXObject throws a 'Type Mismatch' exception when setting
									// request header to a null-value.
									//
									// To keep consistent with other XHR implementations, cast the value
									// to string and ignore `undefined`.
									if (headers[i] !== undefined) {
										xhr.setRequestHeader(i, headers[i] + "");
									}
								}

								// Do send the request
								// This may raise an exception which is actually
								// handled in jQuery.ajax (so no try/catch here)
								xhr.send(options.hasContent && options.data || null);

								// Listener
								_callback = function callback(_, isAbort) {
									var status, statusText, responses;

									// Was never called and is aborted or complete
									if (_callback && (isAbort || xhr.readyState === 4)) {

										// Clean up
										delete xhrCallbacks[id];
										_callback = undefined;
										xhr.onreadystatechange = jQuery.noop;

										// Abort manually if needed
										if (isAbort) {
											if (xhr.readyState !== 4) {
												xhr.abort();
											}
										} else {
											responses = {};
											status = xhr.status;

											// Support: IE<10
											// Accessing binary-data responseText throws an exception
											// (#11426)
											if (typeof xhr.responseText === "string") {
												responses.text = xhr.responseText;
											}

											// Firefox throws an exception when accessing
											// statusText for faulty cross-domain requests
											try {
												statusText = xhr.statusText;
											} catch (e) {

												// We normalize with Webkit giving an empty statusText
												statusText = "";
											}

											// Filter status for non standard behaviors

											// If the request is local and we have data: assume a success
											// (success with no data won't get notified, that's the best we
											// can do given current implementations)
											if (!status && options.isLocal && !options.crossDomain) {
												status = responses.text ? 200 : 404;

												// IE - #1450: sometimes returns 1223 when it should be 204
											} else if (status === 1223) {
												status = 204;
											}
										}
									}

									// Call complete if needed
									if (responses) {
										complete(status, statusText, responses, xhr.getAllResponseHeaders());
									}
								};

								// Do send the request
								// `xhr.send` may raise an exception, but it will be
								// handled in jQuery.ajax (so no try/catch here)
								if (!options.async) {

									// If we're in sync mode we fire the callback
									_callback();
								} else if (xhr.readyState === 4) {

									// (IE6 & IE7) if it's in cache and has been
									// retrieved directly we need to fire the callback
									window.setTimeout(_callback);
								} else {

									// Register the callback, but delay it in case `xhr.send` throws
									// Add to the list of active xhr callbacks
									xhr.onreadystatechange = xhrCallbacks[id] = _callback;
								}
							},

							abort: function abort() {
								if (_callback) {
									_callback(undefined, true);
								}
							}
						};
					}
				});
			}

			// Functions to create xhrs
			function createStandardXHR() {
				try {
					return new window.XMLHttpRequest();
				} catch (e) {}
			}

			function createActiveXHR() {
				try {
					return new window.ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {}
			}

			// Install script dataType
			jQuery.ajaxSetup({
				accepts: {
					script: "text/javascript, application/javascript, " + "application/ecmascript, application/x-ecmascript"
				},
				contents: {
					script: /\b(?:java|ecma)script\b/
				},
				converters: {
					"text script": function textScript(text) {
						jQuery.globalEval(text);
						return text;
					}
				}
			});

			// Handle cache's special case and global
			jQuery.ajaxPrefilter("script", function (s) {
				if (s.cache === undefined) {
					s.cache = false;
				}
				if (s.crossDomain) {
					s.type = "GET";
					s.global = false;
				}
			});

			// Bind script tag hack transport
			jQuery.ajaxTransport("script", function (s) {

				// This transport only deals with cross domain requests
				if (s.crossDomain) {

					var script,
					    head = document.head || jQuery("head")[0] || document.documentElement;

					return {

						send: function send(_, callback) {

							script = document.createElement("script");

							script.async = true;

							if (s.scriptCharset) {
								script.charset = s.scriptCharset;
							}

							script.src = s.url;

							// Attach handlers for all browsers
							script.onload = script.onreadystatechange = function (_, isAbort) {

								if (isAbort || !script.readyState || /loaded|complete/.test(script.readyState)) {

									// Handle memory leak in IE
									script.onload = script.onreadystatechange = null;

									// Remove the script
									if (script.parentNode) {
										script.parentNode.removeChild(script);
									}

									// Dereference the script
									script = null;

									// Callback if not abort
									if (!isAbort) {
										callback(200, "success");
									}
								}
							};

							// Circumvent IE6 bugs with base elements (#2709 and #4378) by prepending
							// Use native DOM manipulation to avoid our domManip AJAX trickery
							head.insertBefore(script, head.firstChild);
						},

						abort: function abort() {
							if (script) {
								script.onload(undefined, true);
							}
						}
					};
				}
			});

			var oldCallbacks = [],
			    rjsonp = /(=)\?(?=&|$)|\?\?/;

			// Default jsonp settings
			jQuery.ajaxSetup({
				jsonp: "callback",
				jsonpCallback: function jsonpCallback() {
					var callback = oldCallbacks.pop() || jQuery.expando + "_" + nonce++;
					this[callback] = true;
					return callback;
				}
			});

			// Detect, normalize options and install callbacks for jsonp requests
			jQuery.ajaxPrefilter("json jsonp", function (s, originalSettings, jqXHR) {

				var callbackName,
				    overwritten,
				    responseContainer,
				    jsonProp = s.jsonp !== false && (rjsonp.test(s.url) ? "url" : typeof s.data === "string" && (s.contentType || "").indexOf("application/x-www-form-urlencoded") === 0 && rjsonp.test(s.data) && "data");

				// Handle iff the expected data type is "jsonp" or we have a parameter to set
				if (jsonProp || s.dataTypes[0] === "jsonp") {

					// Get callback name, remembering preexisting value associated with it
					callbackName = s.jsonpCallback = jQuery.isFunction(s.jsonpCallback) ? s.jsonpCallback() : s.jsonpCallback;

					// Insert callback into url or form data
					if (jsonProp) {
						s[jsonProp] = s[jsonProp].replace(rjsonp, "$1" + callbackName);
					} else if (s.jsonp !== false) {
						s.url += (rquery.test(s.url) ? "&" : "?") + s.jsonp + "=" + callbackName;
					}

					// Use data converter to retrieve json after script execution
					s.converters["script json"] = function () {
						if (!responseContainer) {
							jQuery.error(callbackName + " was not called");
						}
						return responseContainer[0];
					};

					// force json dataType
					s.dataTypes[0] = "json";

					// Install callback
					overwritten = window[callbackName];
					window[callbackName] = function () {
						responseContainer = arguments;
					};

					// Clean-up function (fires after converters)
					jqXHR.always(function () {

						// If previous value didn't exist - remove it
						if (overwritten === undefined) {
							jQuery(window).removeProp(callbackName);

							// Otherwise restore preexisting value
						} else {
							window[callbackName] = overwritten;
						}

						// Save back as free
						if (s[callbackName]) {

							// make sure that re-using the options doesn't screw things around
							s.jsonpCallback = originalSettings.jsonpCallback;

							// save the callback name for future use
							oldCallbacks.push(callbackName);
						}

						// Call if it was a function and we have a response
						if (responseContainer && jQuery.isFunction(overwritten)) {
							overwritten(responseContainer[0]);
						}

						responseContainer = overwritten = undefined;
					});

					// Delegate to script
					return "script";
				}
			});

			// data: string of html
			// context (optional): If specified, the fragment will be created in this context,
			// defaults to document
			// keepScripts (optional): If true, will include scripts passed in the html string
			jQuery.parseHTML = function (data, context, keepScripts) {
				if (!data || typeof data !== "string") {
					return null;
				}
				if (typeof context === "boolean") {
					keepScripts = context;
					context = false;
				}
				context = context || document;

				var parsed = rsingleTag.exec(data),
				    scripts = !keepScripts && [];

				// Single tag
				if (parsed) {
					return [context.createElement(parsed[1])];
				}

				parsed = buildFragment([data], context, scripts);

				if (scripts && scripts.length) {
					jQuery(scripts).remove();
				}

				return jQuery.merge([], parsed.childNodes);
			};

			// Keep a copy of the old load method
			var _load = jQuery.fn.load;

			/**
	   * Load a url into a page
	   */
			jQuery.fn.load = function (url, params, callback) {
				if (typeof url !== "string" && _load) {
					return _load.apply(this, arguments);
				}

				var selector,
				    type,
				    response,
				    self = this,
				    off = url.indexOf(" ");

				if (off > -1) {
					selector = jQuery.trim(url.slice(off, url.length));
					url = url.slice(0, off);
				}

				// If it's a function
				if (jQuery.isFunction(params)) {

					// We assume that it's the callback
					callback = params;
					params = undefined;

					// Otherwise, build a param string
				} else if (params && (typeof params === "undefined" ? "undefined" : _typeof(params)) === "object") {
					type = "POST";
				}

				// If we have elements to modify, make the request
				if (self.length > 0) {
					jQuery.ajax({
						url: url,

						// If "type" variable is undefined, then "GET" method will be used.
						// Make value of this field explicit since
						// user can override it through ajaxSetup method
						type: type || "GET",
						dataType: "html",
						data: params
					}).done(function (responseText) {

						// Save response for use in complete callback
						response = arguments;

						self.html(selector ?

						// If a selector was specified, locate the right elements in a dummy div
						// Exclude scripts to avoid IE 'Permission Denied' errors
						jQuery("<div>").append(jQuery.parseHTML(responseText)).find(selector) :

						// Otherwise use the full result
						responseText);

						// If the request succeeds, this function gets "data", "status", "jqXHR"
						// but they are ignored because response was set above.
						// If it fails, this function gets "jqXHR", "status", "error"
					}).always(callback && function (jqXHR, status) {
						self.each(function () {
							callback.apply(this, response || [jqXHR.responseText, status, jqXHR]);
						});
					});
				}

				return this;
			};

			// Attach a bunch of functions for handling common AJAX events
			jQuery.each(["ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend"], function (i, type) {
				jQuery.fn[type] = function (fn) {
					return this.on(type, fn);
				};
			});

			jQuery.expr.filters.animated = function (elem) {
				return jQuery.grep(jQuery.timers, function (fn) {
					return elem === fn.elem;
				}).length;
			};

			/**
	   * Gets a window from an element
	   */
			function getWindow(elem) {
				return jQuery.isWindow(elem) ? elem : elem.nodeType === 9 ? elem.defaultView || elem.parentWindow : false;
			}

			jQuery.offset = {
				setOffset: function setOffset(elem, options, i) {
					var curPosition,
					    curLeft,
					    curCSSTop,
					    curTop,
					    curOffset,
					    curCSSLeft,
					    calculatePosition,
					    position = jQuery.css(elem, "position"),
					    curElem = jQuery(elem),
					    props = {};

					// set position first, in-case top/left are set even on static elem
					if (position === "static") {
						elem.style.position = "relative";
					}

					curOffset = curElem.offset();
					curCSSTop = jQuery.css(elem, "top");
					curCSSLeft = jQuery.css(elem, "left");
					calculatePosition = (position === "absolute" || position === "fixed") && jQuery.inArray("auto", [curCSSTop, curCSSLeft]) > -1;

					// need to be able to calculate position if either top or left
					// is auto and position is either absolute or fixed
					if (calculatePosition) {
						curPosition = curElem.position();
						curTop = curPosition.top;
						curLeft = curPosition.left;
					} else {
						curTop = parseFloat(curCSSTop) || 0;
						curLeft = parseFloat(curCSSLeft) || 0;
					}

					if (jQuery.isFunction(options)) {

						// Use jQuery.extend here to allow modification of coordinates argument (gh-1848)
						options = options.call(elem, i, jQuery.extend({}, curOffset));
					}

					if (options.top != null) {
						props.top = options.top - curOffset.top + curTop;
					}
					if (options.left != null) {
						props.left = options.left - curOffset.left + curLeft;
					}

					if ("using" in options) {
						options.using.call(elem, props);
					} else {
						curElem.css(props);
					}
				}
			};

			jQuery.fn.extend({
				offset: function offset(options) {
					if (arguments.length) {
						return options === undefined ? this : this.each(function (i) {
							jQuery.offset.setOffset(this, options, i);
						});
					}

					var docElem,
					    win,
					    box = { top: 0, left: 0 },
					    elem = this[0],
					    doc = elem && elem.ownerDocument;

					if (!doc) {
						return;
					}

					docElem = doc.documentElement;

					// Make sure it's not a disconnected DOM node
					if (!jQuery.contains(docElem, elem)) {
						return box;
					}

					// If we don't have gBCR, just use 0,0 rather than error
					// BlackBerry 5, iOS 3 (original iPhone)
					if (typeof elem.getBoundingClientRect !== "undefined") {
						box = elem.getBoundingClientRect();
					}
					win = getWindow(doc);
					return {
						top: box.top + (win.pageYOffset || docElem.scrollTop) - (docElem.clientTop || 0),
						left: box.left + (win.pageXOffset || docElem.scrollLeft) - (docElem.clientLeft || 0)
					};
				},

				position: function position() {
					if (!this[0]) {
						return;
					}

					var offsetParent,
					    offset,
					    parentOffset = { top: 0, left: 0 },
					    elem = this[0];

					// Fixed elements are offset from window (parentOffset = {top:0, left: 0},
					// because it is its only offset parent
					if (jQuery.css(elem, "position") === "fixed") {

						// we assume that getBoundingClientRect is available when computed position is fixed
						offset = elem.getBoundingClientRect();
					} else {

						// Get *real* offsetParent
						offsetParent = this.offsetParent();

						// Get correct offsets
						offset = this.offset();
						if (!jQuery.nodeName(offsetParent[0], "html")) {
							parentOffset = offsetParent.offset();
						}

						// Add offsetParent borders
						parentOffset.top += jQuery.css(offsetParent[0], "borderTopWidth", true);
						parentOffset.left += jQuery.css(offsetParent[0], "borderLeftWidth", true);
					}

					// Subtract parent offsets and element margins
					// note: when an element has margin: auto the offsetLeft and marginLeft
					// are the same in Safari causing offset.left to incorrectly be 0
					return {
						top: offset.top - parentOffset.top - jQuery.css(elem, "marginTop", true),
						left: offset.left - parentOffset.left - jQuery.css(elem, "marginLeft", true)
					};
				},

				offsetParent: function offsetParent() {
					return this.map(function () {
						var offsetParent = this.offsetParent;

						while (offsetParent && !jQuery.nodeName(offsetParent, "html") && jQuery.css(offsetParent, "position") === "static") {
							offsetParent = offsetParent.offsetParent;
						}
						return offsetParent || documentElement;
					});
				}
			});

			// Create scrollLeft and scrollTop methods
			jQuery.each({ scrollLeft: "pageXOffset", scrollTop: "pageYOffset" }, function (method, prop) {
				var top = /Y/.test(prop);

				jQuery.fn[method] = function (val) {
					return access(this, function (elem, method, val) {
						var win = getWindow(elem);

						if (val === undefined) {
							return win ? prop in win ? win[prop] : win.document.documentElement[method] : elem[method];
						}

						if (win) {
							win.scrollTo(!top ? val : jQuery(win).scrollLeft(), top ? val : jQuery(win).scrollTop());
						} else {
							elem[method] = val;
						}
					}, method, val, arguments.length, null);
				};
			});

			// Support: Safari<7-8+, Chrome<37-44+
			// Add the top/left cssHooks using jQuery.fn.position
			// Webkit bug: https://bugs.webkit.org/show_bug.cgi?id=29084
			// getComputedStyle returns percent when specified for top/left/bottom/right
			// rather than make the css module depend on the offset module, we just check for it here
			jQuery.each(["top", "left"], function (i, prop) {
				jQuery.cssHooks[prop] = addGetHookIf(support.pixelPosition, function (elem, computed) {
					if (computed) {
						computed = curCSS(elem, prop);

						// if curCSS returns percentage, fallback to offset
						return rnumnonpx.test(computed) ? jQuery(elem).position()[prop] + "px" : computed;
					}
				});
			});

			// Create innerHeight, innerWidth, height, width, outerHeight and outerWidth methods
			jQuery.each({ Height: "height", Width: "width" }, function (name, type) {
				jQuery.each({ padding: "inner" + name, content: type, "": "outer" + name }, function (defaultExtra, funcName) {

					// margin is only for outerHeight, outerWidth
					jQuery.fn[funcName] = function (margin, value) {
						var chainable = arguments.length && (defaultExtra || typeof margin !== "boolean"),
						    extra = defaultExtra || (margin === true || value === true ? "margin" : "border");

						return access(this, function (elem, type, value) {
							var doc;

							if (jQuery.isWindow(elem)) {

								// As of 5/8/2012 this will yield incorrect results for Mobile Safari, but there
								// isn't a whole lot we can do. See pull request at this URL for discussion:
								// https://github.com/jquery/jquery/pull/764
								return elem.document.documentElement["client" + name];
							}

							// Get document width or height
							if (elem.nodeType === 9) {
								doc = elem.documentElement;

								// Either scroll[Width/Height] or offset[Width/Height] or client[Width/Height],
								// whichever is greatest
								// unfortunately, this causes bug #3838 in IE6/8 only,
								// but there is currently no good, small way to fix it.
								return Math.max(elem.body["scroll" + name], doc["scroll" + name], elem.body["offset" + name], doc["offset" + name], doc["client" + name]);
							}

							return value === undefined ?

							// Get width or height on the element, requesting but not forcing parseFloat
							jQuery.css(elem, type, extra) :

							// Set width or height on the element
							jQuery.style(elem, type, value, extra);
						}, type, chainable ? margin : undefined, chainable, null);
					};
				});
			});

			jQuery.fn.extend({

				bind: function bind(types, data, fn) {
					return this.on(types, null, data, fn);
				},
				unbind: function unbind(types, fn) {
					return this.off(types, null, fn);
				},

				delegate: function delegate(selector, types, data, fn) {
					return this.on(types, selector, data, fn);
				},
				undelegate: function undelegate(selector, types, fn) {

					// ( namespace ) or ( selector, types [, fn] )
					return arguments.length === 1 ? this.off(selector, "**") : this.off(types, selector || "**", fn);
				}
			});

			// The number of elements contained in the matched element set
			jQuery.fn.size = function () {
				return this.length;
			};

			jQuery.fn.andSelf = jQuery.fn.addBack;

			// Register as a named AMD module, since jQuery can be concatenated with other
			// files that may use define, but not via a proper concatenation script that
			// understands anonymous AMD modules. A named AMD is safest and most robust
			// way to register. Lowercase jquery is used because AMD module names are
			// derived from file names, and jQuery is normally delivered in a lowercase
			// file name. Do this after creating the global so that if an AMD module wants
			// to call noConflict to hide this version of jQuery, it will work.

			// Note that for maximum portability, libraries that are not jQuery should
			// declare themselves as anonymous modules, and avoid setting a global if an
			// AMD loader is present. jQuery is a special case. For more information, see
			// https://github.com/jrburke/requirejs/wiki/Updating-existing-libraries#wiki-anon

			if (typeof define === "function" && define.amd) {
				define("jquery", [], function () {
					return jQuery;
				});
			}

			var

			// Map over jQuery in case of overwrite
			_jQuery = window.jQuery,


			// Map over the $ in case of overwrite
			_$ = window.$;

			jQuery.noConflict = function (deep) {
				if (window.$ === jQuery) {
					window.$ = _$;
				}

				if (deep && window.jQuery === jQuery) {
					window.jQuery = _jQuery;
				}

				return jQuery;
			};

			// Expose jQuery and $ identifiers, even in
			// AMD (#7102#comment:10, https://github.com/jquery/jquery/pull/557)
			// and CommonJS for browser emulators (#13566)
			if (!noGlobal) {
				window.jQuery = window.$ = jQuery;
			}

			return jQuery;
		});
	}).call(window);

/***/ },
/* 4 */
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	__webpack_require__(5);

	__webpack_require__(6);

/***/ },
/* 5 */
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;var require;var require;'use strict';

	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

	/*!
	 * Select2 4.0.3
	 * https://select2.github.io
	 *
	 * Released under the MIT license
	 * https://github.com/select2/select2/blob/master/LICENSE.md
	 */
	(function (factory) {
	  if (true) {
	    // AMD. Register as an anonymous module.
	    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(3)], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory), __WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ? (__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	  } else if ((typeof exports === 'undefined' ? 'undefined' : _typeof(exports)) === 'object') {
	    // Node/CommonJS
	    factory(require('jquery'));
	  } else {
	    // Browser globals
	    factory(jQuery);
	  }
	})(function (jQuery) {
	  // This is needed so we can catch the AMD loader configuration and use it
	  // The inner file should be wrapped (by `banner.start.js`) in a function that
	  // returns the AMD loader references.
	  var S2 = function () {
	    // Restore the Select2 AMD loader so it can be used
	    // Needed mostly in the language files, where the loader is not inserted
	    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) {
	      var S2 = jQuery.fn.select2.amd;
	    }
	    var S2;(function () {
	      if (!S2 || !S2.requirejs) {
	        if (!S2) {
	          S2 = {};
	        } else {
	          require = S2;
	        }
	        /**
	         * @license almond 0.3.1 Copyright (c) 2011-2014, The Dojo Foundation All Rights Reserved.
	         * Available via the MIT or new BSD license.
	         * see: http://github.com/jrburke/almond for details
	         */
	        //Going sloppy to avoid 'use strict' string cost, but strict practices should
	        //be followed.
	        /*jslint sloppy: true */
	        /*global setTimeout: false */

	        var requirejs, require, define;
	        (function (undef) {
	          var main,
	              _req,
	              makeMap,
	              handlers,
	              defined = {},
	              waiting = {},
	              config = {},
	              defining = {},
	              hasOwn = Object.prototype.hasOwnProperty,
	              aps = [].slice,
	              jsSuffixRegExp = /\.js$/;

	          function hasProp(obj, prop) {
	            return hasOwn.call(obj, prop);
	          }

	          /**
	           * Given a relative module name, like ./something, normalize it to
	           * a real name that can be mapped to a path.
	           * @param {String} name the relative name
	           * @param {String} baseName a real name that the name arg is relative
	           * to.
	           * @returns {String} normalized name
	           */
	          function normalize(name, baseName) {
	            var nameParts,
	                nameSegment,
	                mapValue,
	                foundMap,
	                lastIndex,
	                foundI,
	                foundStarMap,
	                starI,
	                i,
	                j,
	                part,
	                baseParts = baseName && baseName.split("/"),
	                map = config.map,
	                starMap = map && map['*'] || {};

	            //Adjust any relative paths.
	            if (name && name.charAt(0) === ".") {
	              //If have a base name, try to normalize against it,
	              //otherwise, assume it is a top-level require that will
	              //be relative to baseUrl in the end.
	              if (baseName) {
	                name = name.split('/');
	                lastIndex = name.length - 1;

	                // Node .js allowance:
	                if (config.nodeIdCompat && jsSuffixRegExp.test(name[lastIndex])) {
	                  name[lastIndex] = name[lastIndex].replace(jsSuffixRegExp, '');
	                }

	                //Lop off the last part of baseParts, so that . matches the
	                //"directory" and not name of the baseName's module. For instance,
	                //baseName of "one/two/three", maps to "one/two/three.js", but we
	                //want the directory, "one/two" for this normalization.
	                name = baseParts.slice(0, baseParts.length - 1).concat(name);

	                //start trimDots
	                for (i = 0; i < name.length; i += 1) {
	                  part = name[i];
	                  if (part === ".") {
	                    name.splice(i, 1);
	                    i -= 1;
	                  } else if (part === "..") {
	                    if (i === 1 && (name[2] === '..' || name[0] === '..')) {
	                      //End of the line. Keep at least one non-dot
	                      //path segment at the front so it can be mapped
	                      //correctly to disk. Otherwise, there is likely
	                      //no path mapping for a path starting with '..'.
	                      //This can still fail, but catches the most reasonable
	                      //uses of ..
	                      break;
	                    } else if (i > 0) {
	                      name.splice(i - 1, 2);
	                      i -= 2;
	                    }
	                  }
	                }
	                //end trimDots

	                name = name.join("/");
	              } else if (name.indexOf('./') === 0) {
	                // No baseName, so this is ID is resolved relative
	                // to baseUrl, pull off the leading dot.
	                name = name.substring(2);
	              }
	            }

	            //Apply map config if available.
	            if ((baseParts || starMap) && map) {
	              nameParts = name.split('/');

	              for (i = nameParts.length; i > 0; i -= 1) {
	                nameSegment = nameParts.slice(0, i).join("/");

	                if (baseParts) {
	                  //Find the longest baseName segment match in the config.
	                  //So, do joins on the biggest to smallest lengths of baseParts.
	                  for (j = baseParts.length; j > 0; j -= 1) {
	                    mapValue = map[baseParts.slice(0, j).join('/')];

	                    //baseName segment has  config, find if it has one for
	                    //this name.
	                    if (mapValue) {
	                      mapValue = mapValue[nameSegment];
	                      if (mapValue) {
	                        //Match, update name to the new value.
	                        foundMap = mapValue;
	                        foundI = i;
	                        break;
	                      }
	                    }
	                  }
	                }

	                if (foundMap) {
	                  break;
	                }

	                //Check for a star map match, but just hold on to it,
	                //if there is a shorter segment match later in a matching
	                //config, then favor over this star map.
	                if (!foundStarMap && starMap && starMap[nameSegment]) {
	                  foundStarMap = starMap[nameSegment];
	                  starI = i;
	                }
	              }

	              if (!foundMap && foundStarMap) {
	                foundMap = foundStarMap;
	                foundI = starI;
	              }

	              if (foundMap) {
	                nameParts.splice(0, foundI, foundMap);
	                name = nameParts.join('/');
	              }
	            }

	            return name;
	          }

	          function makeRequire(relName, forceSync) {
	            return function () {
	              //A version of a require function that passes a moduleName
	              //value for items that may need to
	              //look up paths relative to the moduleName
	              var args = aps.call(arguments, 0);

	              //If first arg is not require('string'), and there is only
	              //one arg, it is the array form without a callback. Insert
	              //a null so that the following concat is correct.
	              if (typeof args[0] !== 'string' && args.length === 1) {
	                args.push(null);
	              }
	              return _req.apply(undef, args.concat([relName, forceSync]));
	            };
	          }

	          function makeNormalize(relName) {
	            return function (name) {
	              return normalize(name, relName);
	            };
	          }

	          function makeLoad(depName) {
	            return function (value) {
	              defined[depName] = value;
	            };
	          }

	          function callDep(name) {
	            if (hasProp(waiting, name)) {
	              var args = waiting[name];
	              delete waiting[name];
	              defining[name] = true;
	              main.apply(undef, args);
	            }

	            if (!hasProp(defined, name) && !hasProp(defining, name)) {
	              throw new Error('No ' + name);
	            }
	            return defined[name];
	          }

	          //Turns a plugin!resource to [plugin, resource]
	          //with the plugin being undefined if the name
	          //did not have a plugin prefix.
	          function splitPrefix(name) {
	            var prefix,
	                index = name ? name.indexOf('!') : -1;
	            if (index > -1) {
	              prefix = name.substring(0, index);
	              name = name.substring(index + 1, name.length);
	            }
	            return [prefix, name];
	          }

	          /**
	           * Makes a name map, normalizing the name, and using a plugin
	           * for normalization if necessary. Grabs a ref to plugin
	           * too, as an optimization.
	           */
	          makeMap = function makeMap(name, relName) {
	            var plugin,
	                parts = splitPrefix(name),
	                prefix = parts[0];

	            name = parts[1];

	            if (prefix) {
	              prefix = normalize(prefix, relName);
	              plugin = callDep(prefix);
	            }

	            //Normalize according
	            if (prefix) {
	              if (plugin && plugin.normalize) {
	                name = plugin.normalize(name, makeNormalize(relName));
	              } else {
	                name = normalize(name, relName);
	              }
	            } else {
	              name = normalize(name, relName);
	              parts = splitPrefix(name);
	              prefix = parts[0];
	              name = parts[1];
	              if (prefix) {
	                plugin = callDep(prefix);
	              }
	            }

	            //Using ridiculous property names for space reasons
	            return {
	              f: prefix ? prefix + '!' + name : name, //fullName
	              n: name,
	              pr: prefix,
	              p: plugin
	            };
	          };

	          function makeConfig(name) {
	            return function () {
	              return config && config.config && config.config[name] || {};
	            };
	          }

	          handlers = {
	            require: function require(name) {
	              return makeRequire(name);
	            },
	            exports: function exports(name) {
	              var e = defined[name];
	              if (typeof e !== 'undefined') {
	                return e;
	              } else {
	                return defined[name] = {};
	              }
	            },
	            module: function module(name) {
	              return {
	                id: name,
	                uri: '',
	                exports: defined[name],
	                config: makeConfig(name)
	              };
	            }
	          };

	          main = function main(name, deps, callback, relName) {
	            var cjsModule,
	                depName,
	                ret,
	                map,
	                i,
	                args = [],
	                callbackType = typeof callback === 'undefined' ? 'undefined' : _typeof(callback),
	                usingExports;

	            //Use name if no relName
	            relName = relName || name;

	            //Call the callback to define the module, if necessary.
	            if (callbackType === 'undefined' || callbackType === 'function') {
	              //Pull out the defined dependencies and pass the ordered
	              //values to the callback.
	              //Default to [require, exports, module] if no deps
	              deps = !deps.length && callback.length ? ['require', 'exports', 'module'] : deps;
	              for (i = 0; i < deps.length; i += 1) {
	                map = makeMap(deps[i], relName);
	                depName = map.f;

	                //Fast path CommonJS standard dependencies.
	                if (depName === "require") {
	                  args[i] = handlers.require(name);
	                } else if (depName === "exports") {
	                  //CommonJS module spec 1.1
	                  args[i] = handlers.exports(name);
	                  usingExports = true;
	                } else if (depName === "module") {
	                  //CommonJS module spec 1.1
	                  cjsModule = args[i] = handlers.module(name);
	                } else if (hasProp(defined, depName) || hasProp(waiting, depName) || hasProp(defining, depName)) {
	                  args[i] = callDep(depName);
	                } else if (map.p) {
	                  map.p.load(map.n, makeRequire(relName, true), makeLoad(depName), {});
	                  args[i] = defined[depName];
	                } else {
	                  throw new Error(name + ' missing ' + depName);
	                }
	              }

	              ret = callback ? callback.apply(defined[name], args) : undefined;

	              if (name) {
	                //If setting exports via "module" is in play,
	                //favor that over return value and exports. After that,
	                //favor a non-undefined return value over exports use.
	                if (cjsModule && cjsModule.exports !== undef && cjsModule.exports !== defined[name]) {
	                  defined[name] = cjsModule.exports;
	                } else if (ret !== undef || !usingExports) {
	                  //Use the return value from the function.
	                  defined[name] = ret;
	                }
	              }
	            } else if (name) {
	              //May just be an object definition for the module. Only
	              //worry about defining if have a module name.
	              defined[name] = callback;
	            }
	          };

	          requirejs = require = _req = function req(deps, callback, relName, forceSync, alt) {
	            if (typeof deps === "string") {
	              if (handlers[deps]) {
	                //callback in this case is really relName
	                return handlers[deps](callback);
	              }
	              //Just return the module wanted. In this scenario, the
	              //deps arg is the module name, and second arg (if passed)
	              //is just the relName.
	              //Normalize module name, if it contains . or ..
	              return callDep(makeMap(deps, callback).f);
	            } else if (!deps.splice) {
	              //deps is a config object, not an array.
	              config = deps;
	              if (config.deps) {
	                _req(config.deps, config.callback);
	              }
	              if (!callback) {
	                return;
	              }

	              if (callback.splice) {
	                //callback is an array, which means it is a dependency list.
	                //Adjust args if there are dependencies
	                deps = callback;
	                callback = relName;
	                relName = null;
	              } else {
	                deps = undef;
	              }
	            }

	            //Support require(['a'])
	            callback = callback || function () {};

	            //If relName is a function, it is an errback handler,
	            //so remove it.
	            if (typeof relName === 'function') {
	              relName = forceSync;
	              forceSync = alt;
	            }

	            //Simulate async callback;
	            if (forceSync) {
	              main(undef, deps, callback, relName);
	            } else {
	              //Using a non-zero value because of concern for what old browsers
	              //do, and latest browsers "upgrade" to 4 if lower value is used:
	              //http://www.whatwg.org/specs/web-apps/current-work/multipage/timers.html#dom-windowtimers-settimeout:
	              //If want a value immediately, use require('id') instead -- something
	              //that works in almond on the global level, but not guaranteed and
	              //unlikely to work in other AMD implementations.
	              setTimeout(function () {
	                main(undef, deps, callback, relName);
	              }, 4);
	            }

	            return _req;
	          };

	          /**
	           * Just drops the config on the floor, but returns req in case
	           * the config return value is used.
	           */
	          _req.config = function (cfg) {
	            return _req(cfg);
	          };

	          /**
	           * Expose module registry for debugging and tooling
	           */
	          requirejs._defined = defined;

	          define = function define(name, deps, callback) {
	            if (typeof name !== 'string') {
	              throw new Error('See almond README: incorrect module build, no module name');
	            }

	            //This module may not have dependencies
	            if (!deps.splice) {
	              //deps is not an array, so probably means
	              //an object literal or factory function for
	              //the value. Adjust args.
	              callback = deps;
	              deps = [];
	            }

	            if (!hasProp(defined, name) && !hasProp(waiting, name)) {
	              waiting[name] = [name, deps, callback];
	            }
	          };

	          define.amd = {
	            jQuery: true
	          };
	        })();

	        S2.requirejs = requirejs;S2.require = require;S2.define = define;
	      }
	    })();
	    S2.define("almond", function () {});

	    /* global jQuery:false, $:false */
	    S2.define('jquery', [], function () {
	      var _$ = jQuery || $;

	      if (_$ == null && console && console.error) {
	        console.error('Select2: An instance of jQuery or a jQuery-compatible library was not ' + 'found. Make sure that you are including jQuery before Select2 on your ' + 'web page.');
	      }

	      return _$;
	    });

	    S2.define('select2/utils', ['jquery'], function ($) {
	      var Utils = {};

	      Utils.Extend = function (ChildClass, SuperClass) {
	        var __hasProp = {}.hasOwnProperty;

	        function BaseConstructor() {
	          this.constructor = ChildClass;
	        }

	        for (var key in SuperClass) {
	          if (__hasProp.call(SuperClass, key)) {
	            ChildClass[key] = SuperClass[key];
	          }
	        }

	        BaseConstructor.prototype = SuperClass.prototype;
	        ChildClass.prototype = new BaseConstructor();
	        ChildClass.__super__ = SuperClass.prototype;

	        return ChildClass;
	      };

	      function getMethods(theClass) {
	        var proto = theClass.prototype;

	        var methods = [];

	        for (var methodName in proto) {
	          var m = proto[methodName];

	          if (typeof m !== 'function') {
	            continue;
	          }

	          if (methodName === 'constructor') {
	            continue;
	          }

	          methods.push(methodName);
	        }

	        return methods;
	      }

	      Utils.Decorate = function (SuperClass, DecoratorClass) {
	        var decoratedMethods = getMethods(DecoratorClass);
	        var superMethods = getMethods(SuperClass);

	        function DecoratedClass() {
	          var unshift = Array.prototype.unshift;

	          var argCount = DecoratorClass.prototype.constructor.length;

	          var calledConstructor = SuperClass.prototype.constructor;

	          if (argCount > 0) {
	            unshift.call(arguments, SuperClass.prototype.constructor);

	            calledConstructor = DecoratorClass.prototype.constructor;
	          }

	          calledConstructor.apply(this, arguments);
	        }

	        DecoratorClass.displayName = SuperClass.displayName;

	        function ctr() {
	          this.constructor = DecoratedClass;
	        }

	        DecoratedClass.prototype = new ctr();

	        for (var m = 0; m < superMethods.length; m++) {
	          var superMethod = superMethods[m];

	          DecoratedClass.prototype[superMethod] = SuperClass.prototype[superMethod];
	        }

	        var calledMethod = function calledMethod(methodName) {
	          // Stub out the original method if it's not decorating an actual method
	          var originalMethod = function originalMethod() {};

	          if (methodName in DecoratedClass.prototype) {
	            originalMethod = DecoratedClass.prototype[methodName];
	          }

	          var decoratedMethod = DecoratorClass.prototype[methodName];

	          return function () {
	            var unshift = Array.prototype.unshift;

	            unshift.call(arguments, originalMethod);

	            return decoratedMethod.apply(this, arguments);
	          };
	        };

	        for (var d = 0; d < decoratedMethods.length; d++) {
	          var decoratedMethod = decoratedMethods[d];

	          DecoratedClass.prototype[decoratedMethod] = calledMethod(decoratedMethod);
	        }

	        return DecoratedClass;
	      };

	      var Observable = function Observable() {
	        this.listeners = {};
	      };

	      Observable.prototype.on = function (event, callback) {
	        this.listeners = this.listeners || {};

	        if (event in this.listeners) {
	          this.listeners[event].push(callback);
	        } else {
	          this.listeners[event] = [callback];
	        }
	      };

	      Observable.prototype.trigger = function (event) {
	        var slice = Array.prototype.slice;
	        var params = slice.call(arguments, 1);

	        this.listeners = this.listeners || {};

	        // Params should always come in as an array
	        if (params == null) {
	          params = [];
	        }

	        // If there are no arguments to the event, use a temporary object
	        if (params.length === 0) {
	          params.push({});
	        }

	        // Set the `_type` of the first object to the event
	        params[0]._type = event;

	        if (event in this.listeners) {
	          this.invoke(this.listeners[event], slice.call(arguments, 1));
	        }

	        if ('*' in this.listeners) {
	          this.invoke(this.listeners['*'], arguments);
	        }
	      };

	      Observable.prototype.invoke = function (listeners, params) {
	        for (var i = 0, len = listeners.length; i < len; i++) {
	          listeners[i].apply(this, params);
	        }
	      };

	      Utils.Observable = Observable;

	      Utils.generateChars = function (length) {
	        var chars = '';

	        for (var i = 0; i < length; i++) {
	          var randomChar = Math.floor(Math.random() * 36);
	          chars += randomChar.toString(36);
	        }

	        return chars;
	      };

	      Utils.bind = function (func, context) {
	        return function () {
	          func.apply(context, arguments);
	        };
	      };

	      Utils._convertData = function (data) {
	        for (var originalKey in data) {
	          var keys = originalKey.split('-');

	          var dataLevel = data;

	          if (keys.length === 1) {
	            continue;
	          }

	          for (var k = 0; k < keys.length; k++) {
	            var key = keys[k];

	            // Lowercase the first letter
	            // By default, dash-separated becomes camelCase
	            key = key.substring(0, 1).toLowerCase() + key.substring(1);

	            if (!(key in dataLevel)) {
	              dataLevel[key] = {};
	            }

	            if (k == keys.length - 1) {
	              dataLevel[key] = data[originalKey];
	            }

	            dataLevel = dataLevel[key];
	          }

	          delete data[originalKey];
	        }

	        return data;
	      };

	      Utils.hasScroll = function (index, el) {
	        // Adapted from the function created by @ShadowScripter
	        // and adapted by @BillBarry on the Stack Exchange Code Review website.
	        // The original code can be found at
	        // http://codereview.stackexchange.com/q/13338
	        // and was designed to be used with the Sizzle selector engine.

	        var $el = $(el);
	        var overflowX = el.style.overflowX;
	        var overflowY = el.style.overflowY;

	        //Check both x and y declarations
	        if (overflowX === overflowY && (overflowY === 'hidden' || overflowY === 'visible')) {
	          return false;
	        }

	        if (overflowX === 'scroll' || overflowY === 'scroll') {
	          return true;
	        }

	        return $el.innerHeight() < el.scrollHeight || $el.innerWidth() < el.scrollWidth;
	      };

	      Utils.escapeMarkup = function (markup) {
	        var replaceMap = {
	          '\\': '&#92;',
	          '&': '&amp;',
	          '<': '&lt;',
	          '>': '&gt;',
	          '"': '&quot;',
	          '\'': '&#39;',
	          '/': '&#47;'
	        };

	        // Do not try to escape the markup if it's not a string
	        if (typeof markup !== 'string') {
	          return markup;
	        }

	        return String(markup).replace(/[&<>"'\/\\]/g, function (match) {
	          return replaceMap[match];
	        });
	      };

	      // Append an array of jQuery nodes to a given element.
	      Utils.appendMany = function ($element, $nodes) {
	        // jQuery 1.7.x does not support $.fn.append() with an array
	        // Fall back to a jQuery object collection using $.fn.add()
	        if ($.fn.jquery.substr(0, 3) === '1.7') {
	          var $jqNodes = $();

	          $.map($nodes, function (node) {
	            $jqNodes = $jqNodes.add(node);
	          });

	          $nodes = $jqNodes;
	        }

	        $element.append($nodes);
	      };

	      return Utils;
	    });

	    S2.define('select2/results', ['jquery', './utils'], function ($, Utils) {
	      function Results($element, options, dataAdapter) {
	        this.$element = $element;
	        this.data = dataAdapter;
	        this.options = options;

	        Results.__super__.constructor.call(this);
	      }

	      Utils.Extend(Results, Utils.Observable);

	      Results.prototype.render = function () {
	        var $results = $('<ul class="select2-results__options" role="tree"></ul>');

	        if (this.options.get('multiple')) {
	          $results.attr('aria-multiselectable', 'true');
	        }

	        this.$results = $results;

	        return $results;
	      };

	      Results.prototype.clear = function () {
	        this.$results.empty();
	      };

	      Results.prototype.displayMessage = function (params) {
	        var escapeMarkup = this.options.get('escapeMarkup');

	        this.clear();
	        this.hideLoading();

	        var $message = $('<li role="treeitem" aria-live="assertive"' + ' class="select2-results__option"></li>');

	        var message = this.options.get('translations').get(params.message);

	        $message.append(escapeMarkup(message(params.args)));

	        $message[0].className += ' select2-results__message';

	        this.$results.append($message);
	      };

	      Results.prototype.hideMessages = function () {
	        this.$results.find('.select2-results__message').remove();
	      };

	      Results.prototype.append = function (data) {
	        this.hideLoading();

	        var $options = [];

	        if (data.results == null || data.results.length === 0) {
	          if (this.$results.children().length === 0) {
	            this.trigger('results:message', {
	              message: 'noResults'
	            });
	          }

	          return;
	        }

	        data.results = this.sort(data.results);

	        for (var d = 0; d < data.results.length; d++) {
	          var item = data.results[d];

	          var $option = this.option(item);

	          $options.push($option);
	        }

	        this.$results.append($options);
	      };

	      Results.prototype.position = function ($results, $dropdown) {
	        var $resultsContainer = $dropdown.find('.select2-results');
	        $resultsContainer.append($results);
	      };

	      Results.prototype.sort = function (data) {
	        var sorter = this.options.get('sorter');

	        return sorter(data);
	      };

	      Results.prototype.highlightFirstItem = function () {
	        var $options = this.$results.find('.select2-results__option[aria-selected]');

	        var $selected = $options.filter('[aria-selected=true]');

	        // Check if there are any selected options
	        if ($selected.length > 0) {
	          // If there are selected options, highlight the first
	          $selected.first().trigger('mouseenter');
	        } else {
	          // If there are no selected options, highlight the first option
	          // in the dropdown
	          $options.first().trigger('mouseenter');
	        }

	        this.ensureHighlightVisible();
	      };

	      Results.prototype.setClasses = function () {
	        var self = this;

	        this.data.current(function (selected) {
	          var selectedIds = $.map(selected, function (s) {
	            return s.id.toString();
	          });

	          var $options = self.$results.find('.select2-results__option[aria-selected]');

	          $options.each(function () {
	            var $option = $(this);

	            var item = $.data(this, 'data');

	            // id needs to be converted to a string when comparing
	            var id = '' + item.id;

	            if (item.element != null && item.element.selected || item.element == null && $.inArray(id, selectedIds) > -1) {
	              $option.attr('aria-selected', 'true');
	            } else {
	              $option.attr('aria-selected', 'false');
	            }
	          });
	        });
	      };

	      Results.prototype.showLoading = function (params) {
	        this.hideLoading();

	        var loadingMore = this.options.get('translations').get('searching');

	        var loading = {
	          disabled: true,
	          loading: true,
	          text: loadingMore(params)
	        };
	        var $loading = this.option(loading);
	        $loading.className += ' loading-results';

	        this.$results.prepend($loading);
	      };

	      Results.prototype.hideLoading = function () {
	        this.$results.find('.loading-results').remove();
	      };

	      Results.prototype.option = function (data) {
	        var option = document.createElement('li');
	        option.className = 'select2-results__option';

	        var attrs = {
	          'role': 'treeitem',
	          'aria-selected': 'false'
	        };

	        if (data.disabled) {
	          delete attrs['aria-selected'];
	          attrs['aria-disabled'] = 'true';
	        }

	        if (data.id == null) {
	          delete attrs['aria-selected'];
	        }

	        if (data._resultId != null) {
	          option.id = data._resultId;
	        }

	        if (data.title) {
	          option.title = data.title;
	        }

	        if (data.children) {
	          attrs.role = 'group';
	          attrs['aria-label'] = data.text;
	          delete attrs['aria-selected'];
	        }

	        for (var attr in attrs) {
	          var val = attrs[attr];

	          option.setAttribute(attr, val);
	        }

	        if (data.children) {
	          var $option = $(option);

	          var label = document.createElement('strong');
	          label.className = 'select2-results__group';

	          var $label = $(label);
	          this.template(data, label);

	          var $children = [];

	          for (var c = 0; c < data.children.length; c++) {
	            var child = data.children[c];

	            var $child = this.option(child);

	            $children.push($child);
	          }

	          var $childrenContainer = $('<ul></ul>', {
	            'class': 'select2-results__options select2-results__options--nested'
	          });

	          $childrenContainer.append($children);

	          $option.append(label);
	          $option.append($childrenContainer);
	        } else {
	          this.template(data, option);
	        }

	        $.data(option, 'data', data);

	        return option;
	      };

	      Results.prototype.bind = function (container, $container) {
	        var self = this;

	        var id = container.id + '-results';

	        this.$results.attr('id', id);

	        container.on('results:all', function (params) {
	          self.clear();
	          self.append(params.data);

	          if (container.isOpen()) {
	            self.setClasses();
	            self.highlightFirstItem();
	          }
	        });

	        container.on('results:append', function (params) {
	          self.append(params.data);

	          if (container.isOpen()) {
	            self.setClasses();
	          }
	        });

	        container.on('query', function (params) {
	          self.hideMessages();
	          self.showLoading(params);
	        });

	        container.on('select', function () {
	          if (!container.isOpen()) {
	            return;
	          }

	          self.setClasses();
	          self.highlightFirstItem();
	        });

	        container.on('unselect', function () {
	          if (!container.isOpen()) {
	            return;
	          }

	          self.setClasses();
	          self.highlightFirstItem();
	        });

	        container.on('open', function () {
	          // When the dropdown is open, aria-expended="true"
	          self.$results.attr('aria-expanded', 'true');
	          self.$results.attr('aria-hidden', 'false');

	          self.setClasses();
	          self.ensureHighlightVisible();
	        });

	        container.on('close', function () {
	          // When the dropdown is closed, aria-expended="false"
	          self.$results.attr('aria-expanded', 'false');
	          self.$results.attr('aria-hidden', 'true');
	          self.$results.removeAttr('aria-activedescendant');
	        });

	        container.on('results:toggle', function () {
	          var $highlighted = self.getHighlightedResults();

	          if ($highlighted.length === 0) {
	            return;
	          }

	          $highlighted.trigger('mouseup');
	        });

	        container.on('results:select', function () {
	          var $highlighted = self.getHighlightedResults();

	          if ($highlighted.length === 0) {
	            return;
	          }

	          var data = $highlighted.data('data');

	          if ($highlighted.attr('aria-selected') == 'true') {
	            self.trigger('close', {});
	          } else {
	            self.trigger('select', {
	              data: data
	            });
	          }
	        });

	        container.on('results:previous', function () {
	          var $highlighted = self.getHighlightedResults();

	          var $options = self.$results.find('[aria-selected]');

	          var currentIndex = $options.index($highlighted);

	          // If we are already at te top, don't move further
	          if (currentIndex === 0) {
	            return;
	          }

	          var nextIndex = currentIndex - 1;

	          // If none are highlighted, highlight the first
	          if ($highlighted.length === 0) {
	            nextIndex = 0;
	          }

	          var $next = $options.eq(nextIndex);

	          $next.trigger('mouseenter');

	          var currentOffset = self.$results.offset().top;
	          var nextTop = $next.offset().top;
	          var nextOffset = self.$results.scrollTop() + (nextTop - currentOffset);

	          if (nextIndex === 0) {
	            self.$results.scrollTop(0);
	          } else if (nextTop - currentOffset < 0) {
	            self.$results.scrollTop(nextOffset);
	          }
	        });

	        container.on('results:next', function () {
	          var $highlighted = self.getHighlightedResults();

	          var $options = self.$results.find('[aria-selected]');

	          var currentIndex = $options.index($highlighted);

	          var nextIndex = currentIndex + 1;

	          // If we are at the last option, stay there
	          if (nextIndex >= $options.length) {
	            return;
	          }

	          var $next = $options.eq(nextIndex);

	          $next.trigger('mouseenter');

	          var currentOffset = self.$results.offset().top + self.$results.outerHeight(false);
	          var nextBottom = $next.offset().top + $next.outerHeight(false);
	          var nextOffset = self.$results.scrollTop() + nextBottom - currentOffset;

	          if (nextIndex === 0) {
	            self.$results.scrollTop(0);
	          } else if (nextBottom > currentOffset) {
	            self.$results.scrollTop(nextOffset);
	          }
	        });

	        container.on('results:focus', function (params) {
	          params.element.addClass('select2-results__option--highlighted');
	        });

	        container.on('results:message', function (params) {
	          self.displayMessage(params);
	        });

	        if ($.fn.mousewheel) {
	          this.$results.on('mousewheel', function (e) {
	            var top = self.$results.scrollTop();

	            var bottom = self.$results.get(0).scrollHeight - top + e.deltaY;

	            var isAtTop = e.deltaY > 0 && top - e.deltaY <= 0;
	            var isAtBottom = e.deltaY < 0 && bottom <= self.$results.height();

	            if (isAtTop) {
	              self.$results.scrollTop(0);

	              e.preventDefault();
	              e.stopPropagation();
	            } else if (isAtBottom) {
	              self.$results.scrollTop(self.$results.get(0).scrollHeight - self.$results.height());

	              e.preventDefault();
	              e.stopPropagation();
	            }
	          });
	        }

	        this.$results.on('mouseup', '.select2-results__option[aria-selected]', function (evt) {
	          var $this = $(this);

	          var data = $this.data('data');

	          if ($this.attr('aria-selected') === 'true') {
	            if (self.options.get('multiple')) {
	              self.trigger('unselect', {
	                originalEvent: evt,
	                data: data
	              });
	            } else {
	              self.trigger('close', {});
	            }

	            return;
	          }

	          self.trigger('select', {
	            originalEvent: evt,
	            data: data
	          });
	        });

	        this.$results.on('mouseenter', '.select2-results__option[aria-selected]', function (evt) {
	          var data = $(this).data('data');

	          self.getHighlightedResults().removeClass('select2-results__option--highlighted');

	          self.trigger('results:focus', {
	            data: data,
	            element: $(this)
	          });
	        });
	      };

	      Results.prototype.getHighlightedResults = function () {
	        var $highlighted = this.$results.find('.select2-results__option--highlighted');

	        return $highlighted;
	      };

	      Results.prototype.destroy = function () {
	        this.$results.remove();
	      };

	      Results.prototype.ensureHighlightVisible = function () {
	        var $highlighted = this.getHighlightedResults();

	        if ($highlighted.length === 0) {
	          return;
	        }

	        var $options = this.$results.find('[aria-selected]');

	        var currentIndex = $options.index($highlighted);

	        var currentOffset = this.$results.offset().top;
	        var nextTop = $highlighted.offset().top;
	        var nextOffset = this.$results.scrollTop() + (nextTop - currentOffset);

	        var offsetDelta = nextTop - currentOffset;
	        nextOffset -= $highlighted.outerHeight(false) * 2;

	        if (currentIndex <= 2) {
	          this.$results.scrollTop(0);
	        } else if (offsetDelta > this.$results.outerHeight() || offsetDelta < 0) {
	          this.$results.scrollTop(nextOffset);
	        }
	      };

	      Results.prototype.template = function (result, container) {
	        var template = this.options.get('templateResult');
	        var escapeMarkup = this.options.get('escapeMarkup');

	        var content = template(result, container);

	        if (content == null) {
	          container.style.display = 'none';
	        } else if (typeof content === 'string') {
	          container.innerHTML = escapeMarkup(content);
	        } else {
	          $(container).append(content);
	        }
	      };

	      return Results;
	    });

	    S2.define('select2/keys', [], function () {
	      var KEYS = {
	        BACKSPACE: 8,
	        TAB: 9,
	        ENTER: 13,
	        SHIFT: 16,
	        CTRL: 17,
	        ALT: 18,
	        ESC: 27,
	        SPACE: 32,
	        PAGE_UP: 33,
	        PAGE_DOWN: 34,
	        END: 35,
	        HOME: 36,
	        LEFT: 37,
	        UP: 38,
	        RIGHT: 39,
	        DOWN: 40,
	        DELETE: 46
	      };

	      return KEYS;
	    });

	    S2.define('select2/selection/base', ['jquery', '../utils', '../keys'], function ($, Utils, KEYS) {
	      function BaseSelection($element, options) {
	        this.$element = $element;
	        this.options = options;

	        BaseSelection.__super__.constructor.call(this);
	      }

	      Utils.Extend(BaseSelection, Utils.Observable);

	      BaseSelection.prototype.render = function () {
	        var $selection = $('<span class="select2-selection" role="combobox" ' + ' aria-haspopup="true" aria-expanded="false">' + '</span>');

	        this._tabindex = 0;

	        if (this.$element.data('old-tabindex') != null) {
	          this._tabindex = this.$element.data('old-tabindex');
	        } else if (this.$element.attr('tabindex') != null) {
	          this._tabindex = this.$element.attr('tabindex');
	        }

	        $selection.attr('title', this.$element.attr('title'));
	        $selection.attr('tabindex', this._tabindex);

	        this.$selection = $selection;

	        return $selection;
	      };

	      BaseSelection.prototype.bind = function (container, $container) {
	        var self = this;

	        var id = container.id + '-container';
	        var resultsId = container.id + '-results';

	        this.container = container;

	        this.$selection.on('focus', function (evt) {
	          self.trigger('focus', evt);
	        });

	        this.$selection.on('blur', function (evt) {
	          self._handleBlur(evt);
	        });

	        this.$selection.on('keydown', function (evt) {
	          self.trigger('keypress', evt);

	          if (evt.which === KEYS.SPACE) {
	            evt.preventDefault();
	          }
	        });

	        container.on('results:focus', function (params) {
	          self.$selection.attr('aria-activedescendant', params.data._resultId);
	        });

	        container.on('selection:update', function (params) {
	          self.update(params.data);
	        });

	        container.on('open', function () {
	          // When the dropdown is open, aria-expanded="true"
	          self.$selection.attr('aria-expanded', 'true');
	          self.$selection.attr('aria-owns', resultsId);

	          self._attachCloseHandler(container);
	        });

	        container.on('close', function () {
	          // When the dropdown is closed, aria-expanded="false"
	          self.$selection.attr('aria-expanded', 'false');
	          self.$selection.removeAttr('aria-activedescendant');
	          self.$selection.removeAttr('aria-owns');

	          self.$selection.focus();

	          self._detachCloseHandler(container);
	        });

	        container.on('enable', function () {
	          self.$selection.attr('tabindex', self._tabindex);
	        });

	        container.on('disable', function () {
	          self.$selection.attr('tabindex', '-1');
	        });
	      };

	      BaseSelection.prototype._handleBlur = function (evt) {
	        var self = this;

	        // This needs to be delayed as the active element is the body when the tab
	        // key is pressed, possibly along with others.
	        window.setTimeout(function () {
	          // Don't trigger `blur` if the focus is still in the selection
	          if (document.activeElement == self.$selection[0] || $.contains(self.$selection[0], document.activeElement)) {
	            return;
	          }

	          self.trigger('blur', evt);
	        }, 1);
	      };

	      BaseSelection.prototype._attachCloseHandler = function (container) {
	        var self = this;

	        $(document.body).on('mousedown.select2.' + container.id, function (e) {
	          var $target = $(e.target);

	          var $select = $target.closest('.select2');

	          var $all = $('.select2.select2-container--open');

	          $all.each(function () {
	            var $this = $(this);

	            if (this == $select[0]) {
	              return;
	            }

	            var $element = $this.data('element');

	            $element.select2('close');
	          });
	        });
	      };

	      BaseSelection.prototype._detachCloseHandler = function (container) {
	        $(document.body).off('mousedown.select2.' + container.id);
	      };

	      BaseSelection.prototype.position = function ($selection, $container) {
	        var $selectionContainer = $container.find('.selection');
	        $selectionContainer.append($selection);
	      };

	      BaseSelection.prototype.destroy = function () {
	        this._detachCloseHandler(this.container);
	      };

	      BaseSelection.prototype.update = function (data) {
	        throw new Error('The `update` method must be defined in child classes.');
	      };

	      return BaseSelection;
	    });

	    S2.define('select2/selection/single', ['jquery', './base', '../utils', '../keys'], function ($, BaseSelection, Utils, KEYS) {
	      function SingleSelection() {
	        SingleSelection.__super__.constructor.apply(this, arguments);
	      }

	      Utils.Extend(SingleSelection, BaseSelection);

	      SingleSelection.prototype.render = function () {
	        var $selection = SingleSelection.__super__.render.call(this);

	        $selection.addClass('select2-selection--single');

	        $selection.html('<span class="select2-selection__rendered"></span>' + '<span class="select2-selection__arrow" role="presentation">' + '<b role="presentation"></b>' + '</span>');

	        return $selection;
	      };

	      SingleSelection.prototype.bind = function (container, $container) {
	        var self = this;

	        SingleSelection.__super__.bind.apply(this, arguments);

	        var id = container.id + '-container';

	        this.$selection.find('.select2-selection__rendered').attr('id', id);
	        this.$selection.attr('aria-labelledby', id);

	        this.$selection.on('mousedown', function (evt) {
	          // Only respond to left clicks
	          if (evt.which !== 1) {
	            return;
	          }

	          self.trigger('toggle', {
	            originalEvent: evt
	          });
	        });

	        this.$selection.on('focus', function (evt) {
	          // User focuses on the container
	        });

	        this.$selection.on('blur', function (evt) {
	          // User exits the container
	        });

	        container.on('focus', function (evt) {
	          if (!container.isOpen()) {
	            self.$selection.focus();
	          }
	        });

	        container.on('selection:update', function (params) {
	          self.update(params.data);
	        });
	      };

	      SingleSelection.prototype.clear = function () {
	        this.$selection.find('.select2-selection__rendered').empty();
	      };

	      SingleSelection.prototype.display = function (data, container) {
	        var template = this.options.get('templateSelection');
	        var escapeMarkup = this.options.get('escapeMarkup');

	        return escapeMarkup(template(data, container));
	      };

	      SingleSelection.prototype.selectionContainer = function () {
	        return $('<span></span>');
	      };

	      SingleSelection.prototype.update = function (data) {
	        if (data.length === 0) {
	          this.clear();
	          return;
	        }

	        var selection = data[0];

	        var $rendered = this.$selection.find('.select2-selection__rendered');
	        var formatted = this.display(selection, $rendered);

	        $rendered.empty().append(formatted);
	        $rendered.prop('title', selection.title || selection.text);
	      };

	      return SingleSelection;
	    });

	    S2.define('select2/selection/multiple', ['jquery', './base', '../utils'], function ($, BaseSelection, Utils) {
	      function MultipleSelection($element, options) {
	        MultipleSelection.__super__.constructor.apply(this, arguments);
	      }

	      Utils.Extend(MultipleSelection, BaseSelection);

	      MultipleSelection.prototype.render = function () {
	        var $selection = MultipleSelection.__super__.render.call(this);

	        $selection.addClass('select2-selection--multiple');

	        $selection.html('<ul class="select2-selection__rendered"></ul>');

	        return $selection;
	      };

	      MultipleSelection.prototype.bind = function (container, $container) {
	        var self = this;

	        MultipleSelection.__super__.bind.apply(this, arguments);

	        this.$selection.on('click', function (evt) {
	          self.trigger('toggle', {
	            originalEvent: evt
	          });
	        });

	        this.$selection.on('click', '.select2-selection__choice__remove', function (evt) {
	          // Ignore the event if it is disabled
	          if (self.options.get('disabled')) {
	            return;
	          }

	          var $remove = $(this);
	          var $selection = $remove.parent();

	          var data = $selection.data('data');

	          self.trigger('unselect', {
	            originalEvent: evt,
	            data: data
	          });
	        });
	      };

	      MultipleSelection.prototype.clear = function () {
	        this.$selection.find('.select2-selection__rendered').empty();
	      };

	      MultipleSelection.prototype.display = function (data, container) {
	        var template = this.options.get('templateSelection');
	        var escapeMarkup = this.options.get('escapeMarkup');

	        return escapeMarkup(template(data, container));
	      };

	      MultipleSelection.prototype.selectionContainer = function () {
	        var $container = $('<li class="select2-selection__choice">' + '<span class="select2-selection__choice__remove" role="presentation">' + '&times;' + '</span>' + '</li>');

	        return $container;
	      };

	      MultipleSelection.prototype.update = function (data) {
	        this.clear();

	        if (data.length === 0) {
	          return;
	        }

	        var $selections = [];

	        for (var d = 0; d < data.length; d++) {
	          var selection = data[d];

	          var $selection = this.selectionContainer();
	          var formatted = this.display(selection, $selection);

	          $selection.append(formatted);
	          $selection.prop('title', selection.title || selection.text);

	          $selection.data('data', selection);

	          $selections.push($selection);
	        }

	        var $rendered = this.$selection.find('.select2-selection__rendered');

	        Utils.appendMany($rendered, $selections);
	      };

	      return MultipleSelection;
	    });

	    S2.define('select2/selection/placeholder', ['../utils'], function (Utils) {
	      function Placeholder(decorated, $element, options) {
	        this.placeholder = this.normalizePlaceholder(options.get('placeholder'));

	        decorated.call(this, $element, options);
	      }

	      Placeholder.prototype.normalizePlaceholder = function (_, placeholder) {
	        if (typeof placeholder === 'string') {
	          placeholder = {
	            id: '',
	            text: placeholder
	          };
	        }

	        return placeholder;
	      };

	      Placeholder.prototype.createPlaceholder = function (decorated, placeholder) {
	        var $placeholder = this.selectionContainer();

	        $placeholder.html(this.display(placeholder));
	        $placeholder.addClass('select2-selection__placeholder').removeClass('select2-selection__choice');

	        return $placeholder;
	      };

	      Placeholder.prototype.update = function (decorated, data) {
	        var singlePlaceholder = data.length == 1 && data[0].id != this.placeholder.id;
	        var multipleSelections = data.length > 1;

	        if (multipleSelections || singlePlaceholder) {
	          return decorated.call(this, data);
	        }

	        this.clear();

	        var $placeholder = this.createPlaceholder(this.placeholder);

	        this.$selection.find('.select2-selection__rendered').append($placeholder);
	      };

	      return Placeholder;
	    });

	    S2.define('select2/selection/allowClear', ['jquery', '../keys'], function ($, KEYS) {
	      function AllowClear() {}

	      AllowClear.prototype.bind = function (decorated, container, $container) {
	        var self = this;

	        decorated.call(this, container, $container);

	        if (this.placeholder == null) {
	          if (this.options.get('debug') && window.console && console.error) {
	            console.error('Select2: The `allowClear` option should be used in combination ' + 'with the `placeholder` option.');
	          }
	        }

	        this.$selection.on('mousedown', '.select2-selection__clear', function (evt) {
	          self._handleClear(evt);
	        });

	        container.on('keypress', function (evt) {
	          self._handleKeyboardClear(evt, container);
	        });
	      };

	      AllowClear.prototype._handleClear = function (_, evt) {
	        // Ignore the event if it is disabled
	        if (this.options.get('disabled')) {
	          return;
	        }

	        var $clear = this.$selection.find('.select2-selection__clear');

	        // Ignore the event if nothing has been selected
	        if ($clear.length === 0) {
	          return;
	        }

	        evt.stopPropagation();

	        var data = $clear.data('data');

	        for (var d = 0; d < data.length; d++) {
	          var unselectData = {
	            data: data[d]
	          };

	          // Trigger the `unselect` event, so people can prevent it from being
	          // cleared.
	          this.trigger('unselect', unselectData);

	          // If the event was prevented, don't clear it out.
	          if (unselectData.prevented) {
	            return;
	          }
	        }

	        this.$element.val(this.placeholder.id).trigger('change');

	        this.trigger('toggle', {});
	      };

	      AllowClear.prototype._handleKeyboardClear = function (_, evt, container) {
	        if (container.isOpen()) {
	          return;
	        }

	        if (evt.which == KEYS.DELETE || evt.which == KEYS.BACKSPACE) {
	          this._handleClear(evt);
	        }
	      };

	      AllowClear.prototype.update = function (decorated, data) {
	        decorated.call(this, data);

	        if (this.$selection.find('.select2-selection__placeholder').length > 0 || data.length === 0) {
	          return;
	        }

	        var $remove = $('<span class="select2-selection__clear">' + '&times;' + '</span>');
	        $remove.data('data', data);

	        this.$selection.find('.select2-selection__rendered').prepend($remove);
	      };

	      return AllowClear;
	    });

	    S2.define('select2/selection/search', ['jquery', '../utils', '../keys'], function ($, Utils, KEYS) {
	      function Search(decorated, $element, options) {
	        decorated.call(this, $element, options);
	      }

	      Search.prototype.render = function (decorated) {
	        var $search = $('<li class="select2-search select2-search--inline">' + '<input class="select2-search__field" type="search" tabindex="-1"' + ' autocomplete="off" autocorrect="off" autocapitalize="off"' + ' spellcheck="false" role="textbox" aria-autocomplete="list" />' + '</li>');

	        this.$searchContainer = $search;
	        this.$search = $search.find('input');

	        var $rendered = decorated.call(this);

	        this._transferTabIndex();

	        return $rendered;
	      };

	      Search.prototype.bind = function (decorated, container, $container) {
	        var self = this;

	        decorated.call(this, container, $container);

	        container.on('open', function () {
	          self.$search.trigger('focus');
	        });

	        container.on('close', function () {
	          self.$search.val('');
	          self.$search.removeAttr('aria-activedescendant');
	          self.$search.trigger('focus');
	        });

	        container.on('enable', function () {
	          self.$search.prop('disabled', false);

	          self._transferTabIndex();
	        });

	        container.on('disable', function () {
	          self.$search.prop('disabled', true);
	        });

	        container.on('focus', function (evt) {
	          self.$search.trigger('focus');
	        });

	        container.on('results:focus', function (params) {
	          self.$search.attr('aria-activedescendant', params.id);
	        });

	        this.$selection.on('focusin', '.select2-search--inline', function (evt) {
	          self.trigger('focus', evt);
	        });

	        this.$selection.on('focusout', '.select2-search--inline', function (evt) {
	          self._handleBlur(evt);
	        });

	        this.$selection.on('keydown', '.select2-search--inline', function (evt) {
	          evt.stopPropagation();

	          self.trigger('keypress', evt);

	          self._keyUpPrevented = evt.isDefaultPrevented();

	          var key = evt.which;

	          if (key === KEYS.BACKSPACE && self.$search.val() === '') {
	            var $previousChoice = self.$searchContainer.prev('.select2-selection__choice');

	            if ($previousChoice.length > 0) {
	              var item = $previousChoice.data('data');

	              self.searchRemoveChoice(item);

	              evt.preventDefault();
	            }
	          }
	        });

	        // Try to detect the IE version should the `documentMode` property that
	        // is stored on the document. This is only implemented in IE and is
	        // slightly cleaner than doing a user agent check.
	        // This property is not available in Edge, but Edge also doesn't have
	        // this bug.
	        var msie = document.documentMode;
	        var disableInputEvents = msie && msie <= 11;

	        // Workaround for browsers which do not support the `input` event
	        // This will prevent double-triggering of events for browsers which support
	        // both the `keyup` and `input` events.
	        this.$selection.on('input.searchcheck', '.select2-search--inline', function (evt) {
	          // IE will trigger the `input` event when a placeholder is used on a
	          // search box. To get around this issue, we are forced to ignore all
	          // `input` events in IE and keep using `keyup`.
	          if (disableInputEvents) {
	            self.$selection.off('input.search input.searchcheck');
	            return;
	          }

	          // Unbind the duplicated `keyup` event
	          self.$selection.off('keyup.search');
	        });

	        this.$selection.on('keyup.search input.search', '.select2-search--inline', function (evt) {
	          // IE will trigger the `input` event when a placeholder is used on a
	          // search box. To get around this issue, we are forced to ignore all
	          // `input` events in IE and keep using `keyup`.
	          if (disableInputEvents && evt.type === 'input') {
	            self.$selection.off('input.search input.searchcheck');
	            return;
	          }

	          var key = evt.which;

	          // We can freely ignore events from modifier keys
	          if (key == KEYS.SHIFT || key == KEYS.CTRL || key == KEYS.ALT) {
	            return;
	          }

	          // Tabbing will be handled during the `keydown` phase
	          if (key == KEYS.TAB) {
	            return;
	          }

	          self.handleSearch(evt);
	        });
	      };

	      /**
	       * This method will transfer the tabindex attribute from the rendered
	       * selection to the search box. This allows for the search box to be used as
	       * the primary focus instead of the selection container.
	       *
	       * @private
	       */
	      Search.prototype._transferTabIndex = function (decorated) {
	        this.$search.attr('tabindex', this.$selection.attr('tabindex'));
	        this.$selection.attr('tabindex', '-1');
	      };

	      Search.prototype.createPlaceholder = function (decorated, placeholder) {
	        this.$search.attr('placeholder', placeholder.text);
	      };

	      Search.prototype.update = function (decorated, data) {
	        var searchHadFocus = this.$search[0] == document.activeElement;

	        this.$search.attr('placeholder', '');

	        decorated.call(this, data);

	        this.$selection.find('.select2-selection__rendered').append(this.$searchContainer);

	        this.resizeSearch();
	        if (searchHadFocus) {
	          this.$search.focus();
	        }
	      };

	      Search.prototype.handleSearch = function () {
	        this.resizeSearch();

	        if (!this._keyUpPrevented) {
	          var input = this.$search.val();

	          this.trigger('query', {
	            term: input
	          });
	        }

	        this._keyUpPrevented = false;
	      };

	      Search.prototype.searchRemoveChoice = function (decorated, item) {
	        this.trigger('unselect', {
	          data: item
	        });

	        this.$search.val(item.text);
	        this.handleSearch();
	      };

	      Search.prototype.resizeSearch = function () {
	        this.$search.css('width', '25px');

	        var width = '';

	        if (this.$search.attr('placeholder') !== '') {
	          width = this.$selection.find('.select2-selection__rendered').innerWidth();
	        } else {
	          var minimumWidth = this.$search.val().length + 1;

	          width = minimumWidth * 0.75 + 'em';
	        }

	        this.$search.css('width', width);
	      };

	      return Search;
	    });

	    S2.define('select2/selection/eventRelay', ['jquery'], function ($) {
	      function EventRelay() {}

	      EventRelay.prototype.bind = function (decorated, container, $container) {
	        var self = this;
	        var relayEvents = ['open', 'opening', 'close', 'closing', 'select', 'selecting', 'unselect', 'unselecting'];

	        var preventableEvents = ['opening', 'closing', 'selecting', 'unselecting'];

	        decorated.call(this, container, $container);

	        container.on('*', function (name, params) {
	          // Ignore events that should not be relayed
	          if ($.inArray(name, relayEvents) === -1) {
	            return;
	          }

	          // The parameters should always be an object
	          params = params || {};

	          // Generate the jQuery event for the Select2 event
	          var evt = $.Event('select2:' + name, {
	            params: params
	          });

	          self.$element.trigger(evt);

	          // Only handle preventable events if it was one
	          if ($.inArray(name, preventableEvents) === -1) {
	            return;
	          }

	          params.prevented = evt.isDefaultPrevented();
	        });
	      };

	      return EventRelay;
	    });

	    S2.define('select2/translation', ['jquery', 'require'], function ($, require) {
	      function Translation(dict) {
	        this.dict = dict || {};
	      }

	      Translation.prototype.all = function () {
	        return this.dict;
	      };

	      Translation.prototype.get = function (key) {
	        return this.dict[key];
	      };

	      Translation.prototype.extend = function (translation) {
	        this.dict = $.extend({}, translation.all(), this.dict);
	      };

	      // Static functions

	      Translation._cache = {};

	      Translation.loadPath = function (path) {
	        if (!(path in Translation._cache)) {
	          var translations = require(path);

	          Translation._cache[path] = translations;
	        }

	        return new Translation(Translation._cache[path]);
	      };

	      return Translation;
	    });

	    S2.define('select2/diacritics', [], function () {
	      var diacritics = {
	        '\u24B6': 'A',
	        '\uFF21': 'A',
	        '\xC0': 'A',
	        '\xC1': 'A',
	        '\xC2': 'A',
	        '\u1EA6': 'A',
	        '\u1EA4': 'A',
	        '\u1EAA': 'A',
	        '\u1EA8': 'A',
	        '\xC3': 'A',
	        '\u0100': 'A',
	        '\u0102': 'A',
	        '\u1EB0': 'A',
	        '\u1EAE': 'A',
	        '\u1EB4': 'A',
	        '\u1EB2': 'A',
	        '\u0226': 'A',
	        '\u01E0': 'A',
	        '\xC4': 'A',
	        '\u01DE': 'A',
	        '\u1EA2': 'A',
	        '\xC5': 'A',
	        '\u01FA': 'A',
	        '\u01CD': 'A',
	        '\u0200': 'A',
	        '\u0202': 'A',
	        '\u1EA0': 'A',
	        '\u1EAC': 'A',
	        '\u1EB6': 'A',
	        '\u1E00': 'A',
	        '\u0104': 'A',
	        '\u023A': 'A',
	        '\u2C6F': 'A',
	        '\uA732': 'AA',
	        '\xC6': 'AE',
	        '\u01FC': 'AE',
	        '\u01E2': 'AE',
	        '\uA734': 'AO',
	        '\uA736': 'AU',
	        '\uA738': 'AV',
	        '\uA73A': 'AV',
	        '\uA73C': 'AY',
	        '\u24B7': 'B',
	        '\uFF22': 'B',
	        '\u1E02': 'B',
	        '\u1E04': 'B',
	        '\u1E06': 'B',
	        '\u0243': 'B',
	        '\u0182': 'B',
	        '\u0181': 'B',
	        '\u24B8': 'C',
	        '\uFF23': 'C',
	        '\u0106': 'C',
	        '\u0108': 'C',
	        '\u010A': 'C',
	        '\u010C': 'C',
	        '\xC7': 'C',
	        '\u1E08': 'C',
	        '\u0187': 'C',
	        '\u023B': 'C',
	        '\uA73E': 'C',
	        '\u24B9': 'D',
	        '\uFF24': 'D',
	        '\u1E0A': 'D',
	        '\u010E': 'D',
	        '\u1E0C': 'D',
	        '\u1E10': 'D',
	        '\u1E12': 'D',
	        '\u1E0E': 'D',
	        '\u0110': 'D',
	        '\u018B': 'D',
	        '\u018A': 'D',
	        '\u0189': 'D',
	        '\uA779': 'D',
	        '\u01F1': 'DZ',
	        '\u01C4': 'DZ',
	        '\u01F2': 'Dz',
	        '\u01C5': 'Dz',
	        '\u24BA': 'E',
	        '\uFF25': 'E',
	        '\xC8': 'E',
	        '\xC9': 'E',
	        '\xCA': 'E',
	        '\u1EC0': 'E',
	        '\u1EBE': 'E',
	        '\u1EC4': 'E',
	        '\u1EC2': 'E',
	        '\u1EBC': 'E',
	        '\u0112': 'E',
	        '\u1E14': 'E',
	        '\u1E16': 'E',
	        '\u0114': 'E',
	        '\u0116': 'E',
	        '\xCB': 'E',
	        '\u1EBA': 'E',
	        '\u011A': 'E',
	        '\u0204': 'E',
	        '\u0206': 'E',
	        '\u1EB8': 'E',
	        '\u1EC6': 'E',
	        '\u0228': 'E',
	        '\u1E1C': 'E',
	        '\u0118': 'E',
	        '\u1E18': 'E',
	        '\u1E1A': 'E',
	        '\u0190': 'E',
	        '\u018E': 'E',
	        '\u24BB': 'F',
	        '\uFF26': 'F',
	        '\u1E1E': 'F',
	        '\u0191': 'F',
	        '\uA77B': 'F',
	        '\u24BC': 'G',
	        '\uFF27': 'G',
	        '\u01F4': 'G',
	        '\u011C': 'G',
	        '\u1E20': 'G',
	        '\u011E': 'G',
	        '\u0120': 'G',
	        '\u01E6': 'G',
	        '\u0122': 'G',
	        '\u01E4': 'G',
	        '\u0193': 'G',
	        '\uA7A0': 'G',
	        '\uA77D': 'G',
	        '\uA77E': 'G',
	        '\u24BD': 'H',
	        '\uFF28': 'H',
	        '\u0124': 'H',
	        '\u1E22': 'H',
	        '\u1E26': 'H',
	        '\u021E': 'H',
	        '\u1E24': 'H',
	        '\u1E28': 'H',
	        '\u1E2A': 'H',
	        '\u0126': 'H',
	        '\u2C67': 'H',
	        '\u2C75': 'H',
	        '\uA78D': 'H',
	        '\u24BE': 'I',
	        '\uFF29': 'I',
	        '\xCC': 'I',
	        '\xCD': 'I',
	        '\xCE': 'I',
	        '\u0128': 'I',
	        '\u012A': 'I',
	        '\u012C': 'I',
	        '\u0130': 'I',
	        '\xCF': 'I',
	        '\u1E2E': 'I',
	        '\u1EC8': 'I',
	        '\u01CF': 'I',
	        '\u0208': 'I',
	        '\u020A': 'I',
	        '\u1ECA': 'I',
	        '\u012E': 'I',
	        '\u1E2C': 'I',
	        '\u0197': 'I',
	        '\u24BF': 'J',
	        '\uFF2A': 'J',
	        '\u0134': 'J',
	        '\u0248': 'J',
	        '\u24C0': 'K',
	        '\uFF2B': 'K',
	        '\u1E30': 'K',
	        '\u01E8': 'K',
	        '\u1E32': 'K',
	        '\u0136': 'K',
	        '\u1E34': 'K',
	        '\u0198': 'K',
	        '\u2C69': 'K',
	        '\uA740': 'K',
	        '\uA742': 'K',
	        '\uA744': 'K',
	        '\uA7A2': 'K',
	        '\u24C1': 'L',
	        '\uFF2C': 'L',
	        '\u013F': 'L',
	        '\u0139': 'L',
	        '\u013D': 'L',
	        '\u1E36': 'L',
	        '\u1E38': 'L',
	        '\u013B': 'L',
	        '\u1E3C': 'L',
	        '\u1E3A': 'L',
	        '\u0141': 'L',
	        '\u023D': 'L',
	        '\u2C62': 'L',
	        '\u2C60': 'L',
	        '\uA748': 'L',
	        '\uA746': 'L',
	        '\uA780': 'L',
	        '\u01C7': 'LJ',
	        '\u01C8': 'Lj',
	        '\u24C2': 'M',
	        '\uFF2D': 'M',
	        '\u1E3E': 'M',
	        '\u1E40': 'M',
	        '\u1E42': 'M',
	        '\u2C6E': 'M',
	        '\u019C': 'M',
	        '\u24C3': 'N',
	        '\uFF2E': 'N',
	        '\u01F8': 'N',
	        '\u0143': 'N',
	        '\xD1': 'N',
	        '\u1E44': 'N',
	        '\u0147': 'N',
	        '\u1E46': 'N',
	        '\u0145': 'N',
	        '\u1E4A': 'N',
	        '\u1E48': 'N',
	        '\u0220': 'N',
	        '\u019D': 'N',
	        '\uA790': 'N',
	        '\uA7A4': 'N',
	        '\u01CA': 'NJ',
	        '\u01CB': 'Nj',
	        '\u24C4': 'O',
	        '\uFF2F': 'O',
	        '\xD2': 'O',
	        '\xD3': 'O',
	        '\xD4': 'O',
	        '\u1ED2': 'O',
	        '\u1ED0': 'O',
	        '\u1ED6': 'O',
	        '\u1ED4': 'O',
	        '\xD5': 'O',
	        '\u1E4C': 'O',
	        '\u022C': 'O',
	        '\u1E4E': 'O',
	        '\u014C': 'O',
	        '\u1E50': 'O',
	        '\u1E52': 'O',
	        '\u014E': 'O',
	        '\u022E': 'O',
	        '\u0230': 'O',
	        '\xD6': 'O',
	        '\u022A': 'O',
	        '\u1ECE': 'O',
	        '\u0150': 'O',
	        '\u01D1': 'O',
	        '\u020C': 'O',
	        '\u020E': 'O',
	        '\u01A0': 'O',
	        '\u1EDC': 'O',
	        '\u1EDA': 'O',
	        '\u1EE0': 'O',
	        '\u1EDE': 'O',
	        '\u1EE2': 'O',
	        '\u1ECC': 'O',
	        '\u1ED8': 'O',
	        '\u01EA': 'O',
	        '\u01EC': 'O',
	        '\xD8': 'O',
	        '\u01FE': 'O',
	        '\u0186': 'O',
	        '\u019F': 'O',
	        '\uA74A': 'O',
	        '\uA74C': 'O',
	        '\u01A2': 'OI',
	        '\uA74E': 'OO',
	        '\u0222': 'OU',
	        '\u24C5': 'P',
	        '\uFF30': 'P',
	        '\u1E54': 'P',
	        '\u1E56': 'P',
	        '\u01A4': 'P',
	        '\u2C63': 'P',
	        '\uA750': 'P',
	        '\uA752': 'P',
	        '\uA754': 'P',
	        '\u24C6': 'Q',
	        '\uFF31': 'Q',
	        '\uA756': 'Q',
	        '\uA758': 'Q',
	        '\u024A': 'Q',
	        '\u24C7': 'R',
	        '\uFF32': 'R',
	        '\u0154': 'R',
	        '\u1E58': 'R',
	        '\u0158': 'R',
	        '\u0210': 'R',
	        '\u0212': 'R',
	        '\u1E5A': 'R',
	        '\u1E5C': 'R',
	        '\u0156': 'R',
	        '\u1E5E': 'R',
	        '\u024C': 'R',
	        '\u2C64': 'R',
	        '\uA75A': 'R',
	        '\uA7A6': 'R',
	        '\uA782': 'R',
	        '\u24C8': 'S',
	        '\uFF33': 'S',
	        '\u1E9E': 'S',
	        '\u015A': 'S',
	        '\u1E64': 'S',
	        '\u015C': 'S',
	        '\u1E60': 'S',
	        '\u0160': 'S',
	        '\u1E66': 'S',
	        '\u1E62': 'S',
	        '\u1E68': 'S',
	        '\u0218': 'S',
	        '\u015E': 'S',
	        '\u2C7E': 'S',
	        '\uA7A8': 'S',
	        '\uA784': 'S',
	        '\u24C9': 'T',
	        '\uFF34': 'T',
	        '\u1E6A': 'T',
	        '\u0164': 'T',
	        '\u1E6C': 'T',
	        '\u021A': 'T',
	        '\u0162': 'T',
	        '\u1E70': 'T',
	        '\u1E6E': 'T',
	        '\u0166': 'T',
	        '\u01AC': 'T',
	        '\u01AE': 'T',
	        '\u023E': 'T',
	        '\uA786': 'T',
	        '\uA728': 'TZ',
	        '\u24CA': 'U',
	        '\uFF35': 'U',
	        '\xD9': 'U',
	        '\xDA': 'U',
	        '\xDB': 'U',
	        '\u0168': 'U',
	        '\u1E78': 'U',
	        '\u016A': 'U',
	        '\u1E7A': 'U',
	        '\u016C': 'U',
	        '\xDC': 'U',
	        '\u01DB': 'U',
	        '\u01D7': 'U',
	        '\u01D5': 'U',
	        '\u01D9': 'U',
	        '\u1EE6': 'U',
	        '\u016E': 'U',
	        '\u0170': 'U',
	        '\u01D3': 'U',
	        '\u0214': 'U',
	        '\u0216': 'U',
	        '\u01AF': 'U',
	        '\u1EEA': 'U',
	        '\u1EE8': 'U',
	        '\u1EEE': 'U',
	        '\u1EEC': 'U',
	        '\u1EF0': 'U',
	        '\u1EE4': 'U',
	        '\u1E72': 'U',
	        '\u0172': 'U',
	        '\u1E76': 'U',
	        '\u1E74': 'U',
	        '\u0244': 'U',
	        '\u24CB': 'V',
	        '\uFF36': 'V',
	        '\u1E7C': 'V',
	        '\u1E7E': 'V',
	        '\u01B2': 'V',
	        '\uA75E': 'V',
	        '\u0245': 'V',
	        '\uA760': 'VY',
	        '\u24CC': 'W',
	        '\uFF37': 'W',
	        '\u1E80': 'W',
	        '\u1E82': 'W',
	        '\u0174': 'W',
	        '\u1E86': 'W',
	        '\u1E84': 'W',
	        '\u1E88': 'W',
	        '\u2C72': 'W',
	        '\u24CD': 'X',
	        '\uFF38': 'X',
	        '\u1E8A': 'X',
	        '\u1E8C': 'X',
	        '\u24CE': 'Y',
	        '\uFF39': 'Y',
	        '\u1EF2': 'Y',
	        '\xDD': 'Y',
	        '\u0176': 'Y',
	        '\u1EF8': 'Y',
	        '\u0232': 'Y',
	        '\u1E8E': 'Y',
	        '\u0178': 'Y',
	        '\u1EF6': 'Y',
	        '\u1EF4': 'Y',
	        '\u01B3': 'Y',
	        '\u024E': 'Y',
	        '\u1EFE': 'Y',
	        '\u24CF': 'Z',
	        '\uFF3A': 'Z',
	        '\u0179': 'Z',
	        '\u1E90': 'Z',
	        '\u017B': 'Z',
	        '\u017D': 'Z',
	        '\u1E92': 'Z',
	        '\u1E94': 'Z',
	        '\u01B5': 'Z',
	        '\u0224': 'Z',
	        '\u2C7F': 'Z',
	        '\u2C6B': 'Z',
	        '\uA762': 'Z',
	        '\u24D0': 'a',
	        '\uFF41': 'a',
	        '\u1E9A': 'a',
	        '\xE0': 'a',
	        '\xE1': 'a',
	        '\xE2': 'a',
	        '\u1EA7': 'a',
	        '\u1EA5': 'a',
	        '\u1EAB': 'a',
	        '\u1EA9': 'a',
	        '\xE3': 'a',
	        '\u0101': 'a',
	        '\u0103': 'a',
	        '\u1EB1': 'a',
	        '\u1EAF': 'a',
	        '\u1EB5': 'a',
	        '\u1EB3': 'a',
	        '\u0227': 'a',
	        '\u01E1': 'a',
	        '\xE4': 'a',
	        '\u01DF': 'a',
	        '\u1EA3': 'a',
	        '\xE5': 'a',
	        '\u01FB': 'a',
	        '\u01CE': 'a',
	        '\u0201': 'a',
	        '\u0203': 'a',
	        '\u1EA1': 'a',
	        '\u1EAD': 'a',
	        '\u1EB7': 'a',
	        '\u1E01': 'a',
	        '\u0105': 'a',
	        '\u2C65': 'a',
	        '\u0250': 'a',
	        '\uA733': 'aa',
	        '\xE6': 'ae',
	        '\u01FD': 'ae',
	        '\u01E3': 'ae',
	        '\uA735': 'ao',
	        '\uA737': 'au',
	        '\uA739': 'av',
	        '\uA73B': 'av',
	        '\uA73D': 'ay',
	        '\u24D1': 'b',
	        '\uFF42': 'b',
	        '\u1E03': 'b',
	        '\u1E05': 'b',
	        '\u1E07': 'b',
	        '\u0180': 'b',
	        '\u0183': 'b',
	        '\u0253': 'b',
	        '\u24D2': 'c',
	        '\uFF43': 'c',
	        '\u0107': 'c',
	        '\u0109': 'c',
	        '\u010B': 'c',
	        '\u010D': 'c',
	        '\xE7': 'c',
	        '\u1E09': 'c',
	        '\u0188': 'c',
	        '\u023C': 'c',
	        '\uA73F': 'c',
	        '\u2184': 'c',
	        '\u24D3': 'd',
	        '\uFF44': 'd',
	        '\u1E0B': 'd',
	        '\u010F': 'd',
	        '\u1E0D': 'd',
	        '\u1E11': 'd',
	        '\u1E13': 'd',
	        '\u1E0F': 'd',
	        '\u0111': 'd',
	        '\u018C': 'd',
	        '\u0256': 'd',
	        '\u0257': 'd',
	        '\uA77A': 'd',
	        '\u01F3': 'dz',
	        '\u01C6': 'dz',
	        '\u24D4': 'e',
	        '\uFF45': 'e',
	        '\xE8': 'e',
	        '\xE9': 'e',
	        '\xEA': 'e',
	        '\u1EC1': 'e',
	        '\u1EBF': 'e',
	        '\u1EC5': 'e',
	        '\u1EC3': 'e',
	        '\u1EBD': 'e',
	        '\u0113': 'e',
	        '\u1E15': 'e',
	        '\u1E17': 'e',
	        '\u0115': 'e',
	        '\u0117': 'e',
	        '\xEB': 'e',
	        '\u1EBB': 'e',
	        '\u011B': 'e',
	        '\u0205': 'e',
	        '\u0207': 'e',
	        '\u1EB9': 'e',
	        '\u1EC7': 'e',
	        '\u0229': 'e',
	        '\u1E1D': 'e',
	        '\u0119': 'e',
	        '\u1E19': 'e',
	        '\u1E1B': 'e',
	        '\u0247': 'e',
	        '\u025B': 'e',
	        '\u01DD': 'e',
	        '\u24D5': 'f',
	        '\uFF46': 'f',
	        '\u1E1F': 'f',
	        '\u0192': 'f',
	        '\uA77C': 'f',
	        '\u24D6': 'g',
	        '\uFF47': 'g',
	        '\u01F5': 'g',
	        '\u011D': 'g',
	        '\u1E21': 'g',
	        '\u011F': 'g',
	        '\u0121': 'g',
	        '\u01E7': 'g',
	        '\u0123': 'g',
	        '\u01E5': 'g',
	        '\u0260': 'g',
	        '\uA7A1': 'g',
	        '\u1D79': 'g',
	        '\uA77F': 'g',
	        '\u24D7': 'h',
	        '\uFF48': 'h',
	        '\u0125': 'h',
	        '\u1E23': 'h',
	        '\u1E27': 'h',
	        '\u021F': 'h',
	        '\u1E25': 'h',
	        '\u1E29': 'h',
	        '\u1E2B': 'h',
	        '\u1E96': 'h',
	        '\u0127': 'h',
	        '\u2C68': 'h',
	        '\u2C76': 'h',
	        '\u0265': 'h',
	        '\u0195': 'hv',
	        '\u24D8': 'i',
	        '\uFF49': 'i',
	        '\xEC': 'i',
	        '\xED': 'i',
	        '\xEE': 'i',
	        '\u0129': 'i',
	        '\u012B': 'i',
	        '\u012D': 'i',
	        '\xEF': 'i',
	        '\u1E2F': 'i',
	        '\u1EC9': 'i',
	        '\u01D0': 'i',
	        '\u0209': 'i',
	        '\u020B': 'i',
	        '\u1ECB': 'i',
	        '\u012F': 'i',
	        '\u1E2D': 'i',
	        '\u0268': 'i',
	        '\u0131': 'i',
	        '\u24D9': 'j',
	        '\uFF4A': 'j',
	        '\u0135': 'j',
	        '\u01F0': 'j',
	        '\u0249': 'j',
	        '\u24DA': 'k',
	        '\uFF4B': 'k',
	        '\u1E31': 'k',
	        '\u01E9': 'k',
	        '\u1E33': 'k',
	        '\u0137': 'k',
	        '\u1E35': 'k',
	        '\u0199': 'k',
	        '\u2C6A': 'k',
	        '\uA741': 'k',
	        '\uA743': 'k',
	        '\uA745': 'k',
	        '\uA7A3': 'k',
	        '\u24DB': 'l',
	        '\uFF4C': 'l',
	        '\u0140': 'l',
	        '\u013A': 'l',
	        '\u013E': 'l',
	        '\u1E37': 'l',
	        '\u1E39': 'l',
	        '\u013C': 'l',
	        '\u1E3D': 'l',
	        '\u1E3B': 'l',
	        '\u017F': 'l',
	        '\u0142': 'l',
	        '\u019A': 'l',
	        '\u026B': 'l',
	        '\u2C61': 'l',
	        '\uA749': 'l',
	        '\uA781': 'l',
	        '\uA747': 'l',
	        '\u01C9': 'lj',
	        '\u24DC': 'm',
	        '\uFF4D': 'm',
	        '\u1E3F': 'm',
	        '\u1E41': 'm',
	        '\u1E43': 'm',
	        '\u0271': 'm',
	        '\u026F': 'm',
	        '\u24DD': 'n',
	        '\uFF4E': 'n',
	        '\u01F9': 'n',
	        '\u0144': 'n',
	        '\xF1': 'n',
	        '\u1E45': 'n',
	        '\u0148': 'n',
	        '\u1E47': 'n',
	        '\u0146': 'n',
	        '\u1E4B': 'n',
	        '\u1E49': 'n',
	        '\u019E': 'n',
	        '\u0272': 'n',
	        '\u0149': 'n',
	        '\uA791': 'n',
	        '\uA7A5': 'n',
	        '\u01CC': 'nj',
	        '\u24DE': 'o',
	        '\uFF4F': 'o',
	        '\xF2': 'o',
	        '\xF3': 'o',
	        '\xF4': 'o',
	        '\u1ED3': 'o',
	        '\u1ED1': 'o',
	        '\u1ED7': 'o',
	        '\u1ED5': 'o',
	        '\xF5': 'o',
	        '\u1E4D': 'o',
	        '\u022D': 'o',
	        '\u1E4F': 'o',
	        '\u014D': 'o',
	        '\u1E51': 'o',
	        '\u1E53': 'o',
	        '\u014F': 'o',
	        '\u022F': 'o',
	        '\u0231': 'o',
	        '\xF6': 'o',
	        '\u022B': 'o',
	        '\u1ECF': 'o',
	        '\u0151': 'o',
	        '\u01D2': 'o',
	        '\u020D': 'o',
	        '\u020F': 'o',
	        '\u01A1': 'o',
	        '\u1EDD': 'o',
	        '\u1EDB': 'o',
	        '\u1EE1': 'o',
	        '\u1EDF': 'o',
	        '\u1EE3': 'o',
	        '\u1ECD': 'o',
	        '\u1ED9': 'o',
	        '\u01EB': 'o',
	        '\u01ED': 'o',
	        '\xF8': 'o',
	        '\u01FF': 'o',
	        '\u0254': 'o',
	        '\uA74B': 'o',
	        '\uA74D': 'o',
	        '\u0275': 'o',
	        '\u01A3': 'oi',
	        '\u0223': 'ou',
	        '\uA74F': 'oo',
	        '\u24DF': 'p',
	        '\uFF50': 'p',
	        '\u1E55': 'p',
	        '\u1E57': 'p',
	        '\u01A5': 'p',
	        '\u1D7D': 'p',
	        '\uA751': 'p',
	        '\uA753': 'p',
	        '\uA755': 'p',
	        '\u24E0': 'q',
	        '\uFF51': 'q',
	        '\u024B': 'q',
	        '\uA757': 'q',
	        '\uA759': 'q',
	        '\u24E1': 'r',
	        '\uFF52': 'r',
	        '\u0155': 'r',
	        '\u1E59': 'r',
	        '\u0159': 'r',
	        '\u0211': 'r',
	        '\u0213': 'r',
	        '\u1E5B': 'r',
	        '\u1E5D': 'r',
	        '\u0157': 'r',
	        '\u1E5F': 'r',
	        '\u024D': 'r',
	        '\u027D': 'r',
	        '\uA75B': 'r',
	        '\uA7A7': 'r',
	        '\uA783': 'r',
	        '\u24E2': 's',
	        '\uFF53': 's',
	        '\xDF': 's',
	        '\u015B': 's',
	        '\u1E65': 's',
	        '\u015D': 's',
	        '\u1E61': 's',
	        '\u0161': 's',
	        '\u1E67': 's',
	        '\u1E63': 's',
	        '\u1E69': 's',
	        '\u0219': 's',
	        '\u015F': 's',
	        '\u023F': 's',
	        '\uA7A9': 's',
	        '\uA785': 's',
	        '\u1E9B': 's',
	        '\u24E3': 't',
	        '\uFF54': 't',
	        '\u1E6B': 't',
	        '\u1E97': 't',
	        '\u0165': 't',
	        '\u1E6D': 't',
	        '\u021B': 't',
	        '\u0163': 't',
	        '\u1E71': 't',
	        '\u1E6F': 't',
	        '\u0167': 't',
	        '\u01AD': 't',
	        '\u0288': 't',
	        '\u2C66': 't',
	        '\uA787': 't',
	        '\uA729': 'tz',
	        '\u24E4': 'u',
	        '\uFF55': 'u',
	        '\xF9': 'u',
	        '\xFA': 'u',
	        '\xFB': 'u',
	        '\u0169': 'u',
	        '\u1E79': 'u',
	        '\u016B': 'u',
	        '\u1E7B': 'u',
	        '\u016D': 'u',
	        '\xFC': 'u',
	        '\u01DC': 'u',
	        '\u01D8': 'u',
	        '\u01D6': 'u',
	        '\u01DA': 'u',
	        '\u1EE7': 'u',
	        '\u016F': 'u',
	        '\u0171': 'u',
	        '\u01D4': 'u',
	        '\u0215': 'u',
	        '\u0217': 'u',
	        '\u01B0': 'u',
	        '\u1EEB': 'u',
	        '\u1EE9': 'u',
	        '\u1EEF': 'u',
	        '\u1EED': 'u',
	        '\u1EF1': 'u',
	        '\u1EE5': 'u',
	        '\u1E73': 'u',
	        '\u0173': 'u',
	        '\u1E77': 'u',
	        '\u1E75': 'u',
	        '\u0289': 'u',
	        '\u24E5': 'v',
	        '\uFF56': 'v',
	        '\u1E7D': 'v',
	        '\u1E7F': 'v',
	        '\u028B': 'v',
	        '\uA75F': 'v',
	        '\u028C': 'v',
	        '\uA761': 'vy',
	        '\u24E6': 'w',
	        '\uFF57': 'w',
	        '\u1E81': 'w',
	        '\u1E83': 'w',
	        '\u0175': 'w',
	        '\u1E87': 'w',
	        '\u1E85': 'w',
	        '\u1E98': 'w',
	        '\u1E89': 'w',
	        '\u2C73': 'w',
	        '\u24E7': 'x',
	        '\uFF58': 'x',
	        '\u1E8B': 'x',
	        '\u1E8D': 'x',
	        '\u24E8': 'y',
	        '\uFF59': 'y',
	        '\u1EF3': 'y',
	        '\xFD': 'y',
	        '\u0177': 'y',
	        '\u1EF9': 'y',
	        '\u0233': 'y',
	        '\u1E8F': 'y',
	        '\xFF': 'y',
	        '\u1EF7': 'y',
	        '\u1E99': 'y',
	        '\u1EF5': 'y',
	        '\u01B4': 'y',
	        '\u024F': 'y',
	        '\u1EFF': 'y',
	        '\u24E9': 'z',
	        '\uFF5A': 'z',
	        '\u017A': 'z',
	        '\u1E91': 'z',
	        '\u017C': 'z',
	        '\u017E': 'z',
	        '\u1E93': 'z',
	        '\u1E95': 'z',
	        '\u01B6': 'z',
	        '\u0225': 'z',
	        '\u0240': 'z',
	        '\u2C6C': 'z',
	        '\uA763': 'z',
	        '\u0386': '\u0391',
	        '\u0388': '\u0395',
	        '\u0389': '\u0397',
	        '\u038A': '\u0399',
	        '\u03AA': '\u0399',
	        '\u038C': '\u039F',
	        '\u038E': '\u03A5',
	        '\u03AB': '\u03A5',
	        '\u038F': '\u03A9',
	        '\u03AC': '\u03B1',
	        '\u03AD': '\u03B5',
	        '\u03AE': '\u03B7',
	        '\u03AF': '\u03B9',
	        '\u03CA': '\u03B9',
	        '\u0390': '\u03B9',
	        '\u03CC': '\u03BF',
	        '\u03CD': '\u03C5',
	        '\u03CB': '\u03C5',
	        '\u03B0': '\u03C5',
	        '\u03C9': '\u03C9',
	        '\u03C2': '\u03C3'
	      };

	      return diacritics;
	    });

	    S2.define('select2/data/base', ['../utils'], function (Utils) {
	      function BaseAdapter($element, options) {
	        BaseAdapter.__super__.constructor.call(this);
	      }

	      Utils.Extend(BaseAdapter, Utils.Observable);

	      BaseAdapter.prototype.current = function (callback) {
	        throw new Error('The `current` method must be defined in child classes.');
	      };

	      BaseAdapter.prototype.query = function (params, callback) {
	        throw new Error('The `query` method must be defined in child classes.');
	      };

	      BaseAdapter.prototype.bind = function (container, $container) {
	        // Can be implemented in subclasses
	      };

	      BaseAdapter.prototype.destroy = function () {
	        // Can be implemented in subclasses
	      };

	      BaseAdapter.prototype.generateResultId = function (container, data) {
	        var id = container.id + '-result-';

	        id += Utils.generateChars(4);

	        if (data.id != null) {
	          id += '-' + data.id.toString();
	        } else {
	          id += '-' + Utils.generateChars(4);
	        }
	        return id;
	      };

	      return BaseAdapter;
	    });

	    S2.define('select2/data/select', ['./base', '../utils', 'jquery'], function (BaseAdapter, Utils, $) {
	      function SelectAdapter($element, options) {
	        this.$element = $element;
	        this.options = options;

	        SelectAdapter.__super__.constructor.call(this);
	      }

	      Utils.Extend(SelectAdapter, BaseAdapter);

	      SelectAdapter.prototype.current = function (callback) {
	        var data = [];
	        var self = this;

	        this.$element.find(':selected').each(function () {
	          var $option = $(this);

	          var option = self.item($option);

	          data.push(option);
	        });

	        callback(data);
	      };

	      SelectAdapter.prototype.select = function (data) {
	        var self = this;

	        data.selected = true;

	        // If data.element is a DOM node, use it instead
	        if ($(data.element).is('option')) {
	          data.element.selected = true;

	          this.$element.trigger('change');

	          return;
	        }

	        if (this.$element.prop('multiple')) {
	          this.current(function (currentData) {
	            var val = [];

	            data = [data];
	            data.push.apply(data, currentData);

	            for (var d = 0; d < data.length; d++) {
	              var id = data[d].id;

	              if ($.inArray(id, val) === -1) {
	                val.push(id);
	              }
	            }

	            self.$element.val(val);
	            self.$element.trigger('change');
	          });
	        } else {
	          var val = data.id;

	          this.$element.val(val);
	          this.$element.trigger('change');
	        }
	      };

	      SelectAdapter.prototype.unselect = function (data) {
	        var self = this;

	        if (!this.$element.prop('multiple')) {
	          return;
	        }

	        data.selected = false;

	        if ($(data.element).is('option')) {
	          data.element.selected = false;

	          this.$element.trigger('change');

	          return;
	        }

	        this.current(function (currentData) {
	          var val = [];

	          for (var d = 0; d < currentData.length; d++) {
	            var id = currentData[d].id;

	            if (id !== data.id && $.inArray(id, val) === -1) {
	              val.push(id);
	            }
	          }

	          self.$element.val(val);

	          self.$element.trigger('change');
	        });
	      };

	      SelectAdapter.prototype.bind = function (container, $container) {
	        var self = this;

	        this.container = container;

	        container.on('select', function (params) {
	          self.select(params.data);
	        });

	        container.on('unselect', function (params) {
	          self.unselect(params.data);
	        });
	      };

	      SelectAdapter.prototype.destroy = function () {
	        // Remove anything added to child elements
	        this.$element.find('*').each(function () {
	          // Remove any custom data set by Select2
	          $.removeData(this, 'data');
	        });
	      };

	      SelectAdapter.prototype.query = function (params, callback) {
	        var data = [];
	        var self = this;

	        var $options = this.$element.children();

	        $options.each(function () {
	          var $option = $(this);

	          if (!$option.is('option') && !$option.is('optgroup')) {
	            return;
	          }

	          var option = self.item($option);

	          var matches = self.matches(params, option);

	          if (matches !== null) {
	            data.push(matches);
	          }
	        });

	        callback({
	          results: data
	        });
	      };

	      SelectAdapter.prototype.addOptions = function ($options) {
	        Utils.appendMany(this.$element, $options);
	      };

	      SelectAdapter.prototype.option = function (data) {
	        var option;

	        if (data.children) {
	          option = document.createElement('optgroup');
	          option.label = data.text;
	        } else {
	          option = document.createElement('option');

	          if (option.textContent !== undefined) {
	            option.textContent = data.text;
	          } else {
	            option.innerText = data.text;
	          }
	        }

	        if (data.id) {
	          option.value = data.id;
	        }

	        if (data.disabled) {
	          option.disabled = true;
	        }

	        if (data.selected) {
	          option.selected = true;
	        }

	        if (data.title) {
	          option.title = data.title;
	        }

	        var $option = $(option);

	        var normalizedData = this._normalizeItem(data);
	        normalizedData.element = option;

	        // Override the option's data with the combined data
	        $.data(option, 'data', normalizedData);

	        return $option;
	      };

	      SelectAdapter.prototype.item = function ($option) {
	        var data = {};

	        data = $.data($option[0], 'data');

	        if (data != null) {
	          return data;
	        }

	        if ($option.is('option')) {
	          data = {
	            id: $option.val(),
	            text: $option.text(),
	            disabled: $option.prop('disabled'),
	            selected: $option.prop('selected'),
	            title: $option.prop('title')
	          };
	        } else if ($option.is('optgroup')) {
	          data = {
	            text: $option.prop('label'),
	            children: [],
	            title: $option.prop('title')
	          };

	          var $children = $option.children('option');
	          var children = [];

	          for (var c = 0; c < $children.length; c++) {
	            var $child = $($children[c]);

	            var child = this.item($child);

	            children.push(child);
	          }

	          data.children = children;
	        }

	        data = this._normalizeItem(data);
	        data.element = $option[0];

	        $.data($option[0], 'data', data);

	        return data;
	      };

	      SelectAdapter.prototype._normalizeItem = function (item) {
	        if (!$.isPlainObject(item)) {
	          item = {
	            id: item,
	            text: item
	          };
	        }

	        item = $.extend({}, {
	          text: ''
	        }, item);

	        var defaults = {
	          selected: false,
	          disabled: false
	        };

	        if (item.id != null) {
	          item.id = item.id.toString();
	        }

	        if (item.text != null) {
	          item.text = item.text.toString();
	        }

	        if (item._resultId == null && item.id && this.container != null) {
	          item._resultId = this.generateResultId(this.container, item);
	        }

	        return $.extend({}, defaults, item);
	      };

	      SelectAdapter.prototype.matches = function (params, data) {
	        var matcher = this.options.get('matcher');

	        return matcher(params, data);
	      };

	      return SelectAdapter;
	    });

	    S2.define('select2/data/array', ['./select', '../utils', 'jquery'], function (SelectAdapter, Utils, $) {
	      function ArrayAdapter($element, options) {
	        var data = options.get('data') || [];

	        ArrayAdapter.__super__.constructor.call(this, $element, options);

	        this.addOptions(this.convertToOptions(data));
	      }

	      Utils.Extend(ArrayAdapter, SelectAdapter);

	      ArrayAdapter.prototype.select = function (data) {
	        var $option = this.$element.find('option').filter(function (i, elm) {
	          return elm.value == data.id.toString();
	        });

	        if ($option.length === 0) {
	          $option = this.option(data);

	          this.addOptions($option);
	        }

	        ArrayAdapter.__super__.select.call(this, data);
	      };

	      ArrayAdapter.prototype.convertToOptions = function (data) {
	        var self = this;

	        var $existing = this.$element.find('option');
	        var existingIds = $existing.map(function () {
	          return self.item($(this)).id;
	        }).get();

	        var $options = [];

	        // Filter out all items except for the one passed in the argument
	        function onlyItem(item) {
	          return function () {
	            return $(this).val() == item.id;
	          };
	        }

	        for (var d = 0; d < data.length; d++) {
	          var item = this._normalizeItem(data[d]);

	          // Skip items which were pre-loaded, only merge the data
	          if ($.inArray(item.id, existingIds) >= 0) {
	            var $existingOption = $existing.filter(onlyItem(item));

	            var existingData = this.item($existingOption);
	            var newData = $.extend(true, {}, item, existingData);

	            var $newOption = this.option(newData);

	            $existingOption.replaceWith($newOption);

	            continue;
	          }

	          var $option = this.option(item);

	          if (item.children) {
	            var $children = this.convertToOptions(item.children);

	            Utils.appendMany($option, $children);
	          }

	          $options.push($option);
	        }

	        return $options;
	      };

	      return ArrayAdapter;
	    });

	    S2.define('select2/data/ajax', ['./array', '../utils', 'jquery'], function (ArrayAdapter, Utils, $) {
	      function AjaxAdapter($element, options) {
	        this.ajaxOptions = this._applyDefaults(options.get('ajax'));

	        if (this.ajaxOptions.processResults != null) {
	          this.processResults = this.ajaxOptions.processResults;
	        }

	        AjaxAdapter.__super__.constructor.call(this, $element, options);
	      }

	      Utils.Extend(AjaxAdapter, ArrayAdapter);

	      AjaxAdapter.prototype._applyDefaults = function (options) {
	        var defaults = {
	          data: function data(params) {
	            return $.extend({}, params, {
	              q: params.term
	            });
	          },
	          transport: function transport(params, success, failure) {
	            var $request = $.ajax(params);

	            $request.then(success);
	            $request.fail(failure);

	            return $request;
	          }
	        };

	        return $.extend({}, defaults, options, true);
	      };

	      AjaxAdapter.prototype.processResults = function (results) {
	        return results;
	      };

	      AjaxAdapter.prototype.query = function (params, callback) {
	        var matches = [];
	        var self = this;

	        if (this._request != null) {
	          // JSONP requests cannot always be aborted
	          if ($.isFunction(this._request.abort)) {
	            this._request.abort();
	          }

	          this._request = null;
	        }

	        var options = $.extend({
	          type: 'GET'
	        }, this.ajaxOptions);

	        if (typeof options.url === 'function') {
	          options.url = options.url.call(this.$element, params);
	        }

	        if (typeof options.data === 'function') {
	          options.data = options.data.call(this.$element, params);
	        }

	        function request() {
	          var $request = options.transport(options, function (data) {
	            var results = self.processResults(data, params);

	            if (self.options.get('debug') && window.console && console.error) {
	              // Check to make sure that the response included a `results` key.
	              if (!results || !results.results || !$.isArray(results.results)) {
	                console.error('Select2: The AJAX results did not return an array in the ' + '`results` key of the response.');
	              }
	            }

	            callback(results);
	          }, function () {
	            // Attempt to detect if a request was aborted
	            // Only works if the transport exposes a status property
	            if ($request.status && $request.status === '0') {
	              return;
	            }

	            self.trigger('results:message', {
	              message: 'errorLoading'
	            });
	          });

	          self._request = $request;
	        }

	        if (this.ajaxOptions.delay && params.term != null) {
	          if (this._queryTimeout) {
	            window.clearTimeout(this._queryTimeout);
	          }

	          this._queryTimeout = window.setTimeout(request, this.ajaxOptions.delay);
	        } else {
	          request();
	        }
	      };

	      return AjaxAdapter;
	    });

	    S2.define('select2/data/tags', ['jquery'], function ($) {
	      function Tags(decorated, $element, options) {
	        var tags = options.get('tags');

	        var createTag = options.get('createTag');

	        if (createTag !== undefined) {
	          this.createTag = createTag;
	        }

	        var insertTag = options.get('insertTag');

	        if (insertTag !== undefined) {
	          this.insertTag = insertTag;
	        }

	        decorated.call(this, $element, options);

	        if ($.isArray(tags)) {
	          for (var t = 0; t < tags.length; t++) {
	            var tag = tags[t];
	            var item = this._normalizeItem(tag);

	            var $option = this.option(item);

	            this.$element.append($option);
	          }
	        }
	      }

	      Tags.prototype.query = function (decorated, params, callback) {
	        var self = this;

	        this._removeOldTags();

	        if (params.term == null || params.page != null) {
	          decorated.call(this, params, callback);
	          return;
	        }

	        function wrapper(obj, child) {
	          var data = obj.results;

	          for (var i = 0; i < data.length; i++) {
	            var option = data[i];

	            var checkChildren = option.children != null && !wrapper({
	              results: option.children
	            }, true);

	            var checkText = option.text === params.term;

	            if (checkText || checkChildren) {
	              if (child) {
	                return false;
	              }

	              obj.data = data;
	              callback(obj);

	              return;
	            }
	          }

	          if (child) {
	            return true;
	          }

	          var tag = self.createTag(params);

	          if (tag != null) {
	            var $option = self.option(tag);
	            $option.attr('data-select2-tag', true);

	            self.addOptions([$option]);

	            self.insertTag(data, tag);
	          }

	          obj.results = data;

	          callback(obj);
	        }

	        decorated.call(this, params, wrapper);
	      };

	      Tags.prototype.createTag = function (decorated, params) {
	        var term = $.trim(params.term);

	        if (term === '') {
	          return null;
	        }

	        return {
	          id: term,
	          text: term
	        };
	      };

	      Tags.prototype.insertTag = function (_, data, tag) {
	        data.unshift(tag);
	      };

	      Tags.prototype._removeOldTags = function (_) {
	        var tag = this._lastTag;

	        var $options = this.$element.find('option[data-select2-tag]');

	        $options.each(function () {
	          if (this.selected) {
	            return;
	          }

	          $(this).remove();
	        });
	      };

	      return Tags;
	    });

	    S2.define('select2/data/tokenizer', ['jquery'], function ($) {
	      function Tokenizer(decorated, $element, options) {
	        var tokenizer = options.get('tokenizer');

	        if (tokenizer !== undefined) {
	          this.tokenizer = tokenizer;
	        }

	        decorated.call(this, $element, options);
	      }

	      Tokenizer.prototype.bind = function (decorated, container, $container) {
	        decorated.call(this, container, $container);

	        this.$search = container.dropdown.$search || container.selection.$search || $container.find('.select2-search__field');
	      };

	      Tokenizer.prototype.query = function (decorated, params, callback) {
	        var self = this;

	        function createAndSelect(data) {
	          // Normalize the data object so we can use it for checks
	          var item = self._normalizeItem(data);

	          // Check if the data object already exists as a tag
	          // Select it if it doesn't
	          var $existingOptions = self.$element.find('option').filter(function () {
	            return $(this).val() === item.id;
	          });

	          // If an existing option wasn't found for it, create the option
	          if (!$existingOptions.length) {
	            var $option = self.option(item);
	            $option.attr('data-select2-tag', true);

	            self._removeOldTags();
	            self.addOptions([$option]);
	          }

	          // Select the item, now that we know there is an option for it
	          select(item);
	        }

	        function select(data) {
	          self.trigger('select', {
	            data: data
	          });
	        }

	        params.term = params.term || '';

	        var tokenData = this.tokenizer(params, this.options, createAndSelect);

	        if (tokenData.term !== params.term) {
	          // Replace the search term if we have the search box
	          if (this.$search.length) {
	            this.$search.val(tokenData.term);
	            this.$search.focus();
	          }

	          params.term = tokenData.term;
	        }

	        decorated.call(this, params, callback);
	      };

	      Tokenizer.prototype.tokenizer = function (_, params, options, callback) {
	        var separators = options.get('tokenSeparators') || [];
	        var term = params.term;
	        var i = 0;

	        var createTag = this.createTag || function (params) {
	          return {
	            id: params.term,
	            text: params.term
	          };
	        };

	        while (i < term.length) {
	          var termChar = term[i];

	          if ($.inArray(termChar, separators) === -1) {
	            i++;

	            continue;
	          }

	          var part = term.substr(0, i);
	          var partParams = $.extend({}, params, {
	            term: part
	          });

	          var data = createTag(partParams);

	          if (data == null) {
	            i++;
	            continue;
	          }

	          callback(data);

	          // Reset the term to not include the tokenized portion
	          term = term.substr(i + 1) || '';
	          i = 0;
	        }

	        return {
	          term: term
	        };
	      };

	      return Tokenizer;
	    });

	    S2.define('select2/data/minimumInputLength', [], function () {
	      function MinimumInputLength(decorated, $e, options) {
	        this.minimumInputLength = options.get('minimumInputLength');

	        decorated.call(this, $e, options);
	      }

	      MinimumInputLength.prototype.query = function (decorated, params, callback) {
	        params.term = params.term || '';

	        if (params.term.length < this.minimumInputLength) {
	          this.trigger('results:message', {
	            message: 'inputTooShort',
	            args: {
	              minimum: this.minimumInputLength,
	              input: params.term,
	              params: params
	            }
	          });

	          return;
	        }

	        decorated.call(this, params, callback);
	      };

	      return MinimumInputLength;
	    });

	    S2.define('select2/data/maximumInputLength', [], function () {
	      function MaximumInputLength(decorated, $e, options) {
	        this.maximumInputLength = options.get('maximumInputLength');

	        decorated.call(this, $e, options);
	      }

	      MaximumInputLength.prototype.query = function (decorated, params, callback) {
	        params.term = params.term || '';

	        if (this.maximumInputLength > 0 && params.term.length > this.maximumInputLength) {
	          this.trigger('results:message', {
	            message: 'inputTooLong',
	            args: {
	              maximum: this.maximumInputLength,
	              input: params.term,
	              params: params
	            }
	          });

	          return;
	        }

	        decorated.call(this, params, callback);
	      };

	      return MaximumInputLength;
	    });

	    S2.define('select2/data/maximumSelectionLength', [], function () {
	      function MaximumSelectionLength(decorated, $e, options) {
	        this.maximumSelectionLength = options.get('maximumSelectionLength');

	        decorated.call(this, $e, options);
	      }

	      MaximumSelectionLength.prototype.query = function (decorated, params, callback) {
	        var self = this;

	        this.current(function (currentData) {
	          var count = currentData != null ? currentData.length : 0;
	          if (self.maximumSelectionLength > 0 && count >= self.maximumSelectionLength) {
	            self.trigger('results:message', {
	              message: 'maximumSelected',
	              args: {
	                maximum: self.maximumSelectionLength
	              }
	            });
	            return;
	          }
	          decorated.call(self, params, callback);
	        });
	      };

	      return MaximumSelectionLength;
	    });

	    S2.define('select2/dropdown', ['jquery', './utils'], function ($, Utils) {
	      function Dropdown($element, options) {
	        this.$element = $element;
	        this.options = options;

	        Dropdown.__super__.constructor.call(this);
	      }

	      Utils.Extend(Dropdown, Utils.Observable);

	      Dropdown.prototype.render = function () {
	        var $dropdown = $('<span class="select2-dropdown">' + '<span class="select2-results"></span>' + '</span>');

	        $dropdown.attr('dir', this.options.get('dir'));

	        this.$dropdown = $dropdown;

	        return $dropdown;
	      };

	      Dropdown.prototype.bind = function () {
	        // Should be implemented in subclasses
	      };

	      Dropdown.prototype.position = function ($dropdown, $container) {
	        // Should be implmented in subclasses
	      };

	      Dropdown.prototype.destroy = function () {
	        // Remove the dropdown from the DOM
	        this.$dropdown.remove();
	      };

	      return Dropdown;
	    });

	    S2.define('select2/dropdown/search', ['jquery', '../utils'], function ($, Utils) {
	      function Search() {}

	      Search.prototype.render = function (decorated) {
	        var $rendered = decorated.call(this);

	        var $search = $('<span class="select2-search select2-search--dropdown">' + '<input class="select2-search__field" type="search" tabindex="-1"' + ' autocomplete="off" autocorrect="off" autocapitalize="off"' + ' spellcheck="false" role="textbox" />' + '</span>');

	        this.$searchContainer = $search;
	        this.$search = $search.find('input');

	        $rendered.prepend($search);

	        return $rendered;
	      };

	      Search.prototype.bind = function (decorated, container, $container) {
	        var self = this;

	        decorated.call(this, container, $container);

	        this.$search.on('keydown', function (evt) {
	          self.trigger('keypress', evt);

	          self._keyUpPrevented = evt.isDefaultPrevented();
	        });

	        // Workaround for browsers which do not support the `input` event
	        // This will prevent double-triggering of events for browsers which support
	        // both the `keyup` and `input` events.
	        this.$search.on('input', function (evt) {
	          // Unbind the duplicated `keyup` event
	          $(this).off('keyup');
	        });

	        this.$search.on('keyup input', function (evt) {
	          self.handleSearch(evt);
	        });

	        container.on('open', function () {
	          self.$search.attr('tabindex', 0);

	          self.$search.focus();

	          window.setTimeout(function () {
	            self.$search.focus();
	          }, 0);
	        });

	        container.on('close', function () {
	          self.$search.attr('tabindex', -1);

	          self.$search.val('');
	        });

	        container.on('focus', function () {
	          if (container.isOpen()) {
	            self.$search.focus();
	          }
	        });

	        container.on('results:all', function (params) {
	          if (params.query.term == null || params.query.term === '') {
	            var showSearch = self.showSearch(params);

	            if (showSearch) {
	              self.$searchContainer.removeClass('select2-search--hide');
	            } else {
	              self.$searchContainer.addClass('select2-search--hide');
	            }
	          }
	        });
	      };

	      Search.prototype.handleSearch = function (evt) {
	        if (!this._keyUpPrevented) {
	          var input = this.$search.val();

	          this.trigger('query', {
	            term: input
	          });
	        }

	        this._keyUpPrevented = false;
	      };

	      Search.prototype.showSearch = function (_, params) {
	        return true;
	      };

	      return Search;
	    });

	    S2.define('select2/dropdown/hidePlaceholder', [], function () {
	      function HidePlaceholder(decorated, $element, options, dataAdapter) {
	        this.placeholder = this.normalizePlaceholder(options.get('placeholder'));

	        decorated.call(this, $element, options, dataAdapter);
	      }

	      HidePlaceholder.prototype.append = function (decorated, data) {
	        data.results = this.removePlaceholder(data.results);

	        decorated.call(this, data);
	      };

	      HidePlaceholder.prototype.normalizePlaceholder = function (_, placeholder) {
	        if (typeof placeholder === 'string') {
	          placeholder = {
	            id: '',
	            text: placeholder
	          };
	        }

	        return placeholder;
	      };

	      HidePlaceholder.prototype.removePlaceholder = function (_, data) {
	        var modifiedData = data.slice(0);

	        for (var d = data.length - 1; d >= 0; d--) {
	          var item = data[d];

	          if (this.placeholder.id === item.id) {
	            modifiedData.splice(d, 1);
	          }
	        }

	        return modifiedData;
	      };

	      return HidePlaceholder;
	    });

	    S2.define('select2/dropdown/infiniteScroll', ['jquery'], function ($) {
	      function InfiniteScroll(decorated, $element, options, dataAdapter) {
	        this.lastParams = {};

	        decorated.call(this, $element, options, dataAdapter);

	        this.$loadingMore = this.createLoadingMore();
	        this.loading = false;
	      }

	      InfiniteScroll.prototype.append = function (decorated, data) {
	        this.$loadingMore.remove();
	        this.loading = false;

	        decorated.call(this, data);

	        if (this.showLoadingMore(data)) {
	          this.$results.append(this.$loadingMore);
	        }
	      };

	      InfiniteScroll.prototype.bind = function (decorated, container, $container) {
	        var self = this;

	        decorated.call(this, container, $container);

	        container.on('query', function (params) {
	          self.lastParams = params;
	          self.loading = true;
	        });

	        container.on('query:append', function (params) {
	          self.lastParams = params;
	          self.loading = true;
	        });

	        this.$results.on('scroll', function () {
	          var isLoadMoreVisible = $.contains(document.documentElement, self.$loadingMore[0]);

	          if (self.loading || !isLoadMoreVisible) {
	            return;
	          }

	          var currentOffset = self.$results.offset().top + self.$results.outerHeight(false);
	          var loadingMoreOffset = self.$loadingMore.offset().top + self.$loadingMore.outerHeight(false);

	          if (currentOffset + 50 >= loadingMoreOffset) {
	            self.loadMore();
	          }
	        });
	      };

	      InfiniteScroll.prototype.loadMore = function () {
	        this.loading = true;

	        var params = $.extend({}, { page: 1 }, this.lastParams);

	        params.page++;

	        this.trigger('query:append', params);
	      };

	      InfiniteScroll.prototype.showLoadingMore = function (_, data) {
	        return data.pagination && data.pagination.more;
	      };

	      InfiniteScroll.prototype.createLoadingMore = function () {
	        var $option = $('<li ' + 'class="select2-results__option select2-results__option--load-more"' + 'role="treeitem" aria-disabled="true"></li>');

	        var message = this.options.get('translations').get('loadingMore');

	        $option.html(message(this.lastParams));

	        return $option;
	      };

	      return InfiniteScroll;
	    });

	    S2.define('select2/dropdown/attachBody', ['jquery', '../utils'], function ($, Utils) {
	      function AttachBody(decorated, $element, options) {
	        this.$dropdownParent = options.get('dropdownParent') || $(document.body);

	        decorated.call(this, $element, options);
	      }

	      AttachBody.prototype.bind = function (decorated, container, $container) {
	        var self = this;

	        var setupResultsEvents = false;

	        decorated.call(this, container, $container);

	        container.on('open', function () {
	          self._showDropdown();
	          self._attachPositioningHandler(container);

	          if (!setupResultsEvents) {
	            setupResultsEvents = true;

	            container.on('results:all', function () {
	              self._positionDropdown();
	              self._resizeDropdown();
	            });

	            container.on('results:append', function () {
	              self._positionDropdown();
	              self._resizeDropdown();
	            });
	          }
	        });

	        container.on('close', function () {
	          self._hideDropdown();
	          self._detachPositioningHandler(container);
	        });

	        this.$dropdownContainer.on('mousedown', function (evt) {
	          evt.stopPropagation();
	        });
	      };

	      AttachBody.prototype.destroy = function (decorated) {
	        decorated.call(this);

	        this.$dropdownContainer.remove();
	      };

	      AttachBody.prototype.position = function (decorated, $dropdown, $container) {
	        // Clone all of the container classes
	        $dropdown.attr('class', $container.attr('class'));

	        $dropdown.removeClass('select2');
	        $dropdown.addClass('select2-container--open');

	        $dropdown.css({
	          position: 'absolute',
	          top: -999999
	        });

	        this.$container = $container;
	      };

	      AttachBody.prototype.render = function (decorated) {
	        var $container = $('<span></span>');

	        var $dropdown = decorated.call(this);
	        $container.append($dropdown);

	        this.$dropdownContainer = $container;

	        return $container;
	      };

	      AttachBody.prototype._hideDropdown = function (decorated) {
	        this.$dropdownContainer.detach();
	      };

	      AttachBody.prototype._attachPositioningHandler = function (decorated, container) {
	        var self = this;

	        var scrollEvent = 'scroll.select2.' + container.id;
	        var resizeEvent = 'resize.select2.' + container.id;
	        var orientationEvent = 'orientationchange.select2.' + container.id;

	        var $watchers = this.$container.parents().filter(Utils.hasScroll);
	        $watchers.each(function () {
	          $(this).data('select2-scroll-position', {
	            x: $(this).scrollLeft(),
	            y: $(this).scrollTop()
	          });
	        });

	        $watchers.on(scrollEvent, function (ev) {
	          var position = $(this).data('select2-scroll-position');
	          $(this).scrollTop(position.y);
	        });

	        $(window).on(scrollEvent + ' ' + resizeEvent + ' ' + orientationEvent, function (e) {
	          self._positionDropdown();
	          self._resizeDropdown();
	        });
	      };

	      AttachBody.prototype._detachPositioningHandler = function (decorated, container) {
	        var scrollEvent = 'scroll.select2.' + container.id;
	        var resizeEvent = 'resize.select2.' + container.id;
	        var orientationEvent = 'orientationchange.select2.' + container.id;

	        var $watchers = this.$container.parents().filter(Utils.hasScroll);
	        $watchers.off(scrollEvent);

	        $(window).off(scrollEvent + ' ' + resizeEvent + ' ' + orientationEvent);
	      };

	      AttachBody.prototype._positionDropdown = function () {
	        var $window = $(window);

	        var isCurrentlyAbove = this.$dropdown.hasClass('select2-dropdown--above');
	        var isCurrentlyBelow = this.$dropdown.hasClass('select2-dropdown--below');

	        var newDirection = null;

	        var offset = this.$container.offset();

	        offset.bottom = offset.top + this.$container.outerHeight(false);

	        var container = {
	          height: this.$container.outerHeight(false)
	        };

	        container.top = offset.top;
	        container.bottom = offset.top + container.height;

	        var dropdown = {
	          height: this.$dropdown.outerHeight(false)
	        };

	        var viewport = {
	          top: $window.scrollTop(),
	          bottom: $window.scrollTop() + $window.height()
	        };

	        var enoughRoomAbove = viewport.top < offset.top - dropdown.height;
	        var enoughRoomBelow = viewport.bottom > offset.bottom + dropdown.height;

	        var css = {
	          left: offset.left,
	          top: container.bottom
	        };

	        // Determine what the parent element is to use for calciulating the offset
	        var $offsetParent = this.$dropdownParent;

	        // For statically positoned elements, we need to get the element
	        // that is determining the offset
	        if ($offsetParent.css('position') === 'static') {
	          $offsetParent = $offsetParent.offsetParent();
	        }

	        var parentOffset = $offsetParent.offset();

	        css.top -= parentOffset.top;
	        css.left -= parentOffset.left;

	        if (!isCurrentlyAbove && !isCurrentlyBelow) {
	          newDirection = 'below';
	        }

	        if (!enoughRoomBelow && enoughRoomAbove && !isCurrentlyAbove) {
	          newDirection = 'above';
	        } else if (!enoughRoomAbove && enoughRoomBelow && isCurrentlyAbove) {
	          newDirection = 'below';
	        }

	        if (newDirection == 'above' || isCurrentlyAbove && newDirection !== 'below') {
	          css.top = container.top - parentOffset.top - dropdown.height;
	        }

	        if (newDirection != null) {
	          this.$dropdown.removeClass('select2-dropdown--below select2-dropdown--above').addClass('select2-dropdown--' + newDirection);
	          this.$container.removeClass('select2-container--below select2-container--above').addClass('select2-container--' + newDirection);
	        }

	        this.$dropdownContainer.css(css);
	      };

	      AttachBody.prototype._resizeDropdown = function () {
	        var css = {
	          width: this.$container.outerWidth(false) + 'px'
	        };

	        if (this.options.get('dropdownAutoWidth')) {
	          css.minWidth = css.width;
	          css.position = 'relative';
	          css.width = 'auto';
	        }

	        this.$dropdown.css(css);
	      };

	      AttachBody.prototype._showDropdown = function (decorated) {
	        this.$dropdownContainer.appendTo(this.$dropdownParent);

	        this._positionDropdown();
	        this._resizeDropdown();
	      };

	      return AttachBody;
	    });

	    S2.define('select2/dropdown/minimumResultsForSearch', [], function () {
	      function countResults(data) {
	        var count = 0;

	        for (var d = 0; d < data.length; d++) {
	          var item = data[d];

	          if (item.children) {
	            count += countResults(item.children);
	          } else {
	            count++;
	          }
	        }

	        return count;
	      }

	      function MinimumResultsForSearch(decorated, $element, options, dataAdapter) {
	        this.minimumResultsForSearch = options.get('minimumResultsForSearch');

	        if (this.minimumResultsForSearch < 0) {
	          this.minimumResultsForSearch = Infinity;
	        }

	        decorated.call(this, $element, options, dataAdapter);
	      }

	      MinimumResultsForSearch.prototype.showSearch = function (decorated, params) {
	        if (countResults(params.data.results) < this.minimumResultsForSearch) {
	          return false;
	        }

	        return decorated.call(this, params);
	      };

	      return MinimumResultsForSearch;
	    });

	    S2.define('select2/dropdown/selectOnClose', [], function () {
	      function SelectOnClose() {}

	      SelectOnClose.prototype.bind = function (decorated, container, $container) {
	        var self = this;

	        decorated.call(this, container, $container);

	        container.on('close', function (params) {
	          self._handleSelectOnClose(params);
	        });
	      };

	      SelectOnClose.prototype._handleSelectOnClose = function (_, params) {
	        if (params && params.originalSelect2Event != null) {
	          var event = params.originalSelect2Event;

	          // Don't select an item if the close event was triggered from a select or
	          // unselect event
	          if (event._type === 'select' || event._type === 'unselect') {
	            return;
	          }
	        }

	        var $highlightedResults = this.getHighlightedResults();

	        // Only select highlighted results
	        if ($highlightedResults.length < 1) {
	          return;
	        }

	        var data = $highlightedResults.data('data');

	        // Don't re-select already selected resulte
	        if (data.element != null && data.element.selected || data.element == null && data.selected) {
	          return;
	        }

	        this.trigger('select', {
	          data: data
	        });
	      };

	      return SelectOnClose;
	    });

	    S2.define('select2/dropdown/closeOnSelect', [], function () {
	      function CloseOnSelect() {}

	      CloseOnSelect.prototype.bind = function (decorated, container, $container) {
	        var self = this;

	        decorated.call(this, container, $container);

	        container.on('select', function (evt) {
	          self._selectTriggered(evt);
	        });

	        container.on('unselect', function (evt) {
	          self._selectTriggered(evt);
	        });
	      };

	      CloseOnSelect.prototype._selectTriggered = function (_, evt) {
	        var originalEvent = evt.originalEvent;

	        // Don't close if the control key is being held
	        if (originalEvent && originalEvent.ctrlKey) {
	          return;
	        }

	        this.trigger('close', {
	          originalEvent: originalEvent,
	          originalSelect2Event: evt
	        });
	      };

	      return CloseOnSelect;
	    });

	    S2.define('select2/i18n/en', [], function () {
	      // English
	      return {
	        errorLoading: function errorLoading() {
	          return 'The results could not be loaded.';
	        },
	        inputTooLong: function inputTooLong(args) {
	          var overChars = args.input.length - args.maximum;

	          var message = 'Please delete ' + overChars + ' character';

	          if (overChars != 1) {
	            message += 's';
	          }

	          return message;
	        },
	        inputTooShort: function inputTooShort(args) {
	          var remainingChars = args.minimum - args.input.length;

	          var message = 'Please enter ' + remainingChars + ' or more characters';

	          return message;
	        },
	        loadingMore: function loadingMore() {
	          return 'Loading more results…';
	        },
	        maximumSelected: function maximumSelected(args) {
	          var message = 'You can only select ' + args.maximum + ' item';

	          if (args.maximum != 1) {
	            message += 's';
	          }

	          return message;
	        },
	        noResults: function noResults() {
	          return 'No results found';
	        },
	        searching: function searching() {
	          return 'Searching…';
	        }
	      };
	    });

	    S2.define('select2/defaults', ['jquery', 'require', './results', './selection/single', './selection/multiple', './selection/placeholder', './selection/allowClear', './selection/search', './selection/eventRelay', './utils', './translation', './diacritics', './data/select', './data/array', './data/ajax', './data/tags', './data/tokenizer', './data/minimumInputLength', './data/maximumInputLength', './data/maximumSelectionLength', './dropdown', './dropdown/search', './dropdown/hidePlaceholder', './dropdown/infiniteScroll', './dropdown/attachBody', './dropdown/minimumResultsForSearch', './dropdown/selectOnClose', './dropdown/closeOnSelect', './i18n/en'], function ($, require, ResultsList, SingleSelection, MultipleSelection, Placeholder, AllowClear, SelectionSearch, EventRelay, Utils, Translation, DIACRITICS, SelectData, ArrayData, AjaxData, Tags, Tokenizer, MinimumInputLength, MaximumInputLength, MaximumSelectionLength, Dropdown, DropdownSearch, HidePlaceholder, InfiniteScroll, AttachBody, MinimumResultsForSearch, SelectOnClose, CloseOnSelect, EnglishTranslation) {
	      function Defaults() {
	        this.reset();
	      }

	      Defaults.prototype.apply = function (options) {
	        options = $.extend(true, {}, this.defaults, options);

	        if (options.dataAdapter == null) {
	          if (options.ajax != null) {
	            options.dataAdapter = AjaxData;
	          } else if (options.data != null) {
	            options.dataAdapter = ArrayData;
	          } else {
	            options.dataAdapter = SelectData;
	          }

	          if (options.minimumInputLength > 0) {
	            options.dataAdapter = Utils.Decorate(options.dataAdapter, MinimumInputLength);
	          }

	          if (options.maximumInputLength > 0) {
	            options.dataAdapter = Utils.Decorate(options.dataAdapter, MaximumInputLength);
	          }

	          if (options.maximumSelectionLength > 0) {
	            options.dataAdapter = Utils.Decorate(options.dataAdapter, MaximumSelectionLength);
	          }

	          if (options.tags) {
	            options.dataAdapter = Utils.Decorate(options.dataAdapter, Tags);
	          }

	          if (options.tokenSeparators != null || options.tokenizer != null) {
	            options.dataAdapter = Utils.Decorate(options.dataAdapter, Tokenizer);
	          }

	          if (options.query != null) {
	            var Query = require(options.amdBase + 'compat/query');

	            options.dataAdapter = Utils.Decorate(options.dataAdapter, Query);
	          }

	          if (options.initSelection != null) {
	            var InitSelection = require(options.amdBase + 'compat/initSelection');

	            options.dataAdapter = Utils.Decorate(options.dataAdapter, InitSelection);
	          }
	        }

	        if (options.resultsAdapter == null) {
	          options.resultsAdapter = ResultsList;

	          if (options.ajax != null) {
	            options.resultsAdapter = Utils.Decorate(options.resultsAdapter, InfiniteScroll);
	          }

	          if (options.placeholder != null) {
	            options.resultsAdapter = Utils.Decorate(options.resultsAdapter, HidePlaceholder);
	          }

	          if (options.selectOnClose) {
	            options.resultsAdapter = Utils.Decorate(options.resultsAdapter, SelectOnClose);
	          }
	        }

	        if (options.dropdownAdapter == null) {
	          if (options.multiple) {
	            options.dropdownAdapter = Dropdown;
	          } else {
	            var SearchableDropdown = Utils.Decorate(Dropdown, DropdownSearch);

	            options.dropdownAdapter = SearchableDropdown;
	          }

	          if (options.minimumResultsForSearch !== 0) {
	            options.dropdownAdapter = Utils.Decorate(options.dropdownAdapter, MinimumResultsForSearch);
	          }

	          if (options.closeOnSelect) {
	            options.dropdownAdapter = Utils.Decorate(options.dropdownAdapter, CloseOnSelect);
	          }

	          if (options.dropdownCssClass != null || options.dropdownCss != null || options.adaptDropdownCssClass != null) {
	            var DropdownCSS = require(options.amdBase + 'compat/dropdownCss');

	            options.dropdownAdapter = Utils.Decorate(options.dropdownAdapter, DropdownCSS);
	          }

	          options.dropdownAdapter = Utils.Decorate(options.dropdownAdapter, AttachBody);
	        }

	        if (options.selectionAdapter == null) {
	          if (options.multiple) {
	            options.selectionAdapter = MultipleSelection;
	          } else {
	            options.selectionAdapter = SingleSelection;
	          }

	          // Add the placeholder mixin if a placeholder was specified
	          if (options.placeholder != null) {
	            options.selectionAdapter = Utils.Decorate(options.selectionAdapter, Placeholder);
	          }

	          if (options.allowClear) {
	            options.selectionAdapter = Utils.Decorate(options.selectionAdapter, AllowClear);
	          }

	          if (options.multiple) {
	            options.selectionAdapter = Utils.Decorate(options.selectionAdapter, SelectionSearch);
	          }

	          if (options.containerCssClass != null || options.containerCss != null || options.adaptContainerCssClass != null) {
	            var ContainerCSS = require(options.amdBase + 'compat/containerCss');

	            options.selectionAdapter = Utils.Decorate(options.selectionAdapter, ContainerCSS);
	          }

	          options.selectionAdapter = Utils.Decorate(options.selectionAdapter, EventRelay);
	        }

	        if (typeof options.language === 'string') {
	          // Check if the language is specified with a region
	          if (options.language.indexOf('-') > 0) {
	            // Extract the region information if it is included
	            var languageParts = options.language.split('-');
	            var baseLanguage = languageParts[0];

	            options.language = [options.language, baseLanguage];
	          } else {
	            options.language = [options.language];
	          }
	        }

	        if ($.isArray(options.language)) {
	          var languages = new Translation();
	          options.language.push('en');

	          var languageNames = options.language;

	          for (var l = 0; l < languageNames.length; l++) {
	            var name = languageNames[l];
	            var language = {};

	            try {
	              // Try to load it with the original name
	              language = Translation.loadPath(name);
	            } catch (e) {
	              try {
	                // If we couldn't load it, check if it wasn't the full path
	                name = this.defaults.amdLanguageBase + name;
	                language = Translation.loadPath(name);
	              } catch (ex) {
	                // The translation could not be loaded at all. Sometimes this is
	                // because of a configuration problem, other times this can be
	                // because of how Select2 helps load all possible translation files.
	                if (options.debug && window.console && console.warn) {
	                  console.warn('Select2: The language file for "' + name + '" could not be ' + 'automatically loaded. A fallback will be used instead.');
	                }

	                continue;
	              }
	            }

	            languages.extend(language);
	          }

	          options.translations = languages;
	        } else {
	          var baseTranslation = Translation.loadPath(this.defaults.amdLanguageBase + 'en');
	          var customTranslation = new Translation(options.language);

	          customTranslation.extend(baseTranslation);

	          options.translations = customTranslation;
	        }

	        return options;
	      };

	      Defaults.prototype.reset = function () {
	        function stripDiacritics(text) {
	          // Used 'uni range + named function' from http://jsperf.com/diacritics/18
	          function match(a) {
	            return DIACRITICS[a] || a;
	          }

	          return text.replace(/[^\u0000-\u007E]/g, match);
	        }

	        function matcher(params, data) {
	          // Always return the object if there is nothing to compare
	          if ($.trim(params.term) === '') {
	            return data;
	          }

	          // Do a recursive check for options with children
	          if (data.children && data.children.length > 0) {
	            // Clone the data object if there are children
	            // This is required as we modify the object to remove any non-matches
	            var match = $.extend(true, {}, data);

	            // Check each child of the option
	            for (var c = data.children.length - 1; c >= 0; c--) {
	              var child = data.children[c];

	              var matches = matcher(params, child);

	              // If there wasn't a match, remove the object in the array
	              if (matches == null) {
	                match.children.splice(c, 1);
	              }
	            }

	            // If any children matched, return the new object
	            if (match.children.length > 0) {
	              return match;
	            }

	            // If there were no matching children, check just the plain object
	            return matcher(params, match);
	          }

	          var original = stripDiacritics(data.text).toUpperCase();
	          var term = stripDiacritics(params.term).toUpperCase();

	          // Check if the text contains the term
	          if (original.indexOf(term) > -1) {
	            return data;
	          }

	          // If it doesn't contain the term, don't return anything
	          return null;
	        }

	        this.defaults = {
	          amdBase: './',
	          amdLanguageBase: './i18n/',
	          closeOnSelect: true,
	          debug: false,
	          dropdownAutoWidth: false,
	          escapeMarkup: Utils.escapeMarkup,
	          language: EnglishTranslation,
	          matcher: matcher,
	          minimumInputLength: 0,
	          maximumInputLength: 0,
	          maximumSelectionLength: 0,
	          minimumResultsForSearch: 0,
	          selectOnClose: false,
	          sorter: function sorter(data) {
	            return data;
	          },
	          templateResult: function templateResult(result) {
	            return result.text;
	          },
	          templateSelection: function templateSelection(selection) {
	            return selection.text;
	          },
	          theme: 'default',
	          width: 'resolve'
	        };
	      };

	      Defaults.prototype.set = function (key, value) {
	        var camelKey = $.camelCase(key);

	        var data = {};
	        data[camelKey] = value;

	        var convertedData = Utils._convertData(data);

	        $.extend(this.defaults, convertedData);
	      };

	      var defaults = new Defaults();

	      return defaults;
	    });

	    S2.define('select2/options', ['require', 'jquery', './defaults', './utils'], function (require, $, Defaults, Utils) {
	      function Options(options, $element) {
	        this.options = options;

	        if ($element != null) {
	          this.fromElement($element);
	        }

	        this.options = Defaults.apply(this.options);

	        if ($element && $element.is('input')) {
	          var InputCompat = require(this.get('amdBase') + 'compat/inputData');

	          this.options.dataAdapter = Utils.Decorate(this.options.dataAdapter, InputCompat);
	        }
	      }

	      Options.prototype.fromElement = function ($e) {
	        var excludedData = ['select2'];

	        if (this.options.multiple == null) {
	          this.options.multiple = $e.prop('multiple');
	        }

	        if (this.options.disabled == null) {
	          this.options.disabled = $e.prop('disabled');
	        }

	        if (this.options.language == null) {
	          if ($e.prop('lang')) {
	            this.options.language = $e.prop('lang').toLowerCase();
	          } else if ($e.closest('[lang]').prop('lang')) {
	            this.options.language = $e.closest('[lang]').prop('lang');
	          }
	        }

	        if (this.options.dir == null) {
	          if ($e.prop('dir')) {
	            this.options.dir = $e.prop('dir');
	          } else if ($e.closest('[dir]').prop('dir')) {
	            this.options.dir = $e.closest('[dir]').prop('dir');
	          } else {
	            this.options.dir = 'ltr';
	          }
	        }

	        $e.prop('disabled', this.options.disabled);
	        $e.prop('multiple', this.options.multiple);

	        if ($e.data('select2Tags')) {
	          if (this.options.debug && window.console && console.warn) {
	            console.warn('Select2: The `data-select2-tags` attribute has been changed to ' + 'use the `data-data` and `data-tags="true"` attributes and will be ' + 'removed in future versions of Select2.');
	          }

	          $e.data('data', $e.data('select2Tags'));
	          $e.data('tags', true);
	        }

	        if ($e.data('ajaxUrl')) {
	          if (this.options.debug && window.console && console.warn) {
	            console.warn('Select2: The `data-ajax-url` attribute has been changed to ' + '`data-ajax--url` and support for the old attribute will be removed' + ' in future versions of Select2.');
	          }

	          $e.attr('ajax--url', $e.data('ajaxUrl'));
	          $e.data('ajax--url', $e.data('ajaxUrl'));
	        }

	        var dataset = {};

	        // Prefer the element's `dataset` attribute if it exists
	        // jQuery 1.x does not correctly handle data attributes with multiple dashes
	        if ($.fn.jquery && $.fn.jquery.substr(0, 2) == '1.' && $e[0].dataset) {
	          dataset = $.extend(true, {}, $e[0].dataset, $e.data());
	        } else {
	          dataset = $e.data();
	        }

	        var data = $.extend(true, {}, dataset);

	        data = Utils._convertData(data);

	        for (var key in data) {
	          if ($.inArray(key, excludedData) > -1) {
	            continue;
	          }

	          if ($.isPlainObject(this.options[key])) {
	            $.extend(this.options[key], data[key]);
	          } else {
	            this.options[key] = data[key];
	          }
	        }

	        return this;
	      };

	      Options.prototype.get = function (key) {
	        return this.options[key];
	      };

	      Options.prototype.set = function (key, val) {
	        this.options[key] = val;
	      };

	      return Options;
	    });

	    S2.define('select2/core', ['jquery', './options', './utils', './keys'], function ($, Options, Utils, KEYS) {
	      var Select2 = function Select2($element, options) {
	        if ($element.data('select2') != null) {
	          $element.data('select2').destroy();
	        }

	        this.$element = $element;

	        this.id = this._generateId($element);

	        options = options || {};

	        this.options = new Options(options, $element);

	        Select2.__super__.constructor.call(this);

	        // Set up the tabindex

	        var tabindex = $element.attr('tabindex') || 0;
	        $element.data('old-tabindex', tabindex);
	        $element.attr('tabindex', '-1');

	        // Set up containers and adapters

	        var DataAdapter = this.options.get('dataAdapter');
	        this.dataAdapter = new DataAdapter($element, this.options);

	        var $container = this.render();

	        this._placeContainer($container);

	        var SelectionAdapter = this.options.get('selectionAdapter');
	        this.selection = new SelectionAdapter($element, this.options);
	        this.$selection = this.selection.render();

	        this.selection.position(this.$selection, $container);

	        var DropdownAdapter = this.options.get('dropdownAdapter');
	        this.dropdown = new DropdownAdapter($element, this.options);
	        this.$dropdown = this.dropdown.render();

	        this.dropdown.position(this.$dropdown, $container);

	        var ResultsAdapter = this.options.get('resultsAdapter');
	        this.results = new ResultsAdapter($element, this.options, this.dataAdapter);
	        this.$results = this.results.render();

	        this.results.position(this.$results, this.$dropdown);

	        // Bind events

	        var self = this;

	        // Bind the container to all of the adapters
	        this._bindAdapters();

	        // Register any DOM event handlers
	        this._registerDomEvents();

	        // Register any internal event handlers
	        this._registerDataEvents();
	        this._registerSelectionEvents();
	        this._registerDropdownEvents();
	        this._registerResultsEvents();
	        this._registerEvents();

	        // Set the initial state
	        this.dataAdapter.current(function (initialData) {
	          self.trigger('selection:update', {
	            data: initialData
	          });
	        });

	        // Hide the original select
	        $element.addClass('select2-hidden-accessible');
	        $element.attr('aria-hidden', 'true');

	        // Synchronize any monitored attributes
	        this._syncAttributes();

	        $element.data('select2', this);
	      };

	      Utils.Extend(Select2, Utils.Observable);

	      Select2.prototype._generateId = function ($element) {
	        var id = '';

	        if ($element.attr('id') != null) {
	          id = $element.attr('id');
	        } else if ($element.attr('name') != null) {
	          id = $element.attr('name') + '-' + Utils.generateChars(2);
	        } else {
	          id = Utils.generateChars(4);
	        }

	        id = id.replace(/(:|\.|\[|\]|,)/g, '');
	        id = 'select2-' + id;

	        return id;
	      };

	      Select2.prototype._placeContainer = function ($container) {
	        $container.insertAfter(this.$element);

	        var width = this._resolveWidth(this.$element, this.options.get('width'));

	        if (width != null) {
	          $container.css('width', width);
	        }
	      };

	      Select2.prototype._resolveWidth = function ($element, method) {
	        var WIDTH = /^width:(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc))/i;

	        if (method == 'resolve') {
	          var styleWidth = this._resolveWidth($element, 'style');

	          if (styleWidth != null) {
	            return styleWidth;
	          }

	          return this._resolveWidth($element, 'element');
	        }

	        if (method == 'element') {
	          var elementWidth = $element.outerWidth(false);

	          if (elementWidth <= 0) {
	            return 'auto';
	          }

	          return elementWidth + 'px';
	        }

	        if (method == 'style') {
	          var style = $element.attr('style');

	          if (typeof style !== 'string') {
	            return null;
	          }

	          var attrs = style.split(';');

	          for (var i = 0, l = attrs.length; i < l; i = i + 1) {
	            var attr = attrs[i].replace(/\s/g, '');
	            var matches = attr.match(WIDTH);

	            if (matches !== null && matches.length >= 1) {
	              return matches[1];
	            }
	          }

	          return null;
	        }

	        return method;
	      };

	      Select2.prototype._bindAdapters = function () {
	        this.dataAdapter.bind(this, this.$container);
	        this.selection.bind(this, this.$container);

	        this.dropdown.bind(this, this.$container);
	        this.results.bind(this, this.$container);
	      };

	      Select2.prototype._registerDomEvents = function () {
	        var self = this;

	        this.$element.on('change.select2', function () {
	          self.dataAdapter.current(function (data) {
	            self.trigger('selection:update', {
	              data: data
	            });
	          });
	        });

	        this.$element.on('focus.select2', function (evt) {
	          self.trigger('focus', evt);
	        });

	        this._syncA = Utils.bind(this._syncAttributes, this);
	        this._syncS = Utils.bind(this._syncSubtree, this);

	        if (this.$element[0].attachEvent) {
	          this.$element[0].attachEvent('onpropertychange', this._syncA);
	        }

	        var observer = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;

	        if (observer != null) {
	          this._observer = new observer(function (mutations) {
	            $.each(mutations, self._syncA);
	            $.each(mutations, self._syncS);
	          });
	          this._observer.observe(this.$element[0], {
	            attributes: true,
	            childList: true,
	            subtree: false
	          });
	        } else if (this.$element[0].addEventListener) {
	          this.$element[0].addEventListener('DOMAttrModified', self._syncA, false);
	          this.$element[0].addEventListener('DOMNodeInserted', self._syncS, false);
	          this.$element[0].addEventListener('DOMNodeRemoved', self._syncS, false);
	        }
	      };

	      Select2.prototype._registerDataEvents = function () {
	        var self = this;

	        this.dataAdapter.on('*', function (name, params) {
	          self.trigger(name, params);
	        });
	      };

	      Select2.prototype._registerSelectionEvents = function () {
	        var self = this;
	        var nonRelayEvents = ['toggle', 'focus'];

	        this.selection.on('toggle', function () {
	          self.toggleDropdown();
	        });

	        this.selection.on('focus', function (params) {
	          self.focus(params);
	        });

	        this.selection.on('*', function (name, params) {
	          if ($.inArray(name, nonRelayEvents) !== -1) {
	            return;
	          }

	          self.trigger(name, params);
	        });
	      };

	      Select2.prototype._registerDropdownEvents = function () {
	        var self = this;

	        this.dropdown.on('*', function (name, params) {
	          self.trigger(name, params);
	        });
	      };

	      Select2.prototype._registerResultsEvents = function () {
	        var self = this;

	        this.results.on('*', function (name, params) {
	          self.trigger(name, params);
	        });
	      };

	      Select2.prototype._registerEvents = function () {
	        var self = this;

	        this.on('open', function () {
	          self.$container.addClass('select2-container--open');
	        });

	        this.on('close', function () {
	          self.$container.removeClass('select2-container--open');
	        });

	        this.on('enable', function () {
	          self.$container.removeClass('select2-container--disabled');
	        });

	        this.on('disable', function () {
	          self.$container.addClass('select2-container--disabled');
	        });

	        this.on('blur', function () {
	          self.$container.removeClass('select2-container--focus');
	        });

	        this.on('query', function (params) {
	          if (!self.isOpen()) {
	            self.trigger('open', {});
	          }

	          this.dataAdapter.query(params, function (data) {
	            self.trigger('results:all', {
	              data: data,
	              query: params
	            });
	          });
	        });

	        this.on('query:append', function (params) {
	          this.dataAdapter.query(params, function (data) {
	            self.trigger('results:append', {
	              data: data,
	              query: params
	            });
	          });
	        });

	        this.on('keypress', function (evt) {
	          var key = evt.which;

	          if (self.isOpen()) {
	            if (key === KEYS.ESC || key === KEYS.TAB || key === KEYS.UP && evt.altKey) {
	              self.close();

	              evt.preventDefault();
	            } else if (key === KEYS.ENTER) {
	              self.trigger('results:select', {});

	              evt.preventDefault();
	            } else if (key === KEYS.SPACE && evt.ctrlKey) {
	              self.trigger('results:toggle', {});

	              evt.preventDefault();
	            } else if (key === KEYS.UP) {
	              self.trigger('results:previous', {});

	              evt.preventDefault();
	            } else if (key === KEYS.DOWN) {
	              self.trigger('results:next', {});

	              evt.preventDefault();
	            }
	          } else {
	            if (key === KEYS.ENTER || key === KEYS.SPACE || key === KEYS.DOWN && evt.altKey) {
	              self.open();

	              evt.preventDefault();
	            }
	          }
	        });
	      };

	      Select2.prototype._syncAttributes = function () {
	        this.options.set('disabled', this.$element.prop('disabled'));

	        if (this.options.get('disabled')) {
	          if (this.isOpen()) {
	            this.close();
	          }

	          this.trigger('disable', {});
	        } else {
	          this.trigger('enable', {});
	        }
	      };

	      Select2.prototype._syncSubtree = function (evt, mutations) {
	        var changed = false;
	        var self = this;

	        // Ignore any mutation events raised for elements that aren't options or
	        // optgroups. This handles the case when the select element is destroyed
	        if (evt && evt.target && evt.target.nodeName !== 'OPTION' && evt.target.nodeName !== 'OPTGROUP') {
	          return;
	        }

	        if (!mutations) {
	          // If mutation events aren't supported, then we can only assume that the
	          // change affected the selections
	          changed = true;
	        } else if (mutations.addedNodes && mutations.addedNodes.length > 0) {
	          for (var n = 0; n < mutations.addedNodes.length; n++) {
	            var node = mutations.addedNodes[n];

	            if (node.selected) {
	              changed = true;
	            }
	          }
	        } else if (mutations.removedNodes && mutations.removedNodes.length > 0) {
	          changed = true;
	        }

	        // Only re-pull the data if we think there is a change
	        if (changed) {
	          this.dataAdapter.current(function (currentData) {
	            self.trigger('selection:update', {
	              data: currentData
	            });
	          });
	        }
	      };

	      /**
	       * Override the trigger method to automatically trigger pre-events when
	       * there are events that can be prevented.
	       */
	      Select2.prototype.trigger = function (name, args) {
	        var actualTrigger = Select2.__super__.trigger;
	        var preTriggerMap = {
	          'open': 'opening',
	          'close': 'closing',
	          'select': 'selecting',
	          'unselect': 'unselecting'
	        };

	        if (args === undefined) {
	          args = {};
	        }

	        if (name in preTriggerMap) {
	          var preTriggerName = preTriggerMap[name];
	          var preTriggerArgs = {
	            prevented: false,
	            name: name,
	            args: args
	          };

	          actualTrigger.call(this, preTriggerName, preTriggerArgs);

	          if (preTriggerArgs.prevented) {
	            args.prevented = true;

	            return;
	          }
	        }

	        actualTrigger.call(this, name, args);
	      };

	      Select2.prototype.toggleDropdown = function () {
	        if (this.options.get('disabled')) {
	          return;
	        }

	        if (this.isOpen()) {
	          this.close();
	        } else {
	          this.open();
	        }
	      };

	      Select2.prototype.open = function () {
	        if (this.isOpen()) {
	          return;
	        }

	        this.trigger('query', {});
	      };

	      Select2.prototype.close = function () {
	        if (!this.isOpen()) {
	          return;
	        }

	        this.trigger('close', {});
	      };

	      Select2.prototype.isOpen = function () {
	        return this.$container.hasClass('select2-container--open');
	      };

	      Select2.prototype.hasFocus = function () {
	        return this.$container.hasClass('select2-container--focus');
	      };

	      Select2.prototype.focus = function (data) {
	        // No need to re-trigger focus events if we are already focused
	        if (this.hasFocus()) {
	          return;
	        }

	        this.$container.addClass('select2-container--focus');
	        this.trigger('focus', {});
	      };

	      Select2.prototype.enable = function (args) {
	        if (this.options.get('debug') && window.console && console.warn) {
	          console.warn('Select2: The `select2("enable")` method has been deprecated and will' + ' be removed in later Select2 versions. Use $element.prop("disabled")' + ' instead.');
	        }

	        if (args == null || args.length === 0) {
	          args = [true];
	        }

	        var disabled = !args[0];

	        this.$element.prop('disabled', disabled);
	      };

	      Select2.prototype.data = function () {
	        if (this.options.get('debug') && arguments.length > 0 && window.console && console.warn) {
	          console.warn('Select2: Data can no longer be set using `select2("data")`. You ' + 'should consider setting the value instead using `$element.val()`.');
	        }

	        var data = [];

	        this.dataAdapter.current(function (currentData) {
	          data = currentData;
	        });

	        return data;
	      };

	      Select2.prototype.val = function (args) {
	        if (this.options.get('debug') && window.console && console.warn) {
	          console.warn('Select2: The `select2("val")` method has been deprecated and will be' + ' removed in later Select2 versions. Use $element.val() instead.');
	        }

	        if (args == null || args.length === 0) {
	          return this.$element.val();
	        }

	        var newVal = args[0];

	        if ($.isArray(newVal)) {
	          newVal = $.map(newVal, function (obj) {
	            return obj.toString();
	          });
	        }

	        this.$element.val(newVal).trigger('change');
	      };

	      Select2.prototype.destroy = function () {
	        this.$container.remove();

	        if (this.$element[0].detachEvent) {
	          this.$element[0].detachEvent('onpropertychange', this._syncA);
	        }

	        if (this._observer != null) {
	          this._observer.disconnect();
	          this._observer = null;
	        } else if (this.$element[0].removeEventListener) {
	          this.$element[0].removeEventListener('DOMAttrModified', this._syncA, false);
	          this.$element[0].removeEventListener('DOMNodeInserted', this._syncS, false);
	          this.$element[0].removeEventListener('DOMNodeRemoved', this._syncS, false);
	        }

	        this._syncA = null;
	        this._syncS = null;

	        this.$element.off('.select2');
	        this.$element.attr('tabindex', this.$element.data('old-tabindex'));

	        this.$element.removeClass('select2-hidden-accessible');
	        this.$element.attr('aria-hidden', 'false');
	        this.$element.removeData('select2');

	        this.dataAdapter.destroy();
	        this.selection.destroy();
	        this.dropdown.destroy();
	        this.results.destroy();

	        this.dataAdapter = null;
	        this.selection = null;
	        this.dropdown = null;
	        this.results = null;
	      };

	      Select2.prototype.render = function () {
	        var $container = $('<span class="select2 select2-container">' + '<span class="selection"></span>' + '<span class="dropdown-wrapper" aria-hidden="true"></span>' + '</span>');

	        $container.attr('dir', this.options.get('dir'));

	        this.$container = $container;

	        this.$container.addClass('select2-container--' + this.options.get('theme'));

	        $container.data('element', this.$element);

	        return $container;
	      };

	      return Select2;
	    });

	    S2.define('select2/compat/utils', ['jquery'], function ($) {
	      function syncCssClasses($dest, $src, adapter) {
	        var classes,
	            replacements = [],
	            adapted;

	        classes = $.trim($dest.attr('class'));

	        if (classes) {
	          classes = '' + classes; // for IE which returns object

	          $(classes.split(/\s+/)).each(function () {
	            // Save all Select2 classes
	            if (this.indexOf('select2-') === 0) {
	              replacements.push(this);
	            }
	          });
	        }

	        classes = $.trim($src.attr('class'));

	        if (classes) {
	          classes = '' + classes; // for IE which returns object

	          $(classes.split(/\s+/)).each(function () {
	            // Only adapt non-Select2 classes
	            if (this.indexOf('select2-') !== 0) {
	              adapted = adapter(this);

	              if (adapted != null) {
	                replacements.push(adapted);
	              }
	            }
	          });
	        }

	        $dest.attr('class', replacements.join(' '));
	      }

	      return {
	        syncCssClasses: syncCssClasses
	      };
	    });

	    S2.define('select2/compat/containerCss', ['jquery', './utils'], function ($, CompatUtils) {
	      // No-op CSS adapter that discards all classes by default
	      function _containerAdapter(clazz) {
	        return null;
	      }

	      function ContainerCSS() {}

	      ContainerCSS.prototype.render = function (decorated) {
	        var $container = decorated.call(this);

	        var containerCssClass = this.options.get('containerCssClass') || '';

	        if ($.isFunction(containerCssClass)) {
	          containerCssClass = containerCssClass(this.$element);
	        }

	        var containerCssAdapter = this.options.get('adaptContainerCssClass');
	        containerCssAdapter = containerCssAdapter || _containerAdapter;

	        if (containerCssClass.indexOf(':all:') !== -1) {
	          containerCssClass = containerCssClass.replace(':all:', '');

	          var _cssAdapter = containerCssAdapter;

	          containerCssAdapter = function containerCssAdapter(clazz) {
	            var adapted = _cssAdapter(clazz);

	            if (adapted != null) {
	              // Append the old one along with the adapted one
	              return adapted + ' ' + clazz;
	            }

	            return clazz;
	          };
	        }

	        var containerCss = this.options.get('containerCss') || {};

	        if ($.isFunction(containerCss)) {
	          containerCss = containerCss(this.$element);
	        }

	        CompatUtils.syncCssClasses($container, this.$element, containerCssAdapter);

	        $container.css(containerCss);
	        $container.addClass(containerCssClass);

	        return $container;
	      };

	      return ContainerCSS;
	    });

	    S2.define('select2/compat/dropdownCss', ['jquery', './utils'], function ($, CompatUtils) {
	      // No-op CSS adapter that discards all classes by default
	      function _dropdownAdapter(clazz) {
	        return null;
	      }

	      function DropdownCSS() {}

	      DropdownCSS.prototype.render = function (decorated) {
	        var $dropdown = decorated.call(this);

	        var dropdownCssClass = this.options.get('dropdownCssClass') || '';

	        if ($.isFunction(dropdownCssClass)) {
	          dropdownCssClass = dropdownCssClass(this.$element);
	        }

	        var dropdownCssAdapter = this.options.get('adaptDropdownCssClass');
	        dropdownCssAdapter = dropdownCssAdapter || _dropdownAdapter;

	        if (dropdownCssClass.indexOf(':all:') !== -1) {
	          dropdownCssClass = dropdownCssClass.replace(':all:', '');

	          var _cssAdapter = dropdownCssAdapter;

	          dropdownCssAdapter = function dropdownCssAdapter(clazz) {
	            var adapted = _cssAdapter(clazz);

	            if (adapted != null) {
	              // Append the old one along with the adapted one
	              return adapted + ' ' + clazz;
	            }

	            return clazz;
	          };
	        }

	        var dropdownCss = this.options.get('dropdownCss') || {};

	        if ($.isFunction(dropdownCss)) {
	          dropdownCss = dropdownCss(this.$element);
	        }

	        CompatUtils.syncCssClasses($dropdown, this.$element, dropdownCssAdapter);

	        $dropdown.css(dropdownCss);
	        $dropdown.addClass(dropdownCssClass);

	        return $dropdown;
	      };

	      return DropdownCSS;
	    });

	    S2.define('select2/compat/initSelection', ['jquery'], function ($) {
	      function InitSelection(decorated, $element, options) {
	        if (options.get('debug') && window.console && console.warn) {
	          console.warn('Select2: The `initSelection` option has been deprecated in favor' + ' of a custom data adapter that overrides the `current` method. ' + 'This method is now called multiple times instead of a single ' + 'time when the instance is initialized. Support will be removed ' + 'for the `initSelection` option in future versions of Select2');
	        }

	        this.initSelection = options.get('initSelection');
	        this._isInitialized = false;

	        decorated.call(this, $element, options);
	      }

	      InitSelection.prototype.current = function (decorated, callback) {
	        var self = this;

	        if (this._isInitialized) {
	          decorated.call(this, callback);

	          return;
	        }

	        this.initSelection.call(null, this.$element, function (data) {
	          self._isInitialized = true;

	          if (!$.isArray(data)) {
	            data = [data];
	          }

	          callback(data);
	        });
	      };

	      return InitSelection;
	    });

	    S2.define('select2/compat/inputData', ['jquery'], function ($) {
	      function InputData(decorated, $element, options) {
	        this._currentData = [];
	        this._valueSeparator = options.get('valueSeparator') || ',';

	        if ($element.prop('type') === 'hidden') {
	          if (options.get('debug') && console && console.warn) {
	            console.warn('Select2: Using a hidden input with Select2 is no longer ' + 'supported and may stop working in the future. It is recommended ' + 'to use a `<select>` element instead.');
	          }
	        }

	        decorated.call(this, $element, options);
	      }

	      InputData.prototype.current = function (_, callback) {
	        function getSelected(data, selectedIds) {
	          var selected = [];

	          if (data.selected || $.inArray(data.id, selectedIds) !== -1) {
	            data.selected = true;
	            selected.push(data);
	          } else {
	            data.selected = false;
	          }

	          if (data.children) {
	            selected.push.apply(selected, getSelected(data.children, selectedIds));
	          }

	          return selected;
	        }

	        var selected = [];

	        for (var d = 0; d < this._currentData.length; d++) {
	          var data = this._currentData[d];

	          selected.push.apply(selected, getSelected(data, this.$element.val().split(this._valueSeparator)));
	        }

	        callback(selected);
	      };

	      InputData.prototype.select = function (_, data) {
	        if (!this.options.get('multiple')) {
	          this.current(function (allData) {
	            $.map(allData, function (data) {
	              data.selected = false;
	            });
	          });

	          this.$element.val(data.id);
	          this.$element.trigger('change');
	        } else {
	          var value = this.$element.val();
	          value += this._valueSeparator + data.id;

	          this.$element.val(value);
	          this.$element.trigger('change');
	        }
	      };

	      InputData.prototype.unselect = function (_, data) {
	        var self = this;

	        data.selected = false;

	        this.current(function (allData) {
	          var values = [];

	          for (var d = 0; d < allData.length; d++) {
	            var item = allData[d];

	            if (data.id == item.id) {
	              continue;
	            }

	            values.push(item.id);
	          }

	          self.$element.val(values.join(self._valueSeparator));
	          self.$element.trigger('change');
	        });
	      };

	      InputData.prototype.query = function (_, params, callback) {
	        var results = [];

	        for (var d = 0; d < this._currentData.length; d++) {
	          var data = this._currentData[d];

	          var matches = this.matches(params, data);

	          if (matches !== null) {
	            results.push(matches);
	          }
	        }

	        callback({
	          results: results
	        });
	      };

	      InputData.prototype.addOptions = function (_, $options) {
	        var options = $.map($options, function ($option) {
	          return $.data($option[0], 'data');
	        });

	        this._currentData.push.apply(this._currentData, options);
	      };

	      return InputData;
	    });

	    S2.define('select2/compat/matcher', ['jquery'], function ($) {
	      function oldMatcher(matcher) {
	        function wrappedMatcher(params, data) {
	          var match = $.extend(true, {}, data);

	          if (params.term == null || $.trim(params.term) === '') {
	            return match;
	          }

	          if (data.children) {
	            for (var c = data.children.length - 1; c >= 0; c--) {
	              var child = data.children[c];

	              // Check if the child object matches
	              // The old matcher returned a boolean true or false
	              var doesMatch = matcher(params.term, child.text, child);

	              // If the child didn't match, pop it off
	              if (!doesMatch) {
	                match.children.splice(c, 1);
	              }
	            }

	            if (match.children.length > 0) {
	              return match;
	            }
	          }

	          if (matcher(params.term, data.text, data)) {
	            return match;
	          }

	          return null;
	        }

	        return wrappedMatcher;
	      }

	      return oldMatcher;
	    });

	    S2.define('select2/compat/query', [], function () {
	      function Query(decorated, $element, options) {
	        if (options.get('debug') && window.console && console.warn) {
	          console.warn('Select2: The `query` option has been deprecated in favor of a ' + 'custom data adapter that overrides the `query` method. Support ' + 'will be removed for the `query` option in future versions of ' + 'Select2.');
	        }

	        decorated.call(this, $element, options);
	      }

	      Query.prototype.query = function (_, params, callback) {
	        params.callback = callback;

	        var query = this.options.get('query');

	        query.call(null, params);
	      };

	      return Query;
	    });

	    S2.define('select2/dropdown/attachContainer', [], function () {
	      function AttachContainer(decorated, $element, options) {
	        decorated.call(this, $element, options);
	      }

	      AttachContainer.prototype.position = function (decorated, $dropdown, $container) {
	        var $dropdownContainer = $container.find('.dropdown-wrapper');
	        $dropdownContainer.append($dropdown);

	        $dropdown.addClass('select2-dropdown--below');
	        $container.addClass('select2-container--below');
	      };

	      return AttachContainer;
	    });

	    S2.define('select2/dropdown/stopPropagation', [], function () {
	      function StopPropagation() {}

	      StopPropagation.prototype.bind = function (decorated, container, $container) {
	        decorated.call(this, container, $container);

	        var stoppedEvents = ['blur', 'change', 'click', 'dblclick', 'focus', 'focusin', 'focusout', 'input', 'keydown', 'keyup', 'keypress', 'mousedown', 'mouseenter', 'mouseleave', 'mousemove', 'mouseover', 'mouseup', 'search', 'touchend', 'touchstart'];

	        this.$dropdown.on(stoppedEvents.join(' '), function (evt) {
	          evt.stopPropagation();
	        });
	      };

	      return StopPropagation;
	    });

	    S2.define('select2/selection/stopPropagation', [], function () {
	      function StopPropagation() {}

	      StopPropagation.prototype.bind = function (decorated, container, $container) {
	        decorated.call(this, container, $container);

	        var stoppedEvents = ['blur', 'change', 'click', 'dblclick', 'focus', 'focusin', 'focusout', 'input', 'keydown', 'keyup', 'keypress', 'mousedown', 'mouseenter', 'mouseleave', 'mousemove', 'mouseover', 'mouseup', 'search', 'touchend', 'touchstart'];

	        this.$selection.on(stoppedEvents.join(' '), function (evt) {
	          evt.stopPropagation();
	        });
	      };

	      return StopPropagation;
	    });

	    /*!
	     * jQuery Mousewheel 3.1.13
	     *
	     * Copyright jQuery Foundation and other contributors
	     * Released under the MIT license
	     * http://jquery.org/license
	     */

	    (function (factory) {
	      if (typeof S2.define === 'function' && S2.define.amd) {
	        // AMD. Register as an anonymous module.
	        S2.define('jquery-mousewheel', ['jquery'], factory);
	      } else if (( false ? 'undefined' : _typeof(exports)) === 'object') {
	        // Node/CommonJS style for Browserify
	        module.exports = factory;
	      } else {
	        // Browser globals
	        factory(jQuery);
	      }
	    })(function ($) {

	      var toFix = ['wheel', 'mousewheel', 'DOMMouseScroll', 'MozMousePixelScroll'],
	          toBind = 'onwheel' in document || document.documentMode >= 9 ? ['wheel'] : ['mousewheel', 'DomMouseScroll', 'MozMousePixelScroll'],
	          slice = Array.prototype.slice,
	          nullLowestDeltaTimeout,
	          lowestDelta;

	      if ($.event.fixHooks) {
	        for (var i = toFix.length; i;) {
	          $.event.fixHooks[toFix[--i]] = $.event.mouseHooks;
	        }
	      }

	      var special = $.event.special.mousewheel = {
	        version: '3.1.12',

	        setup: function setup() {
	          if (this.addEventListener) {
	            for (var i = toBind.length; i;) {
	              this.addEventListener(toBind[--i], handler, false);
	            }
	          } else {
	            this.onmousewheel = handler;
	          }
	          // Store the line height and page height for this particular element
	          $.data(this, 'mousewheel-line-height', special.getLineHeight(this));
	          $.data(this, 'mousewheel-page-height', special.getPageHeight(this));
	        },

	        teardown: function teardown() {
	          if (this.removeEventListener) {
	            for (var i = toBind.length; i;) {
	              this.removeEventListener(toBind[--i], handler, false);
	            }
	          } else {
	            this.onmousewheel = null;
	          }
	          // Clean up the data we added to the element
	          $.removeData(this, 'mousewheel-line-height');
	          $.removeData(this, 'mousewheel-page-height');
	        },

	        getLineHeight: function getLineHeight(elem) {
	          var $elem = $(elem),
	              $parent = $elem['offsetParent' in $.fn ? 'offsetParent' : 'parent']();
	          if (!$parent.length) {
	            $parent = $('body');
	          }
	          return parseInt($parent.css('fontSize'), 10) || parseInt($elem.css('fontSize'), 10) || 16;
	        },

	        getPageHeight: function getPageHeight(elem) {
	          return $(elem).height();
	        },

	        settings: {
	          adjustOldDeltas: true, // see shouldAdjustOldDeltas() below
	          normalizeOffset: true // calls getBoundingClientRect for each event
	        }
	      };

	      $.fn.extend({
	        mousewheel: function mousewheel(fn) {
	          return fn ? this.bind('mousewheel', fn) : this.trigger('mousewheel');
	        },

	        unmousewheel: function unmousewheel(fn) {
	          return this.unbind('mousewheel', fn);
	        }
	      });

	      function handler(event) {
	        var orgEvent = event || window.event,
	            args = slice.call(arguments, 1),
	            delta = 0,
	            deltaX = 0,
	            deltaY = 0,
	            absDelta = 0,
	            offsetX = 0,
	            offsetY = 0;
	        event = $.event.fix(orgEvent);
	        event.type = 'mousewheel';

	        // Old school scrollwheel delta
	        if ('detail' in orgEvent) {
	          deltaY = orgEvent.detail * -1;
	        }
	        if ('wheelDelta' in orgEvent) {
	          deltaY = orgEvent.wheelDelta;
	        }
	        if ('wheelDeltaY' in orgEvent) {
	          deltaY = orgEvent.wheelDeltaY;
	        }
	        if ('wheelDeltaX' in orgEvent) {
	          deltaX = orgEvent.wheelDeltaX * -1;
	        }

	        // Firefox < 17 horizontal scrolling related to DOMMouseScroll event
	        if ('axis' in orgEvent && orgEvent.axis === orgEvent.HORIZONTAL_AXIS) {
	          deltaX = deltaY * -1;
	          deltaY = 0;
	        }

	        // Set delta to be deltaY or deltaX if deltaY is 0 for backwards compatabilitiy
	        delta = deltaY === 0 ? deltaX : deltaY;

	        // New school wheel delta (wheel event)
	        if ('deltaY' in orgEvent) {
	          deltaY = orgEvent.deltaY * -1;
	          delta = deltaY;
	        }
	        if ('deltaX' in orgEvent) {
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
	          var lineHeight = $.data(this, 'mousewheel-line-height');
	          delta *= lineHeight;
	          deltaY *= lineHeight;
	          deltaX *= lineHeight;
	        } else if (orgEvent.deltaMode === 2) {
	          var pageHeight = $.data(this, 'mousewheel-page-height');
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
	        delta = Math[delta >= 1 ? 'floor' : 'ceil'](delta / lowestDelta);
	        deltaX = Math[deltaX >= 1 ? 'floor' : 'ceil'](deltaX / lowestDelta);
	        deltaY = Math[deltaY >= 1 ? 'floor' : 'ceil'](deltaY / lowestDelta);

	        // Normalise offsetX and offsetY properties
	        if (special.settings.normalizeOffset && this.getBoundingClientRect) {
	          var boundingRect = this.getBoundingClientRect();
	          offsetX = event.clientX - boundingRect.left;
	          offsetY = event.clientY - boundingRect.top;
	        }

	        // Add information to the event object
	        event.deltaX = deltaX;
	        event.deltaY = deltaY;
	        event.deltaFactor = lowestDelta;
	        event.offsetX = offsetX;
	        event.offsetY = offsetY;
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
	        return special.settings.adjustOldDeltas && orgEvent.type === 'mousewheel' && absDelta % 120 === 0;
	      }
	    });

	    S2.define('jquery.select2', ['jquery', 'jquery-mousewheel', './select2/core', './select2/defaults'], function ($, _, Select2, Defaults) {
	      if ($.fn.select2 == null) {
	        // All methods that should return the element
	        var thisMethods = ['open', 'close', 'destroy'];

	        $.fn.select2 = function (options) {
	          options = options || {};

	          if ((typeof options === 'undefined' ? 'undefined' : _typeof(options)) === 'object') {
	            this.each(function () {
	              var instanceOptions = $.extend(true, {}, options);

	              var instance = new Select2($(this), instanceOptions);
	            });

	            return this;
	          } else if (typeof options === 'string') {
	            var ret;
	            var args = Array.prototype.slice.call(arguments, 1);

	            this.each(function () {
	              var instance = $(this).data('select2');

	              if (instance == null && window.console && console.error) {
	                console.error('The select2(\'' + options + '\') method was called on an ' + 'element that is not using Select2.');
	              }

	              ret = instance[options].apply(instance, args);
	            });

	            // Check if we should be returning `this`
	            if ($.inArray(options, thisMethods) > -1) {
	              return this;
	            }

	            return ret;
	          } else {
	            throw new Error('Invalid arguments for Select2: ' + options);
	          }
	        };
	      }

	      if ($.fn.select2.defaults == null) {
	        $.fn.select2.defaults = Defaults;
	      }

	      return Select2;
	    });

	    // Return the AMD loader configuration so it can be used outside of this file
	    return {
	      define: S2.define,
	      require: S2.require
	    };
	  }();

	  // Autoload the jQuery bindings
	  // We know that all of the modules exist above this, so we're safe
	  var select2 = S2.require('jquery.select2');

	  // Hold the AMD module references on the jQuery function that was just loaded
	  // This allows Select2 to use the internal loader outside of this file, such
	  // as in the language files.
	  jQuery.fn.select2.amd = S2;

	  // Return the Select2 instance for anyone who is importing it.
	  return select2;
	});

/***/ },
/* 6 */
/***/ function(module, exports) {

	// removed by extract-text-webpack-plugin

/***/ }
/******/ ]);