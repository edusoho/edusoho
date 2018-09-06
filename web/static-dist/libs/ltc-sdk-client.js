/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId, i) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		if(i != 0) {
/******/ 			installedModules[moduleId] = module;
/******/ 		}
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
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
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/static-dist/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 27);
/******/ })
/************************************************************************/
/******/ ({

/***/ "03b43af0ee3f8b9a2130":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

var _promise = __webpack_require__("98e8346a38f021750136");

var _promise2 = _interopRequireDefault(_promise);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = function (fn) {
  return function () {
    var gen = fn.apply(this, arguments);
    return new _promise2.default(function (resolve, reject) {
      function step(key, arg) {
        try {
          var info = gen[key](arg);
          var value = info.value;
        } catch (error) {
          reject(error);
          return;
        }

        if (info.done) {
          resolve(value);
        } else {
          return _promise2.default.resolve(value).then(function (value) {
            step("next", value);
          }, function (err) {
            step("throw", err);
          });
        }
      }

      return step("next");
    });
  };
};

/***/ }),

/***/ "056f31ce74b5eb458ec4":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var LIBRARY = __webpack_require__("dff9362b82bd4eafdef8");
var global = __webpack_require__("f27a607058b9cc8c5827");
var ctx = __webpack_require__("a0ce7130a92293994282");
var classof = __webpack_require__("1174b5b657e5fc33c6e4");
var $export = __webpack_require__("d30516674aade65150d3");
var isObject = __webpack_require__("f31cab22762bc6afaacb");
var aFunction = __webpack_require__("e82b6e3d752b3eb55e42");
var anInstance = __webpack_require__("33bec802cd119432e3e3");
var forOf = __webpack_require__("317d26c02ce4a92399f9");
var speciesConstructor = __webpack_require__("83b597dad746a364852b");
var task = __webpack_require__("29eb81e2dc99389adfa9").set;
var microtask = __webpack_require__("137290aa9e49254aae7e")();
var newPromiseCapabilityModule = __webpack_require__("a415db3a52e4fe9edb5f");
var perform = __webpack_require__("5640212163de424af892");
var userAgent = __webpack_require__("d1e7c9d71cb07a1681eb");
var promiseResolve = __webpack_require__("c198a9131d44657c0b4b");
var PROMISE = 'Promise';
var TypeError = global.TypeError;
var process = global.process;
var versions = process && process.versions;
var v8 = versions && versions.v8 || '';
var $Promise = global[PROMISE];
var isNode = classof(process) == 'process';
var empty = function () { /* empty */ };
var Internal, newGenericPromiseCapability, OwnPromiseCapability, Wrapper;
var newPromiseCapability = newGenericPromiseCapability = newPromiseCapabilityModule.f;

var USE_NATIVE = !!function () {
  try {
    // correct subclassing with @@species support
    var promise = $Promise.resolve(1);
    var FakePromise = (promise.constructor = {})[__webpack_require__("79f39f38b3e53642baf4")('species')] = function (exec) {
      exec(empty, empty);
    };
    // unhandled rejections tracking support, NodeJS Promise without it fails @@species test
    return (isNode || typeof PromiseRejectionEvent == 'function')
      && promise.then(empty) instanceof FakePromise
      // v8 6.6 (Node 10 and Chrome 66) have a bug with resolving custom thenables
      // https://bugs.chromium.org/p/chromium/issues/detail?id=830565
      // we can't detect it synchronously, so just check versions
      && v8.indexOf('6.6') !== 0
      && userAgent.indexOf('Chrome/66') === -1;
  } catch (e) { /* empty */ }
}();

// helpers
var isThenable = function (it) {
  var then;
  return isObject(it) && typeof (then = it.then) == 'function' ? then : false;
};
var notify = function (promise, isReject) {
  if (promise._n) return;
  promise._n = true;
  var chain = promise._c;
  microtask(function () {
    var value = promise._v;
    var ok = promise._s == 1;
    var i = 0;
    var run = function (reaction) {
      var handler = ok ? reaction.ok : reaction.fail;
      var resolve = reaction.resolve;
      var reject = reaction.reject;
      var domain = reaction.domain;
      var result, then, exited;
      try {
        if (handler) {
          if (!ok) {
            if (promise._h == 2) onHandleUnhandled(promise);
            promise._h = 1;
          }
          if (handler === true) result = value;
          else {
            if (domain) domain.enter();
            result = handler(value); // may throw
            if (domain) {
              domain.exit();
              exited = true;
            }
          }
          if (result === reaction.promise) {
            reject(TypeError('Promise-chain cycle'));
          } else if (then = isThenable(result)) {
            then.call(result, resolve, reject);
          } else resolve(result);
        } else reject(value);
      } catch (e) {
        if (domain && !exited) domain.exit();
        reject(e);
      }
    };
    while (chain.length > i) run(chain[i++]); // variable length - can't use forEach
    promise._c = [];
    promise._n = false;
    if (isReject && !promise._h) onUnhandled(promise);
  });
};
var onUnhandled = function (promise) {
  task.call(global, function () {
    var value = promise._v;
    var unhandled = isUnhandled(promise);
    var result, handler, console;
    if (unhandled) {
      result = perform(function () {
        if (isNode) {
          process.emit('unhandledRejection', value, promise);
        } else if (handler = global.onunhandledrejection) {
          handler({ promise: promise, reason: value });
        } else if ((console = global.console) && console.error) {
          console.error('Unhandled promise rejection', value);
        }
      });
      // Browsers should not trigger `rejectionHandled` event if it was handled here, NodeJS - should
      promise._h = isNode || isUnhandled(promise) ? 2 : 1;
    } promise._a = undefined;
    if (unhandled && result.e) throw result.v;
  });
};
var isUnhandled = function (promise) {
  return promise._h !== 1 && (promise._a || promise._c).length === 0;
};
var onHandleUnhandled = function (promise) {
  task.call(global, function () {
    var handler;
    if (isNode) {
      process.emit('rejectionHandled', promise);
    } else if (handler = global.onrejectionhandled) {
      handler({ promise: promise, reason: promise._v });
    }
  });
};
var $reject = function (value) {
  var promise = this;
  if (promise._d) return;
  promise._d = true;
  promise = promise._w || promise; // unwrap
  promise._v = value;
  promise._s = 2;
  if (!promise._a) promise._a = promise._c.slice();
  notify(promise, true);
};
var $resolve = function (value) {
  var promise = this;
  var then;
  if (promise._d) return;
  promise._d = true;
  promise = promise._w || promise; // unwrap
  try {
    if (promise === value) throw TypeError("Promise can't be resolved itself");
    if (then = isThenable(value)) {
      microtask(function () {
        var wrapper = { _w: promise, _d: false }; // wrap
        try {
          then.call(value, ctx($resolve, wrapper, 1), ctx($reject, wrapper, 1));
        } catch (e) {
          $reject.call(wrapper, e);
        }
      });
    } else {
      promise._v = value;
      promise._s = 1;
      notify(promise, false);
    }
  } catch (e) {
    $reject.call({ _w: promise, _d: false }, e); // wrap
  }
};

// constructor polyfill
if (!USE_NATIVE) {
  // 25.4.3.1 Promise(executor)
  $Promise = function Promise(executor) {
    anInstance(this, $Promise, PROMISE, '_h');
    aFunction(executor);
    Internal.call(this);
    try {
      executor(ctx($resolve, this, 1), ctx($reject, this, 1));
    } catch (err) {
      $reject.call(this, err);
    }
  };
  // eslint-disable-next-line no-unused-vars
  Internal = function Promise(executor) {
    this._c = [];             // <- awaiting reactions
    this._a = undefined;      // <- checked in isUnhandled reactions
    this._s = 0;              // <- state
    this._d = false;          // <- done
    this._v = undefined;      // <- value
    this._h = 0;              // <- rejection state, 0 - default, 1 - handled, 2 - unhandled
    this._n = false;          // <- notify
  };
  Internal.prototype = __webpack_require__("7fa6865fc5d4346657e4")($Promise.prototype, {
    // 25.4.5.3 Promise.prototype.then(onFulfilled, onRejected)
    then: function then(onFulfilled, onRejected) {
      var reaction = newPromiseCapability(speciesConstructor(this, $Promise));
      reaction.ok = typeof onFulfilled == 'function' ? onFulfilled : true;
      reaction.fail = typeof onRejected == 'function' && onRejected;
      reaction.domain = isNode ? process.domain : undefined;
      this._c.push(reaction);
      if (this._a) this._a.push(reaction);
      if (this._s) notify(this, false);
      return reaction.promise;
    },
    // 25.4.5.1 Promise.prototype.catch(onRejected)
    'catch': function (onRejected) {
      return this.then(undefined, onRejected);
    }
  });
  OwnPromiseCapability = function () {
    var promise = new Internal();
    this.promise = promise;
    this.resolve = ctx($resolve, promise, 1);
    this.reject = ctx($reject, promise, 1);
  };
  newPromiseCapabilityModule.f = newPromiseCapability = function (C) {
    return C === $Promise || C === Wrapper
      ? new OwnPromiseCapability(C)
      : newGenericPromiseCapability(C);
  };
}

$export($export.G + $export.W + $export.F * !USE_NATIVE, { Promise: $Promise });
__webpack_require__("cb274a31409742acdaf8")($Promise, PROMISE);
__webpack_require__("8e239ceca29caee246be")(PROMISE);
Wrapper = __webpack_require__("99ec8b6ce9e8b820539a")[PROMISE];

// statics
$export($export.S + $export.F * !USE_NATIVE, PROMISE, {
  // 25.4.4.5 Promise.reject(r)
  reject: function reject(r) {
    var capability = newPromiseCapability(this);
    var $$reject = capability.reject;
    $$reject(r);
    return capability.promise;
  }
});
$export($export.S + $export.F * (LIBRARY || !USE_NATIVE), PROMISE, {
  // 25.4.4.6 Promise.resolve(x)
  resolve: function resolve(x) {
    return promiseResolve(LIBRARY && this === Wrapper ? $Promise : this, x);
  }
});
$export($export.S + $export.F * !(USE_NATIVE && __webpack_require__("9192d99c20a30ab204d7")(function (iter) {
  $Promise.all(iter)['catch'](empty);
})), PROMISE, {
  // 25.4.4.1 Promise.all(iterable)
  all: function all(iterable) {
    var C = this;
    var capability = newPromiseCapability(C);
    var resolve = capability.resolve;
    var reject = capability.reject;
    var result = perform(function () {
      var values = [];
      var index = 0;
      var remaining = 1;
      forOf(iterable, false, function (promise) {
        var $index = index++;
        var alreadyCalled = false;
        values.push(undefined);
        remaining++;
        C.resolve(promise).then(function (value) {
          if (alreadyCalled) return;
          alreadyCalled = true;
          values[$index] = value;
          --remaining || resolve(values);
        }, reject);
      });
      --remaining || resolve(values);
    });
    if (result.e) reject(result.v);
    return capability.promise;
  },
  // 25.4.4.4 Promise.race(iterable)
  race: function race(iterable) {
    var C = this;
    var capability = newPromiseCapability(C);
    var reject = capability.reject;
    var result = perform(function () {
      forOf(iterable, false, function (promise) {
        C.resolve(promise).then(capability.resolve, reject);
      });
    });
    if (result.e) reject(result.v);
    return capability.promise;
  }
});


/***/ }),

/***/ "07a3448be5f694cdee04":
/***/ (function(module, exports, __webpack_require__) {

// Thank's IE8 for his funny defineProperty
module.exports = !__webpack_require__("457939c2e15065e32401")(function () {
  return Object.defineProperty({}, 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),

/***/ "0b7e406830a62ea861ca":
/***/ (function(module, exports, __webpack_require__) {

// 19.1.3.1 Object.assign(target, source)
var $export = __webpack_require__("d30516674aade65150d3");

$export($export.S + $export.F, 'Object', { assign: __webpack_require__("73d9129a636d63c0b81a") });


/***/ }),

/***/ "0e6d1c03aac99b65ccb1":
/***/ (function(module, exports) {

exports.f = {}.propertyIsEnumerable;


/***/ }),

/***/ "10f5bc3e6ad5971b57b0":
/***/ (function(module, exports, __webpack_require__) {

// 7.1.15 ToLength
var toInteger = __webpack_require__("26b2cb950fcc43474957");
var min = Math.min;
module.exports = function (it) {
  return it > 0 ? min(toInteger(it), 0x1fffffffffffff) : 0; // pow(2, 53) - 1 == 9007199254740991
};


/***/ }),

/***/ "1174b5b657e5fc33c6e4":
/***/ (function(module, exports, __webpack_require__) {

// getting tag from 19.1.3.6 Object.prototype.toString()
var cof = __webpack_require__("5f49a2718ac22ba53d59");
var TAG = __webpack_require__("79f39f38b3e53642baf4")('toStringTag');
// ES3 wrong here
var ARG = cof(function () { return arguments; }()) == 'Arguments';

// fallback for IE11 Script Access Denied error
var tryGet = function (it, key) {
  try {
    return it[key];
  } catch (e) { /* empty */ }
};

module.exports = function (it) {
  var O, T, B;
  return it === undefined ? 'Undefined' : it === null ? 'Null'
    // @@toStringTag case
    : typeof (T = tryGet(O = Object(it), TAG)) == 'string' ? T
    // builtinTag case
    : ARG ? cof(O)
    // ES3 arguments fallback
    : (B = cof(O)) == 'Object' && typeof O.callee == 'function' ? 'Arguments' : B;
};


/***/ }),

/***/ "11f30f0d206e0327acdc":
/***/ (function(module, exports) {

var id = 0;
var px = Math.random();
module.exports = function (key) {
  return 'Symbol('.concat(key === undefined ? '' : key, ')_', (++id + px).toString(36));
};


/***/ }),

/***/ "137290aa9e49254aae7e":
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__("f27a607058b9cc8c5827");
var macrotask = __webpack_require__("29eb81e2dc99389adfa9").set;
var Observer = global.MutationObserver || global.WebKitMutationObserver;
var process = global.process;
var Promise = global.Promise;
var isNode = __webpack_require__("5f49a2718ac22ba53d59")(process) == 'process';

module.exports = function () {
  var head, last, notify;

  var flush = function () {
    var parent, fn;
    if (isNode && (parent = process.domain)) parent.exit();
    while (head) {
      fn = head.fn;
      head = head.next;
      try {
        fn();
      } catch (e) {
        if (head) notify();
        else last = undefined;
        throw e;
      }
    } last = undefined;
    if (parent) parent.enter();
  };

  // Node.js
  if (isNode) {
    notify = function () {
      process.nextTick(flush);
    };
  // browsers with MutationObserver, except iOS Safari - https://github.com/zloirock/core-js/issues/339
  } else if (Observer && !(global.navigator && global.navigator.standalone)) {
    var toggle = true;
    var node = document.createTextNode('');
    new Observer(flush).observe(node, { characterData: true }); // eslint-disable-line no-new
    notify = function () {
      node.data = toggle = !toggle;
    };
  // environments with maybe non-completely correct, but existent Promise
  } else if (Promise && Promise.resolve) {
    // Promise.resolve without an argument throws an error in LG WebOS 2
    var promise = Promise.resolve(undefined);
    notify = function () {
      promise.then(flush);
    };
  // for other environments - macrotask based on:
  // - setImmediate
  // - MessageChannel
  // - window.postMessag
  // - onreadystatechange
  // - setTimeout
  } else {
    notify = function () {
      // strange IE + webpack dev server bug - use .call(global)
      macrotask.call(global, flush);
    };
  }

  return function (fn) {
    var task = { fn: fn, next: undefined };
    if (last) last.next = task;
    if (!head) {
      head = task;
      notify();
    } last = task;
  };
};


/***/ }),

/***/ "14126b102a8a946146ae":
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.2 / 15.2.3.5 Object.create(O [, Properties])
var anObject = __webpack_require__("6c3dda553b2c45fd2161");
var dPs = __webpack_require__("636e90758df583c6551d");
var enumBugKeys = __webpack_require__("25264dbf4ec8cd0a39ef");
var IE_PROTO = __webpack_require__("6b7c061c8481ac422c8d")('IE_PROTO');
var Empty = function () { /* empty */ };
var PROTOTYPE = 'prototype';

// Create object with fake `null` prototype: use iframe Object with cleared prototype
var createDict = function () {
  // Thrash, waste and sodomy: IE GC bug
  var iframe = __webpack_require__("1b58d36ee38e47f1c490")('iframe');
  var i = enumBugKeys.length;
  var lt = '<';
  var gt = '>';
  var iframeDocument;
  iframe.style.display = 'none';
  __webpack_require__("b24a8b38059690cfe907").appendChild(iframe);
  iframe.src = 'javascript:'; // eslint-disable-line no-script-url
  // createDict = iframe.contentWindow.Object;
  // html.removeChild(iframe);
  iframeDocument = iframe.contentWindow.document;
  iframeDocument.open();
  iframeDocument.write(lt + 'script' + gt + 'document.F=Object' + lt + '/script' + gt);
  iframeDocument.close();
  createDict = iframeDocument.F;
  while (i--) delete createDict[PROTOTYPE][enumBugKeys[i]];
  return createDict();
};

module.exports = Object.create || function create(O, Properties) {
  var result;
  if (O !== null) {
    Empty[PROTOTYPE] = anObject(O);
    result = new Empty();
    Empty[PROTOTYPE] = null;
    // add "__proto__" for Object.getPrototypeOf polyfill
    result[IE_PROTO] = O;
  } else result = createDict();
  return Properties === undefined ? result : dPs(result, Properties);
};


/***/ }),

/***/ "18266a2c56780b8d3afe":
/***/ (function(module, exports, __webpack_require__) {

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

// This method of obtaining a reference to the global object needs to be
// kept identical to the way it is obtained in runtime.js
var g = (function() { return this })() || Function("return this")();

// Use `getOwnPropertyNames` because not all browsers support calling
// `hasOwnProperty` on the global `self` object in a worker. See #183.
var hadRuntime = g.regeneratorRuntime &&
  Object.getOwnPropertyNames(g).indexOf("regeneratorRuntime") >= 0;

// Save the old regeneratorRuntime in case it needs to be restored later.
var oldRuntime = hadRuntime && g.regeneratorRuntime;

// Force reevalutation of runtime.js.
g.regeneratorRuntime = undefined;

module.exports = __webpack_require__("280d92cedd4801edbe66");

if (hadRuntime) {
  // Restore the original runtime.
  g.regeneratorRuntime = oldRuntime;
} else {
  // Remove the global property added by runtime.js.
  try {
    delete g.regeneratorRuntime;
  } catch(e) {
    g.regeneratorRuntime = undefined;
  }
}


/***/ }),

/***/ "1b58d36ee38e47f1c490":
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__("f31cab22762bc6afaacb");
var document = __webpack_require__("f27a607058b9cc8c5827").document;
// typeof document.createElement is 'object' in old IE
var is = isObject(document) && isObject(document.createElement);
module.exports = function (it) {
  return is ? document.createElement(it) : {};
};


/***/ }),

/***/ "1c9181382673925a0296":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_component_emitter__ = __webpack_require__("7eb8a3021bf3206e8c37");
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_component_emitter___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_component_emitter__);
const Messenger = __webpack_require__("6a8597c74e19bda39603");


class EsMessenger extends __WEBPACK_IMPORTED_MODULE_0_component_emitter___default.a {
  constructor(options) {
    super();
    this.name = options.name;
    this.project = options.project;
    this.children = options.children;
    this.type = options.type; //enum: parent,child
    this.setup();
  }

  setup() {
    var messenger = new Messenger(this.name, this.project);
    if (this.type == 'child') { //同时广播同域和者跨域
      messenger.addTarget(window.parent, 'parent');
      messenger.addTarget(window.self, 'partner');
    } else if (this.type == 'parent') {
      messenger.addTarget(window.self, 'child');
      var children = this.children;
      for (var i = children.length - 1; i >= 0; i--) {
        messenger.addTarget(children[i].contentWindow, children[i].id);
      }
    }

    messenger.listen((msg) => {
      msg = JSON.parse(msg);
      this.emit(msg.eventName, msg.args);
    });
    this.messenger = messenger;

  }

  sendToParent(eventName, args) {
    for (var target in this.messenger.targets) {
      this.messenger.targets[target].send(
        this.convertToString(eventName, args)
      );
    }
  }

  sendToChild(child, eventName, args) {
    this.messenger.targets[child.id].send(
      this.convertToString(eventName, args)
    );
  }

  convertToString(eventName, args) {
    var msg = {'eventName': eventName, 'args': args};
    msg = JSON.stringify(msg);
    return msg;
  }
}
/* harmony export (immutable) */ __webpack_exports__["default"] = EsMessenger;


/***/ }),

/***/ "25264dbf4ec8cd0a39ef":
/***/ (function(module, exports) {

// IE 8- don't enum bug keys
module.exports = (
  'constructor,hasOwnProperty,isPrototypeOf,propertyIsEnumerable,toLocaleString,toString,valueOf'
).split(',');


/***/ }),

/***/ "26b2cb950fcc43474957":
/***/ (function(module, exports) {

// 7.1.4 ToInteger
var ceil = Math.ceil;
var floor = Math.floor;
module.exports = function (it) {
  return isNaN(it = +it) ? 0 : (it > 0 ? floor : ceil)(it);
};


/***/ }),

/***/ 27:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__("d2592be7ddc193d42ea4");


/***/ }),

/***/ "280d92cedd4801edbe66":
/***/ (function(module, exports) {

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

!(function(global) {
  "use strict";

  var Op = Object.prototype;
  var hasOwn = Op.hasOwnProperty;
  var undefined; // More compressible than void 0.
  var $Symbol = typeof Symbol === "function" ? Symbol : {};
  var iteratorSymbol = $Symbol.iterator || "@@iterator";
  var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
  var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

  var inModule = typeof module === "object";
  var runtime = global.regeneratorRuntime;
  if (runtime) {
    if (inModule) {
      // If regeneratorRuntime is defined globally and we're in a module,
      // make the exports object identical to regeneratorRuntime.
      module.exports = runtime;
    }
    // Don't bother evaluating the rest of this file if the runtime was
    // already defined globally.
    return;
  }

  // Define the runtime globally (as expected by generated code) as either
  // module.exports (if we're in a module) or a new, empty object.
  runtime = global.regeneratorRuntime = inModule ? module.exports : {};

  function wrap(innerFn, outerFn, self, tryLocsList) {
    // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
    var generator = Object.create(protoGenerator.prototype);
    var context = new Context(tryLocsList || []);

    // The ._invoke method unifies the implementations of the .next,
    // .throw, and .return methods.
    generator._invoke = makeInvokeMethod(innerFn, self, context);

    return generator;
  }
  runtime.wrap = wrap;

  // Try/catch helper to minimize deoptimizations. Returns a completion
  // record like context.tryEntries[i].completion. This interface could
  // have been (and was previously) designed to take a closure to be
  // invoked without arguments, but in all the cases we care about we
  // already have an existing method we want to call, so there's no need
  // to create a new function object. We can even get away with assuming
  // the method takes exactly one argument, since that happens to be true
  // in every case, so we don't have to touch the arguments object. The
  // only additional allocation required is the completion record, which
  // has a stable shape and so hopefully should be cheap to allocate.
  function tryCatch(fn, obj, arg) {
    try {
      return { type: "normal", arg: fn.call(obj, arg) };
    } catch (err) {
      return { type: "throw", arg: err };
    }
  }

  var GenStateSuspendedStart = "suspendedStart";
  var GenStateSuspendedYield = "suspendedYield";
  var GenStateExecuting = "executing";
  var GenStateCompleted = "completed";

  // Returning this object from the innerFn has the same effect as
  // breaking out of the dispatch switch statement.
  var ContinueSentinel = {};

  // Dummy constructor functions that we use as the .constructor and
  // .constructor.prototype properties for functions that return Generator
  // objects. For full spec compliance, you may wish to configure your
  // minifier not to mangle the names of these two functions.
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}

  // This is a polyfill for %IteratorPrototype% for environments that
  // don't natively support it.
  var IteratorPrototype = {};
  IteratorPrototype[iteratorSymbol] = function () {
    return this;
  };

  var getProto = Object.getPrototypeOf;
  var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
  if (NativeIteratorPrototype &&
      NativeIteratorPrototype !== Op &&
      hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
    // This environment has a native %IteratorPrototype%; use it instead
    // of the polyfill.
    IteratorPrototype = NativeIteratorPrototype;
  }

  var Gp = GeneratorFunctionPrototype.prototype =
    Generator.prototype = Object.create(IteratorPrototype);
  GeneratorFunction.prototype = Gp.constructor = GeneratorFunctionPrototype;
  GeneratorFunctionPrototype.constructor = GeneratorFunction;
  GeneratorFunctionPrototype[toStringTagSymbol] =
    GeneratorFunction.displayName = "GeneratorFunction";

  // Helper for defining the .next, .throw, and .return methods of the
  // Iterator interface in terms of a single ._invoke method.
  function defineIteratorMethods(prototype) {
    ["next", "throw", "return"].forEach(function(method) {
      prototype[method] = function(arg) {
        return this._invoke(method, arg);
      };
    });
  }

  runtime.isGeneratorFunction = function(genFun) {
    var ctor = typeof genFun === "function" && genFun.constructor;
    return ctor
      ? ctor === GeneratorFunction ||
        // For the native GeneratorFunction constructor, the best we can
        // do is to check its .name property.
        (ctor.displayName || ctor.name) === "GeneratorFunction"
      : false;
  };

  runtime.mark = function(genFun) {
    if (Object.setPrototypeOf) {
      Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
    } else {
      genFun.__proto__ = GeneratorFunctionPrototype;
      if (!(toStringTagSymbol in genFun)) {
        genFun[toStringTagSymbol] = "GeneratorFunction";
      }
    }
    genFun.prototype = Object.create(Gp);
    return genFun;
  };

  // Within the body of any async function, `await x` is transformed to
  // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
  // `hasOwn.call(value, "__await")` to determine if the yielded value is
  // meant to be awaited.
  runtime.awrap = function(arg) {
    return { __await: arg };
  };

  function AsyncIterator(generator) {
    function invoke(method, arg, resolve, reject) {
      var record = tryCatch(generator[method], generator, arg);
      if (record.type === "throw") {
        reject(record.arg);
      } else {
        var result = record.arg;
        var value = result.value;
        if (value &&
            typeof value === "object" &&
            hasOwn.call(value, "__await")) {
          return Promise.resolve(value.__await).then(function(value) {
            invoke("next", value, resolve, reject);
          }, function(err) {
            invoke("throw", err, resolve, reject);
          });
        }

        return Promise.resolve(value).then(function(unwrapped) {
          // When a yielded Promise is resolved, its final value becomes
          // the .value of the Promise<{value,done}> result for the
          // current iteration. If the Promise is rejected, however, the
          // result for this iteration will be rejected with the same
          // reason. Note that rejections of yielded Promises are not
          // thrown back into the generator function, as is the case
          // when an awaited Promise is rejected. This difference in
          // behavior between yield and await is important, because it
          // allows the consumer to decide what to do with the yielded
          // rejection (swallow it and continue, manually .throw it back
          // into the generator, abandon iteration, whatever). With
          // await, by contrast, there is no opportunity to examine the
          // rejection reason outside the generator function, so the
          // only option is to throw it from the await expression, and
          // let the generator function handle the exception.
          result.value = unwrapped;
          resolve(result);
        }, reject);
      }
    }

    var previousPromise;

    function enqueue(method, arg) {
      function callInvokeWithMethodAndArg() {
        return new Promise(function(resolve, reject) {
          invoke(method, arg, resolve, reject);
        });
      }

      return previousPromise =
        // If enqueue has been called before, then we want to wait until
        // all previous Promises have been resolved before calling invoke,
        // so that results are always delivered in the correct order. If
        // enqueue has not been called before, then it is important to
        // call invoke immediately, without waiting on a callback to fire,
        // so that the async generator function has the opportunity to do
        // any necessary setup in a predictable way. This predictability
        // is why the Promise constructor synchronously invokes its
        // executor callback, and why async functions synchronously
        // execute code before the first await. Since we implement simple
        // async functions in terms of async generators, it is especially
        // important to get this right, even though it requires care.
        previousPromise ? previousPromise.then(
          callInvokeWithMethodAndArg,
          // Avoid propagating failures to Promises returned by later
          // invocations of the iterator.
          callInvokeWithMethodAndArg
        ) : callInvokeWithMethodAndArg();
    }

    // Define the unified helper method that is used to implement .next,
    // .throw, and .return (see defineIteratorMethods).
    this._invoke = enqueue;
  }

  defineIteratorMethods(AsyncIterator.prototype);
  AsyncIterator.prototype[asyncIteratorSymbol] = function () {
    return this;
  };
  runtime.AsyncIterator = AsyncIterator;

  // Note that simple async functions are implemented on top of
  // AsyncIterator objects; they just return a Promise for the value of
  // the final result produced by the iterator.
  runtime.async = function(innerFn, outerFn, self, tryLocsList) {
    var iter = new AsyncIterator(
      wrap(innerFn, outerFn, self, tryLocsList)
    );

    return runtime.isGeneratorFunction(outerFn)
      ? iter // If outerFn is a generator, return the full iterator.
      : iter.next().then(function(result) {
          return result.done ? result.value : iter.next();
        });
  };

  function makeInvokeMethod(innerFn, self, context) {
    var state = GenStateSuspendedStart;

    return function invoke(method, arg) {
      if (state === GenStateExecuting) {
        throw new Error("Generator is already running");
      }

      if (state === GenStateCompleted) {
        if (method === "throw") {
          throw arg;
        }

        // Be forgiving, per 25.3.3.3.3 of the spec:
        // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume
        return doneResult();
      }

      context.method = method;
      context.arg = arg;

      while (true) {
        var delegate = context.delegate;
        if (delegate) {
          var delegateResult = maybeInvokeDelegate(delegate, context);
          if (delegateResult) {
            if (delegateResult === ContinueSentinel) continue;
            return delegateResult;
          }
        }

        if (context.method === "next") {
          // Setting context._sent for legacy support of Babel's
          // function.sent implementation.
          context.sent = context._sent = context.arg;

        } else if (context.method === "throw") {
          if (state === GenStateSuspendedStart) {
            state = GenStateCompleted;
            throw context.arg;
          }

          context.dispatchException(context.arg);

        } else if (context.method === "return") {
          context.abrupt("return", context.arg);
        }

        state = GenStateExecuting;

        var record = tryCatch(innerFn, self, context);
        if (record.type === "normal") {
          // If an exception is thrown from innerFn, we leave state ===
          // GenStateExecuting and loop back for another invocation.
          state = context.done
            ? GenStateCompleted
            : GenStateSuspendedYield;

          if (record.arg === ContinueSentinel) {
            continue;
          }

          return {
            value: record.arg,
            done: context.done
          };

        } else if (record.type === "throw") {
          state = GenStateCompleted;
          // Dispatch the exception by looping back around to the
          // context.dispatchException(context.arg) call above.
          context.method = "throw";
          context.arg = record.arg;
        }
      }
    };
  }

  // Call delegate.iterator[context.method](context.arg) and handle the
  // result, either by returning a { value, done } result from the
  // delegate iterator, or by modifying context.method and context.arg,
  // setting context.delegate to null, and returning the ContinueSentinel.
  function maybeInvokeDelegate(delegate, context) {
    var method = delegate.iterator[context.method];
    if (method === undefined) {
      // A .throw or .return when the delegate iterator has no .throw
      // method always terminates the yield* loop.
      context.delegate = null;

      if (context.method === "throw") {
        if (delegate.iterator.return) {
          // If the delegate iterator has a return method, give it a
          // chance to clean up.
          context.method = "return";
          context.arg = undefined;
          maybeInvokeDelegate(delegate, context);

          if (context.method === "throw") {
            // If maybeInvokeDelegate(context) changed context.method from
            // "return" to "throw", let that override the TypeError below.
            return ContinueSentinel;
          }
        }

        context.method = "throw";
        context.arg = new TypeError(
          "The iterator does not provide a 'throw' method");
      }

      return ContinueSentinel;
    }

    var record = tryCatch(method, delegate.iterator, context.arg);

    if (record.type === "throw") {
      context.method = "throw";
      context.arg = record.arg;
      context.delegate = null;
      return ContinueSentinel;
    }

    var info = record.arg;

    if (! info) {
      context.method = "throw";
      context.arg = new TypeError("iterator result is not an object");
      context.delegate = null;
      return ContinueSentinel;
    }

    if (info.done) {
      // Assign the result of the finished delegate to the temporary
      // variable specified by delegate.resultName (see delegateYield).
      context[delegate.resultName] = info.value;

      // Resume execution at the desired location (see delegateYield).
      context.next = delegate.nextLoc;

      // If context.method was "throw" but the delegate handled the
      // exception, let the outer generator proceed normally. If
      // context.method was "next", forget context.arg since it has been
      // "consumed" by the delegate iterator. If context.method was
      // "return", allow the original .return call to continue in the
      // outer generator.
      if (context.method !== "return") {
        context.method = "next";
        context.arg = undefined;
      }

    } else {
      // Re-yield the result returned by the delegate method.
      return info;
    }

    // The delegate iterator is finished, so forget it and continue with
    // the outer generator.
    context.delegate = null;
    return ContinueSentinel;
  }

  // Define Generator.prototype.{next,throw,return} in terms of the
  // unified ._invoke helper method.
  defineIteratorMethods(Gp);

  Gp[toStringTagSymbol] = "Generator";

  // A Generator should always return itself as the iterator object when the
  // @@iterator function is called on it. Some browsers' implementations of the
  // iterator prototype chain incorrectly implement this, causing the Generator
  // object to not be returned from this call. This ensures that doesn't happen.
  // See https://github.com/facebook/regenerator/issues/274 for more details.
  Gp[iteratorSymbol] = function() {
    return this;
  };

  Gp.toString = function() {
    return "[object Generator]";
  };

  function pushTryEntry(locs) {
    var entry = { tryLoc: locs[0] };

    if (1 in locs) {
      entry.catchLoc = locs[1];
    }

    if (2 in locs) {
      entry.finallyLoc = locs[2];
      entry.afterLoc = locs[3];
    }

    this.tryEntries.push(entry);
  }

  function resetTryEntry(entry) {
    var record = entry.completion || {};
    record.type = "normal";
    delete record.arg;
    entry.completion = record;
  }

  function Context(tryLocsList) {
    // The root entry object (effectively a try statement without a catch
    // or a finally block) gives us a place to store values thrown from
    // locations where there is no enclosing try statement.
    this.tryEntries = [{ tryLoc: "root" }];
    tryLocsList.forEach(pushTryEntry, this);
    this.reset(true);
  }

  runtime.keys = function(object) {
    var keys = [];
    for (var key in object) {
      keys.push(key);
    }
    keys.reverse();

    // Rather than returning an object with a next method, we keep
    // things simple and return the next function itself.
    return function next() {
      while (keys.length) {
        var key = keys.pop();
        if (key in object) {
          next.value = key;
          next.done = false;
          return next;
        }
      }

      // To avoid creating an additional object, we just hang the .value
      // and .done properties off the next function object itself. This
      // also ensures that the minifier will not anonymize the function.
      next.done = true;
      return next;
    };
  };

  function values(iterable) {
    if (iterable) {
      var iteratorMethod = iterable[iteratorSymbol];
      if (iteratorMethod) {
        return iteratorMethod.call(iterable);
      }

      if (typeof iterable.next === "function") {
        return iterable;
      }

      if (!isNaN(iterable.length)) {
        var i = -1, next = function next() {
          while (++i < iterable.length) {
            if (hasOwn.call(iterable, i)) {
              next.value = iterable[i];
              next.done = false;
              return next;
            }
          }

          next.value = undefined;
          next.done = true;

          return next;
        };

        return next.next = next;
      }
    }

    // Return an iterator with no values.
    return { next: doneResult };
  }
  runtime.values = values;

  function doneResult() {
    return { value: undefined, done: true };
  }

  Context.prototype = {
    constructor: Context,

    reset: function(skipTempReset) {
      this.prev = 0;
      this.next = 0;
      // Resetting context._sent for legacy support of Babel's
      // function.sent implementation.
      this.sent = this._sent = undefined;
      this.done = false;
      this.delegate = null;

      this.method = "next";
      this.arg = undefined;

      this.tryEntries.forEach(resetTryEntry);

      if (!skipTempReset) {
        for (var name in this) {
          // Not sure about the optimal order of these conditions:
          if (name.charAt(0) === "t" &&
              hasOwn.call(this, name) &&
              !isNaN(+name.slice(1))) {
            this[name] = undefined;
          }
        }
      }
    },

    stop: function() {
      this.done = true;

      var rootEntry = this.tryEntries[0];
      var rootRecord = rootEntry.completion;
      if (rootRecord.type === "throw") {
        throw rootRecord.arg;
      }

      return this.rval;
    },

    dispatchException: function(exception) {
      if (this.done) {
        throw exception;
      }

      var context = this;
      function handle(loc, caught) {
        record.type = "throw";
        record.arg = exception;
        context.next = loc;

        if (caught) {
          // If the dispatched exception was caught by a catch block,
          // then let that catch block handle the exception normally.
          context.method = "next";
          context.arg = undefined;
        }

        return !! caught;
      }

      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        var record = entry.completion;

        if (entry.tryLoc === "root") {
          // Exception thrown outside of any try block that could handle
          // it, so set the completion value of the entire function to
          // throw the exception.
          return handle("end");
        }

        if (entry.tryLoc <= this.prev) {
          var hasCatch = hasOwn.call(entry, "catchLoc");
          var hasFinally = hasOwn.call(entry, "finallyLoc");

          if (hasCatch && hasFinally) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            } else if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else if (hasCatch) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            }

          } else if (hasFinally) {
            if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else {
            throw new Error("try statement without catch or finally");
          }
        }
      }
    },

    abrupt: function(type, arg) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc <= this.prev &&
            hasOwn.call(entry, "finallyLoc") &&
            this.prev < entry.finallyLoc) {
          var finallyEntry = entry;
          break;
        }
      }

      if (finallyEntry &&
          (type === "break" ||
           type === "continue") &&
          finallyEntry.tryLoc <= arg &&
          arg <= finallyEntry.finallyLoc) {
        // Ignore the finally entry if control is not jumping to a
        // location outside the try/catch block.
        finallyEntry = null;
      }

      var record = finallyEntry ? finallyEntry.completion : {};
      record.type = type;
      record.arg = arg;

      if (finallyEntry) {
        this.method = "next";
        this.next = finallyEntry.finallyLoc;
        return ContinueSentinel;
      }

      return this.complete(record);
    },

    complete: function(record, afterLoc) {
      if (record.type === "throw") {
        throw record.arg;
      }

      if (record.type === "break" ||
          record.type === "continue") {
        this.next = record.arg;
      } else if (record.type === "return") {
        this.rval = this.arg = record.arg;
        this.method = "return";
        this.next = "end";
      } else if (record.type === "normal" && afterLoc) {
        this.next = afterLoc;
      }

      return ContinueSentinel;
    },

    finish: function(finallyLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.finallyLoc === finallyLoc) {
          this.complete(entry.completion, entry.afterLoc);
          resetTryEntry(entry);
          return ContinueSentinel;
        }
      }
    },

    "catch": function(tryLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc === tryLoc) {
          var record = entry.completion;
          if (record.type === "throw") {
            var thrown = record.arg;
            resetTryEntry(entry);
          }
          return thrown;
        }
      }

      // The context.catch method must only be called with a location
      // argument that corresponds to a known catch block.
      throw new Error("illegal catch attempt");
    },

    delegateYield: function(iterable, resultName, nextLoc) {
      this.delegate = {
        iterator: values(iterable),
        resultName: resultName,
        nextLoc: nextLoc
      };

      if (this.method === "next") {
        // Deliberately forget the last sent value so that we don't
        // accidentally pass it on to the delegate.
        this.arg = undefined;
      }

      return ContinueSentinel;
    }
  };
})(
  // In sloppy mode, unbound `this` refers to the global object, fallback to
  // Function constructor if we're in global strict mode. That is sadly a form
  // of indirect eval which violates Content Security Policy.
  (function() { return this })() || Function("return this")()
);


/***/ }),

/***/ "29eb81e2dc99389adfa9":
/***/ (function(module, exports, __webpack_require__) {

var ctx = __webpack_require__("a0ce7130a92293994282");
var invoke = __webpack_require__("a2635f47ea99b1a7133e");
var html = __webpack_require__("b24a8b38059690cfe907");
var cel = __webpack_require__("1b58d36ee38e47f1c490");
var global = __webpack_require__("f27a607058b9cc8c5827");
var process = global.process;
var setTask = global.setImmediate;
var clearTask = global.clearImmediate;
var MessageChannel = global.MessageChannel;
var Dispatch = global.Dispatch;
var counter = 0;
var queue = {};
var ONREADYSTATECHANGE = 'onreadystatechange';
var defer, channel, port;
var run = function () {
  var id = +this;
  // eslint-disable-next-line no-prototype-builtins
  if (queue.hasOwnProperty(id)) {
    var fn = queue[id];
    delete queue[id];
    fn();
  }
};
var listener = function (event) {
  run.call(event.data);
};
// Node.js 0.9+ & IE10+ has setImmediate, otherwise:
if (!setTask || !clearTask) {
  setTask = function setImmediate(fn) {
    var args = [];
    var i = 1;
    while (arguments.length > i) args.push(arguments[i++]);
    queue[++counter] = function () {
      // eslint-disable-next-line no-new-func
      invoke(typeof fn == 'function' ? fn : Function(fn), args);
    };
    defer(counter);
    return counter;
  };
  clearTask = function clearImmediate(id) {
    delete queue[id];
  };
  // Node.js 0.8-
  if (__webpack_require__("5f49a2718ac22ba53d59")(process) == 'process') {
    defer = function (id) {
      process.nextTick(ctx(run, id, 1));
    };
  // Sphere (JS game engine) Dispatch API
  } else if (Dispatch && Dispatch.now) {
    defer = function (id) {
      Dispatch.now(ctx(run, id, 1));
    };
  // Browsers with MessageChannel, includes WebWorkers
  } else if (MessageChannel) {
    channel = new MessageChannel();
    port = channel.port2;
    channel.port1.onmessage = listener;
    defer = ctx(port.postMessage, port, 1);
  // Browsers with postMessage, skip WebWorkers
  // IE8 has postMessage, but it's sync & typeof its postMessage is 'object'
  } else if (global.addEventListener && typeof postMessage == 'function' && !global.importScripts) {
    defer = function (id) {
      global.postMessage(id + '', '*');
    };
    global.addEventListener('message', listener, false);
  // IE8-
  } else if (ONREADYSTATECHANGE in cel('script')) {
    defer = function (id) {
      html.appendChild(cel('script'))[ONREADYSTATECHANGE] = function () {
        html.removeChild(this);
        run.call(id);
      };
    };
  // Rest old browsers
  } else {
    defer = function (id) {
      setTimeout(ctx(run, id, 1), 0);
    };
  }
}
module.exports = {
  set: setTask,
  clear: clearTask
};


/***/ }),

/***/ "2ae529d75fd1f719d626":
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__("e7ceb28b20870340ea1d"), __esModule: true };

/***/ }),

/***/ "2be1a6d982f843e91a8a":
/***/ (function(module, exports, __webpack_require__) {

var core = __webpack_require__("99ec8b6ce9e8b820539a");
var global = __webpack_require__("f27a607058b9cc8c5827");
var SHARED = '__core-js_shared__';
var store = global[SHARED] || (global[SHARED] = {});

(module.exports = function (key, value) {
  return store[key] || (store[key] = value !== undefined ? value : {});
})('versions', []).push({
  version: core.version,
  mode: __webpack_require__("dff9362b82bd4eafdef8") ? 'pure' : 'global',
  copyright: '© 2018 Denis Pushkarev (zloirock.ru)'
});


/***/ }),

/***/ "2becabd90e65528fbb02":
/***/ (function(module, exports, __webpack_require__) {

var $export = __webpack_require__("d30516674aade65150d3");
// 19.1.2.4 / 15.2.3.6 Object.defineProperty(O, P, Attributes)
$export($export.S + $export.F * !__webpack_require__("07a3448be5f694cdee04"), 'Object', { defineProperty: __webpack_require__("30373b8543ac2ca8f199").f });


/***/ }),

/***/ "2d54b0801acfe6b5b5d9":
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__("7ef299c1fdb884750de8"), __esModule: true };

/***/ }),

/***/ "30373b8543ac2ca8f199":
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__("6c3dda553b2c45fd2161");
var IE8_DOM_DEFINE = __webpack_require__("bb82a2b96f436781c23d");
var toPrimitive = __webpack_require__("5a32a62db759afe4c196");
var dP = Object.defineProperty;

exports.f = __webpack_require__("07a3448be5f694cdee04") ? Object.defineProperty : function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPrimitive(P, true);
  anObject(Attributes);
  if (IE8_DOM_DEFINE) try {
    return dP(O, P, Attributes);
  } catch (e) { /* empty */ }
  if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported!');
  if ('value' in Attributes) O[P] = Attributes.value;
  return O;
};


/***/ }),

/***/ "317d26c02ce4a92399f9":
/***/ (function(module, exports, __webpack_require__) {

var ctx = __webpack_require__("a0ce7130a92293994282");
var call = __webpack_require__("be5ff68e5fc2b36ab3fd");
var isArrayIter = __webpack_require__("7bbcd029dba3160182ef");
var anObject = __webpack_require__("6c3dda553b2c45fd2161");
var toLength = __webpack_require__("10f5bc3e6ad5971b57b0");
var getIterFn = __webpack_require__("a5b03ec781f71e8d0bed");
var BREAK = {};
var RETURN = {};
var exports = module.exports = function (iterable, entries, fn, that, ITERATOR) {
  var iterFn = ITERATOR ? function () { return iterable; } : getIterFn(iterable);
  var f = ctx(fn, that, entries ? 2 : 1);
  var index = 0;
  var length, step, iterator, result;
  if (typeof iterFn != 'function') throw TypeError(iterable + ' is not iterable!');
  // fast case for arrays with default iterator
  if (isArrayIter(iterFn)) for (length = toLength(iterable.length); length > index; index++) {
    result = entries ? f(anObject(step = iterable[index])[0], step[1]) : f(iterable[index]);
    if (result === BREAK || result === RETURN) return result;
  } else for (iterator = iterFn.call(iterable); !(step = iterator.next()).done;) {
    result = call(iterator, f, step.value, entries);
    if (result === BREAK || result === RETURN) return result;
  }
};
exports.BREAK = BREAK;
exports.RETURN = RETURN;


/***/ }),

/***/ "33bec802cd119432e3e3":
/***/ (function(module, exports) {

module.exports = function (it, Constructor, name, forbiddenField) {
  if (!(it instanceof Constructor) || (forbiddenField !== undefined && forbiddenField in it)) {
    throw TypeError(name + ': incorrect invocation!');
  } return it;
};


/***/ }),

/***/ "373811bb5ca99f7bf840":
/***/ (function(module, exports) {

exports.f = Object.getOwnPropertySymbols;


/***/ }),

/***/ "3b681c301a1d7a1f874a":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("c4f9ccf6d4f9090cfe1b");
__webpack_require__("9f5e733705cd16698cdf");
__webpack_require__("bdf846462d003d52f301");
__webpack_require__("056f31ce74b5eb458ec4");
__webpack_require__("c9a111f30e434d88d80d");
__webpack_require__("d582d148ae5db37e805e");
module.exports = __webpack_require__("99ec8b6ce9e8b820539a").Promise;


/***/ }),

/***/ "4373c1ac6d15a5b2dd1f":
/***/ (function(module, exports) {

module.exports = function (bitmap, value) {
  return {
    enumerable: !(bitmap & 1),
    configurable: !(bitmap & 2),
    writable: !(bitmap & 4),
    value: value
  };
};


/***/ }),

/***/ "44cc46f02a04db587394":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var LIBRARY = __webpack_require__("dff9362b82bd4eafdef8");
var $export = __webpack_require__("d30516674aade65150d3");
var redefine = __webpack_require__("98e158dcbb7a6f824611");
var hide = __webpack_require__("f9e595817d8b54ee32b8");
var Iterators = __webpack_require__("ed94c6ffbb2bc6323c97");
var $iterCreate = __webpack_require__("d6e9c43d4248c3dfedec");
var setToStringTag = __webpack_require__("cb274a31409742acdaf8");
var getPrototypeOf = __webpack_require__("b96d3c311cc0f6a5337c");
var ITERATOR = __webpack_require__("79f39f38b3e53642baf4")('iterator');
var BUGGY = !([].keys && 'next' in [].keys()); // Safari has buggy iterators w/o `next`
var FF_ITERATOR = '@@iterator';
var KEYS = 'keys';
var VALUES = 'values';

var returnThis = function () { return this; };

module.exports = function (Base, NAME, Constructor, next, DEFAULT, IS_SET, FORCED) {
  $iterCreate(Constructor, NAME, next);
  var getMethod = function (kind) {
    if (!BUGGY && kind in proto) return proto[kind];
    switch (kind) {
      case KEYS: return function keys() { return new Constructor(this, kind); };
      case VALUES: return function values() { return new Constructor(this, kind); };
    } return function entries() { return new Constructor(this, kind); };
  };
  var TAG = NAME + ' Iterator';
  var DEF_VALUES = DEFAULT == VALUES;
  var VALUES_BUG = false;
  var proto = Base.prototype;
  var $native = proto[ITERATOR] || proto[FF_ITERATOR] || DEFAULT && proto[DEFAULT];
  var $default = $native || getMethod(DEFAULT);
  var $entries = DEFAULT ? !DEF_VALUES ? $default : getMethod('entries') : undefined;
  var $anyNative = NAME == 'Array' ? proto.entries || $native : $native;
  var methods, key, IteratorPrototype;
  // Fix native
  if ($anyNative) {
    IteratorPrototype = getPrototypeOf($anyNative.call(new Base()));
    if (IteratorPrototype !== Object.prototype && IteratorPrototype.next) {
      // Set @@toStringTag to native iterators
      setToStringTag(IteratorPrototype, TAG, true);
      // fix for some old engines
      if (!LIBRARY && typeof IteratorPrototype[ITERATOR] != 'function') hide(IteratorPrototype, ITERATOR, returnThis);
    }
  }
  // fix Array#{values, @@iterator}.name in V8 / FF
  if (DEF_VALUES && $native && $native.name !== VALUES) {
    VALUES_BUG = true;
    $default = function values() { return $native.call(this); };
  }
  // Define iterator
  if ((!LIBRARY || FORCED) && (BUGGY || VALUES_BUG || !proto[ITERATOR])) {
    hide(proto, ITERATOR, $default);
  }
  // Plug for library
  Iterators[NAME] = $default;
  Iterators[TAG] = returnThis;
  if (DEFAULT) {
    methods = {
      values: DEF_VALUES ? $default : getMethod(VALUES),
      keys: IS_SET ? $default : getMethod(KEYS),
      entries: $entries
    };
    if (FORCED) for (key in methods) {
      if (!(key in proto)) redefine(proto, key, methods[key]);
    } else $export($export.P + $export.F * (BUGGY || VALUES_BUG), NAME, methods);
  }
  return methods;
};


/***/ }),

/***/ "457939c2e15065e32401":
/***/ (function(module, exports) {

module.exports = function (exec) {
  try {
    return !!exec();
  } catch (e) {
    return true;
  }
};


/***/ }),

/***/ "45e4aa9de557cd674741":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _assign = __webpack_require__("adc6e7d88332518aa1ec");

var _assign2 = _interopRequireDefault(_assign);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.validate = function () {
  $.extend($.validator.messages, {
    required: '请输入%display%',
    remote: '请修正此字段',
    email: '请输入有效的电子邮件地址',
    url: '请输入有效的网址',
    date: '请输入有效的日期',
    dateISO: '请输入有效的日期 (YYYY-MM-DD)',
    number: '请输入有效的数字',
    digits: '只能输入整数',
    equalTo: '你的输入不相同',
    maxlength: '最多只能输入 {0} 个字符',
    minlength: '最少需要输入 {0} 个字符',
    rangelength: '请输入长度在 {0} 到 {1} 之间的字符串',
    range: '请输入范围在 {0} 到 {1} 之间的数值',
    max: '请输入不大于 {0} 的数值',
    min: '请输入不小于 {0} 的数值'
  });

  $.validator.setDefaults({
    errorClass: 'form-error-message help-block jq-validate-error',
    errorElement: 'p',
    onkeyup: false,
    ignore: '',
    ajax: false,
    currentDom: null,
    highlight: function highlight(element, errorClass, validClass) {
      var $row = $(element).addClass('form-control-error').closest('.form-group').addClass('has-error');
      $row.find('.help-block').hide();
    },
    unhighlight: function unhighlight(element, errorClass, validClass) {
      var $row = $(element).removeClass('form-control-error').closest('.form-group');
      $row.removeClass('has-error');
      $row.find('.help-block').show();
    },
    errorPlacement: function errorPlacement(error, element) {
      if (element.parent().hasClass('controls')) {
        element.parent('.controls').append(error);
      } else if (element.parent().hasClass('input-group')) {
        element.parent().after(error);
      } else if (element.parent().is('label')) {
        element.parent().parent().append(error);
      } else {
        element.parent().append(error);
      }
    },
    invalidHandler: function invalidHandler(data) {
      console.log(data);
    },
    submitError: function submitError(data) {
      console.log('submitError');
    },
    submitSuccess: function submitSuccess(data) {
      console.log('submitSuccess');
    },
    submitHandler: function submitHandler(form) {}
  });

  $.validator.addMethod('trim', function (value, element, params) {
    return this.optional(element) || $.trim(value).length > 0;
  }, '请输入%display%');

  $.validator.addMethod('course_title', function (value, element, params) {
    return this.optional(element) || /^[^<>]*$/.test(value);
  }, '不支持输入<、>字符');

  $.extend($.validator.prototype, {
    defaultMessage: function defaultMessage(element, rule) {
      if (typeof rule === 'string') {
        rule = { method: rule };
      }

      var message = this.findDefined(this.customMessage(element.name, rule.method), this.customDataMessage(element, rule.method),

      // 'title' is never undefined, so handle empty string as undefined
      !this.settings.ignoreTitle && element.title || undefined, $.validator.messages[rule.method], '<strong>Warning: No message defined for ' + element.name + '</strong>'),
          theregex = /\$?\{(\d+)\}/g,
          displayregex = /%display%/g;
      if (typeof message === 'function') {
        message = message.call(this, rule.parameters, element);
      } else if (theregex.test(message)) {
        message = $.validator.format(message.replace(theregex, '{$1}'), rule.parameters);
      }

      if (displayregex.test(message)) {
        var labeltext, name;
        var id = $(element).attr('id') || $(element).attr('name');
        if (id) {
          labeltext = $('label[for=' + id + ']').text();
          if (labeltext) {
            labeltext = labeltext.replace(/^[\*\s\:\：]*/, '').replace(/[\*\s\:\：]*$/, '');
          }
        }

        name = $(element).data('display') || $(element).attr('name');
        message = message.replace(displayregex, labeltext || name);
      }

      return message;
    }

  });
};

exports.jquery = function () {
  $.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };

  $(document).ajaxSend(function (a, b, c) {
    if (c.type === 'POST') {
      b.setRequestHeader('X-CSRF-Token', window.ltc.getContext().csrf);
    }
  });
};

exports.editor = function () {
  window.ltc.editor = function (value) {
    $('#' + value).data('imageDownloadUrl', window.ltc.getEditorConfig()['imageDownloadUrl']);

    return CKEDITOR.replace(value, (0, _assign2.default)({
      toolbar: 'Task',
      fileSingleSizeLimit: 2,
      allowedContent: true,
      height: 300
    }, window.ltc.getEditorConfig()));
  };
};

/***/ }),

/***/ "4602c3f5fe7ad9e3e91d":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

var _defineProperty = __webpack_require__("90ee69f654d94afb7eca");

var _defineProperty2 = _interopRequireDefault(_defineProperty);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      (0, _defineProperty2.default)(target, descriptor.key, descriptor);
    }
  }

  return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);
    if (staticProps) defineProperties(Constructor, staticProps);
    return Constructor;
  };
}();

/***/ }),

/***/ "50253127b2524d51f717":
/***/ (function(module, exports, __webpack_require__) {

var toInteger = __webpack_require__("26b2cb950fcc43474957");
var defined = __webpack_require__("60adac73113c6fc50379");
// true  -> String#at
// false -> String#codePointAt
module.exports = function (TO_STRING) {
  return function (that, pos) {
    var s = String(defined(that));
    var i = toInteger(pos);
    var l = s.length;
    var a, b;
    if (i < 0 || i >= l) return TO_STRING ? '' : undefined;
    a = s.charCodeAt(i);
    return a < 0xd800 || a > 0xdbff || i + 1 === l || (b = s.charCodeAt(i + 1)) < 0xdc00 || b > 0xdfff
      ? TO_STRING ? s.charAt(i) : a
      : TO_STRING ? s.slice(i, i + 2) : (a - 0xd800 << 10) + (b - 0xdc00) + 0x10000;
  };
};


/***/ }),

/***/ "5640212163de424af892":
/***/ (function(module, exports) {

module.exports = function (exec) {
  try {
    return { e: false, v: exec() };
  } catch (e) {
    return { e: true, v: e };
  }
};


/***/ }),

/***/ "5a1b42cd4752706663d8":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var $defineProperty = __webpack_require__("30373b8543ac2ca8f199");
var createDesc = __webpack_require__("4373c1ac6d15a5b2dd1f");

module.exports = function (object, index, value) {
  if (index in object) $defineProperty.f(object, index, createDesc(0, value));
  else object[index] = value;
};


/***/ }),

/***/ "5a32a62db759afe4c196":
/***/ (function(module, exports, __webpack_require__) {

// 7.1.1 ToPrimitive(input [, PreferredType])
var isObject = __webpack_require__("f31cab22762bc6afaacb");
// instead of the ES6 spec version, we didn't implement @@toPrimitive case
// and the second argument - flag - preferred type is a string
module.exports = function (it, S) {
  if (!isObject(it)) return it;
  var fn, val;
  if (S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  if (typeof (fn = it.valueOf) == 'function' && !isObject(val = fn.call(it))) return val;
  if (!S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  throw TypeError("Can't convert object to primitive value");
};


/***/ }),

/***/ "5f49a2718ac22ba53d59":
/***/ (function(module, exports) {

var toString = {}.toString;

module.exports = function (it) {
  return toString.call(it).slice(8, -1);
};


/***/ }),

/***/ "60adac73113c6fc50379":
/***/ (function(module, exports) {

// 7.2.1 RequireObjectCoercible(argument)
module.exports = function (it) {
  if (it == undefined) throw TypeError("Can't call method on  " + it);
  return it;
};


/***/ }),

/***/ "636e90758df583c6551d":
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__("30373b8543ac2ca8f199");
var anObject = __webpack_require__("6c3dda553b2c45fd2161");
var getKeys = __webpack_require__("d137ba7e6a11667e9035");

module.exports = __webpack_require__("07a3448be5f694cdee04") ? Object.defineProperties : function defineProperties(O, Properties) {
  anObject(O);
  var keys = getKeys(Properties);
  var length = keys.length;
  var i = 0;
  var P;
  while (length > i) dP.f(O, P = keys[i++], Properties[P]);
  return O;
};


/***/ }),

/***/ "68a72f4f57d2a9459710":
/***/ (function(module, exports, __webpack_require__) {

// false -> Array#indexOf
// true  -> Array#includes
var toIObject = __webpack_require__("9e0642ad0826b9d4cd08");
var toLength = __webpack_require__("10f5bc3e6ad5971b57b0");
var toAbsoluteIndex = __webpack_require__("e2544e9d164d5ae98195");
module.exports = function (IS_INCLUDES) {
  return function ($this, el, fromIndex) {
    var O = toIObject($this);
    var length = toLength(O.length);
    var index = toAbsoluteIndex(fromIndex, length);
    var value;
    // Array#includes uses SameValueZero equality algorithm
    // eslint-disable-next-line no-self-compare
    if (IS_INCLUDES && el != el) while (length > index) {
      value = O[index++];
      // eslint-disable-next-line no-self-compare
      if (value != value) return true;
    // Array#indexOf ignores holes, Array#includes - not
    } else for (;length > index; index++) if (IS_INCLUDES || index in O) {
      if (O[index] === el) return IS_INCLUDES || index || 0;
    } return !IS_INCLUDES && -1;
  };
};


/***/ }),

/***/ "68be1979658d3a49fbce":
/***/ (function(module, exports, __webpack_require__) {

// 7.1.13 ToObject(argument)
var defined = __webpack_require__("60adac73113c6fc50379");
module.exports = function (it) {
  return Object(defined(it));
};


/***/ }),

/***/ "6a8597c74e19bda39603":
/***/ (function(module, exports) {

module.exports = (function () {

    // 消息前缀, 建议使用自己的项目名, 避免多项目之间的冲突
    // !注意 消息前缀应使用字符串类型
    var prefix = '[PROJECT_NAME]', supportPostMessage = 'postMessage' in window;
  
    // Target 类, 消息对象
    function Target(target, name, prefix) {
      var errMsg = '';
      if (arguments.length < 2) {
        errMsg = 'target error - target and name are both required';
      } else if (typeof target != 'object') {
        errMsg = 'target error - target itself must be window object';
      } else if (typeof name != 'string') {
        errMsg = 'target error - target name must be string type';
      }
      if (errMsg) {
        throw new Error(errMsg);
      }
  
      this.target = target;
      this.name = name;
      this.prefix = prefix;
    }
  
    // 往 target 发送消息, 出于安全考虑, 发送消息会带上前缀
    if (supportPostMessage) {
      // IE8+ 以及现代浏览器支持
      Target.prototype.send = function (msg) {
        this.target.postMessage(this.prefix + '|' + this.name + '__Messenger__' + msg, '*');
      };
    } else {
      // 兼容IE 6/7
      Target.prototype.send = function (msg) {
        var targetFunc = window.navigator[this.prefix + this.name];
        if (typeof targetFunc == 'function') {
          targetFunc(this.prefix + msg, window);
        } else {
          throw new Error('target callback function is not defined');
        }
      };
    }
  
    // 信使类
    // 创建Messenger实例时指定, 必须指定Messenger的名字, (可选)指定项目名, 以避免Mashup类应用中的冲突
    // !注意: 父子页面中projectName必须保持一致, 否则无法匹配
    function Messenger(messengerName, projectName) {
      this.targets = {};
      this.name = messengerName;
      this.listenFunc = [];
      this.prefix = projectName || prefix;
      this.initListen();
    }
  
    // 添加一个消息对象
    Messenger.prototype.addTarget = function (target, name) {
      var targetObj = new Target(target, name, this.prefix);
      this.targets[name] = targetObj;
    };
  
    // 初始化消息监听
    Messenger.prototype.initListen = function () {
      var self = this;
      let deletefunction = function(){};
      if (window.esGeneralCallback) {
        deletefunction = window.esGeneralCallback;
      }
  
      window.esGeneralCallback = function (msg) {
        if (typeof msg == 'object' && msg.data) {
          msg = msg.data;
        }
  
        try {
          var msgPairs = msg.split('__Messenger__');
        } catch (e) {
          return true;
        }
  
        var sendmsg = msgPairs[1];
        var pairs = msgPairs[0].split('|');
        var prefix = pairs[0];
        var name = pairs[1];
  
        for (var i = 0; i < self.listenFunc.length; i++) {
          if (prefix + name === self.prefix + self.name) {
            self.listenFunc[i](sendmsg);
          }
        }
      };
  
      if (supportPostMessage) {
        if ('addEventListener' in document) {
          window.removeEventListener('message', deletefunction, false);
          window.addEventListener('message', window.esGeneralCallback, false);
        } else if ('attachEvent' in document) {
          window.detachEvent('message', deletefunction);
          window.attachEvent('onmessage', window.esGeneralCallback);
        }
      } else {
        // 兼容IE 6/7
        window.navigator[this.prefix + this.name] = window.esGeneralCallback ;
      }
    };
  
    // 监听消息
    Messenger.prototype.listen = function (callback) {
      var i = 0;
      var len = this.listenFunc.length;
      var cbIsExist = false;
      for (; i < len; i++) {
        if (this.listenFunc[i] == callback) {
          cbIsExist = true;
          break;
        }
      }
      if (!cbIsExist) {
        this.listenFunc.push(callback);
      }
    };
    // 注销监听
    Messenger.prototype.clear = function () {
      this.listenFunc = [];
    };
    // 广播消息
    Messenger.prototype.send = function (msg) {
      var targets = this.targets, target;
      for (target in targets) {
        if (targets.hasOwnProperty(target)) {
          targets[target].send(msg);
        }
      }
    };
  
    return Messenger;
  })();
  

/***/ }),

/***/ "6b7c061c8481ac422c8d":
/***/ (function(module, exports, __webpack_require__) {

var shared = __webpack_require__("2be1a6d982f843e91a8a")('keys');
var uid = __webpack_require__("11f30f0d206e0327acdc");
module.exports = function (key) {
  return shared[key] || (shared[key] = uid(key));
};


/***/ }),

/***/ "6c3dda553b2c45fd2161":
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__("f31cab22762bc6afaacb");
module.exports = function (it) {
  if (!isObject(it)) throw TypeError(it + ' is not an object!');
  return it;
};


/***/ }),

/***/ "73d9129a636d63c0b81a":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

// 19.1.2.1 Object.assign(target, source, ...)
var getKeys = __webpack_require__("d137ba7e6a11667e9035");
var gOPS = __webpack_require__("373811bb5ca99f7bf840");
var pIE = __webpack_require__("0e6d1c03aac99b65ccb1");
var toObject = __webpack_require__("68be1979658d3a49fbce");
var IObject = __webpack_require__("7d2b62f436fd0fdb95d0");
var $assign = Object.assign;

// should work with symbols and should have deterministic property order (V8 bug)
module.exports = !$assign || __webpack_require__("457939c2e15065e32401")(function () {
  var A = {};
  var B = {};
  // eslint-disable-next-line no-undef
  var S = Symbol();
  var K = 'abcdefghijklmnopqrst';
  A[S] = 7;
  K.split('').forEach(function (k) { B[k] = k; });
  return $assign({}, A)[S] != 7 || Object.keys($assign({}, B)).join('') != K;
}) ? function assign(target, source) { // eslint-disable-line no-unused-vars
  var T = toObject(target);
  var aLen = arguments.length;
  var index = 1;
  var getSymbols = gOPS.f;
  var isEnum = pIE.f;
  while (aLen > index) {
    var S = IObject(arguments[index++]);
    var keys = getSymbols ? getKeys(S).concat(getSymbols(S)) : getKeys(S);
    var length = keys.length;
    var j = 0;
    var key;
    while (length > j) if (isEnum.call(S, key = keys[j++])) T[key] = S[key];
  } return T;
} : $assign;


/***/ }),

/***/ "79f39f38b3e53642baf4":
/***/ (function(module, exports, __webpack_require__) {

var store = __webpack_require__("2be1a6d982f843e91a8a")('wks');
var uid = __webpack_require__("11f30f0d206e0327acdc");
var Symbol = __webpack_require__("f27a607058b9cc8c5827").Symbol;
var USE_SYMBOL = typeof Symbol == 'function';

var $exports = module.exports = function (name) {
  return store[name] || (store[name] =
    USE_SYMBOL && Symbol[name] || (USE_SYMBOL ? Symbol : uid)('Symbol.' + name));
};

$exports.store = store;


/***/ }),

/***/ "7a079e57c8ffba47bbed":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

var _from = __webpack_require__("2d54b0801acfe6b5b5d9");

var _from2 = _interopRequireDefault(_from);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = function (arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
      arr2[i] = arr[i];
    }

    return arr2;
  } else {
    return (0, _from2.default)(arr);
  }
};

/***/ }),

/***/ "7ab4a89ebadbfdecc2bf":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

exports.default = function (instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
};

/***/ }),

/***/ "7bbcd029dba3160182ef":
/***/ (function(module, exports, __webpack_require__) {

// check on default Array iterator
var Iterators = __webpack_require__("ed94c6ffbb2bc6323c97");
var ITERATOR = __webpack_require__("79f39f38b3e53642baf4")('iterator');
var ArrayProto = Array.prototype;

module.exports = function (it) {
  return it !== undefined && (Iterators.Array === it || ArrayProto[ITERATOR] === it);
};


/***/ }),

/***/ "7d2b62f436fd0fdb95d0":
/***/ (function(module, exports, __webpack_require__) {

// fallback for non-array-like ES3 and non-enumerable old V8 strings
var cof = __webpack_require__("5f49a2718ac22ba53d59");
// eslint-disable-next-line no-prototype-builtins
module.exports = Object('z').propertyIsEnumerable(0) ? Object : function (it) {
  return cof(it) == 'String' ? it.split('') : Object(it);
};


/***/ }),

/***/ "7eb8a3021bf3206e8c37":
/***/ (function(module, exports, __webpack_require__) {


/**
 * Expose `Emitter`.
 */

if (true) {
  module.exports = Emitter;
}

/**
 * Initialize a new `Emitter`.
 *
 * @api public
 */

function Emitter(obj) {
  if (obj) return mixin(obj);
};

/**
 * Mixin the emitter properties.
 *
 * @param {Object} obj
 * @return {Object}
 * @api private
 */

function mixin(obj) {
  for (var key in Emitter.prototype) {
    obj[key] = Emitter.prototype[key];
  }
  return obj;
}

/**
 * Listen on the given `event` with `fn`.
 *
 * @param {String} event
 * @param {Function} fn
 * @return {Emitter}
 * @api public
 */

Emitter.prototype.on =
Emitter.prototype.addEventListener = function(event, fn){
  this._callbacks = this._callbacks || {};
  (this._callbacks['$' + event] = this._callbacks['$' + event] || [])
    .push(fn);
  return this;
};

/**
 * Adds an `event` listener that will be invoked a single
 * time then automatically removed.
 *
 * @param {String} event
 * @param {Function} fn
 * @return {Emitter}
 * @api public
 */

Emitter.prototype.once = function(event, fn){
  function on() {
    this.off(event, on);
    fn.apply(this, arguments);
  }

  on.fn = fn;
  this.on(event, on);
  return this;
};

/**
 * Remove the given callback for `event` or all
 * registered callbacks.
 *
 * @param {String} event
 * @param {Function} fn
 * @return {Emitter}
 * @api public
 */

Emitter.prototype.off =
Emitter.prototype.removeListener =
Emitter.prototype.removeAllListeners =
Emitter.prototype.removeEventListener = function(event, fn){
  this._callbacks = this._callbacks || {};

  // all
  if (0 == arguments.length) {
    this._callbacks = {};
    return this;
  }

  // specific event
  var callbacks = this._callbacks['$' + event];
  if (!callbacks) return this;

  // remove all handlers
  if (1 == arguments.length) {
    delete this._callbacks['$' + event];
    return this;
  }

  // remove specific handler
  var cb;
  for (var i = 0; i < callbacks.length; i++) {
    cb = callbacks[i];
    if (cb === fn || cb.fn === fn) {
      callbacks.splice(i, 1);
      break;
    }
  }
  return this;
};

/**
 * Emit `event` with the given args.
 *
 * @param {String} event
 * @param {Mixed} ...
 * @return {Emitter}
 */

Emitter.prototype.emit = function(event){
  this._callbacks = this._callbacks || {};
  var args = [].slice.call(arguments, 1)
    , callbacks = this._callbacks['$' + event];

  if (callbacks) {
    callbacks = callbacks.slice(0);
    for (var i = 0, len = callbacks.length; i < len; ++i) {
      callbacks[i].apply(this, args);
    }
  }

  return this;
};

/**
 * Return array of callbacks for `event`.
 *
 * @param {String} event
 * @return {Array}
 * @api public
 */

Emitter.prototype.listeners = function(event){
  this._callbacks = this._callbacks || {};
  return this._callbacks['$' + event] || [];
};

/**
 * Check if this emitter has `event` handlers.
 *
 * @param {String} event
 * @return {Boolean}
 * @api public
 */

Emitter.prototype.hasListeners = function(event){
  return !! this.listeners(event).length;
};


/***/ }),

/***/ "7ef299c1fdb884750de8":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("9f5e733705cd16698cdf");
__webpack_require__("dfa37eb698c113f70efb");
module.exports = __webpack_require__("99ec8b6ce9e8b820539a").Array.from;


/***/ }),

/***/ "7fa6865fc5d4346657e4":
/***/ (function(module, exports, __webpack_require__) {

var hide = __webpack_require__("f9e595817d8b54ee32b8");
module.exports = function (target, src, safe) {
  for (var key in src) {
    if (safe && target[key]) target[key] = src[key];
    else hide(target, key, src[key]);
  } return target;
};


/***/ }),

/***/ "83b597dad746a364852b":
/***/ (function(module, exports, __webpack_require__) {

// 7.3.20 SpeciesConstructor(O, defaultConstructor)
var anObject = __webpack_require__("6c3dda553b2c45fd2161");
var aFunction = __webpack_require__("e82b6e3d752b3eb55e42");
var SPECIES = __webpack_require__("79f39f38b3e53642baf4")('species');
module.exports = function (O, D) {
  var C = anObject(O).constructor;
  var S;
  return C === undefined || (S = anObject(C)[SPECIES]) == undefined ? D : aFunction(S);
};


/***/ }),

/***/ "8e239ceca29caee246be":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var global = __webpack_require__("f27a607058b9cc8c5827");
var core = __webpack_require__("99ec8b6ce9e8b820539a");
var dP = __webpack_require__("30373b8543ac2ca8f199");
var DESCRIPTORS = __webpack_require__("07a3448be5f694cdee04");
var SPECIES = __webpack_require__("79f39f38b3e53642baf4")('species');

module.exports = function (KEY) {
  var C = typeof core[KEY] == 'function' ? core[KEY] : global[KEY];
  if (DESCRIPTORS && C && !C[SPECIES]) dP.f(C, SPECIES, {
    configurable: true,
    get: function () { return this; }
  });
};


/***/ }),

/***/ "90ee69f654d94afb7eca":
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__("bc1908f480b427a8a004"), __esModule: true };

/***/ }),

/***/ "9192d99c20a30ab204d7":
/***/ (function(module, exports, __webpack_require__) {

var ITERATOR = __webpack_require__("79f39f38b3e53642baf4")('iterator');
var SAFE_CLOSING = false;

try {
  var riter = [7][ITERATOR]();
  riter['return'] = function () { SAFE_CLOSING = true; };
  // eslint-disable-next-line no-throw-literal
  Array.from(riter, function () { throw 2; });
} catch (e) { /* empty */ }

module.exports = function (exec, skipClosing) {
  if (!skipClosing && !SAFE_CLOSING) return false;
  var safe = false;
  try {
    var arr = [7];
    var iter = arr[ITERATOR]();
    iter.next = function () { return { done: safe = true }; };
    arr[ITERATOR] = function () { return iter; };
    exec(arr);
  } catch (e) { /* empty */ }
  return safe;
};


/***/ }),

/***/ "98e158dcbb7a6f824611":
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__("f9e595817d8b54ee32b8");


/***/ }),

/***/ "98e8346a38f021750136":
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__("3b681c301a1d7a1f874a"), __esModule: true };

/***/ }),

/***/ "99ec8b6ce9e8b820539a":
/***/ (function(module, exports) {

var core = module.exports = { version: '2.5.6' };
if (typeof __e == 'number') __e = core; // eslint-disable-line no-undef


/***/ }),

/***/ "9e0642ad0826b9d4cd08":
/***/ (function(module, exports, __webpack_require__) {

// to indexed object, toObject with fallback for non-array-like ES3 strings
var IObject = __webpack_require__("7d2b62f436fd0fdb95d0");
var defined = __webpack_require__("60adac73113c6fc50379");
module.exports = function (it) {
  return IObject(defined(it));
};


/***/ }),

/***/ "9f5e733705cd16698cdf":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var $at = __webpack_require__("50253127b2524d51f717")(true);

// 21.1.3.27 String.prototype[@@iterator]()
__webpack_require__("44cc46f02a04db587394")(String, 'String', function (iterated) {
  this._t = String(iterated); // target
  this._i = 0;                // next index
// 21.1.5.2.1 %StringIteratorPrototype%.next()
}, function () {
  var O = this._t;
  var index = this._i;
  var point;
  if (index >= O.length) return { value: undefined, done: true };
  point = $at(O, index);
  this._i += point.length;
  return { value: point, done: false };
});


/***/ }),

/***/ "a0ce7130a92293994282":
/***/ (function(module, exports, __webpack_require__) {

// optional / simple context binding
var aFunction = __webpack_require__("e82b6e3d752b3eb55e42");
module.exports = function (fn, that, length) {
  aFunction(fn);
  if (that === undefined) return fn;
  switch (length) {
    case 1: return function (a) {
      return fn.call(that, a);
    };
    case 2: return function (a, b) {
      return fn.call(that, a, b);
    };
    case 3: return function (a, b, c) {
      return fn.call(that, a, b, c);
    };
  }
  return function (/* ...args */) {
    return fn.apply(that, arguments);
  };
};


/***/ }),

/***/ "a15eafa3f37bdba875c3":
/***/ (function(module, exports) {

module.exports = {"apiList":["getCourse","getAnnouncement","getCourseDraft","saveCourseDraft","getActivity","getActivityResource","saveActivityResource","updateActivityResource"]}

/***/ }),

/***/ "a2635f47ea99b1a7133e":
/***/ (function(module, exports) {

// fast apply, http://jsperf.lnkit.com/fast-apply/5
module.exports = function (fn, args, that) {
  var un = that === undefined;
  switch (args.length) {
    case 0: return un ? fn()
                      : fn.call(that);
    case 1: return un ? fn(args[0])
                      : fn.call(that, args[0]);
    case 2: return un ? fn(args[0], args[1])
                      : fn.call(that, args[0], args[1]);
    case 3: return un ? fn(args[0], args[1], args[2])
                      : fn.call(that, args[0], args[1], args[2]);
    case 4: return un ? fn(args[0], args[1], args[2], args[3])
                      : fn.call(that, args[0], args[1], args[2], args[3]);
  } return fn.apply(that, args);
};


/***/ }),

/***/ "a415db3a52e4fe9edb5f":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

// 25.4.1.5 NewPromiseCapability(C)
var aFunction = __webpack_require__("e82b6e3d752b3eb55e42");

function PromiseCapability(C) {
  var resolve, reject;
  this.promise = new C(function ($$resolve, $$reject) {
    if (resolve !== undefined || reject !== undefined) throw TypeError('Bad Promise constructor');
    resolve = $$resolve;
    reject = $$reject;
  });
  this.resolve = aFunction(resolve);
  this.reject = aFunction(reject);
}

module.exports.f = function (C) {
  return new PromiseCapability(C);
};


/***/ }),

/***/ "a50cfb5e0ca3918fbabe":
/***/ (function(module, exports) {

/*
 * File: iframeResizer.contentWindow.js
 * Desc: Include this file in any page being loaded into an iframe
 *       to force the iframe to resize to the content size.
 * Requires: iframeResizer.js on host page.
 * Doc: https://github.com/davidjbradshaw/iframe-resizer
 * Author: David J. Bradshaw - dave@bradshaw.net
 * Contributor: Jure Mav - jure.mav@gmail.com
 * Contributor: Ian Caunce - ian@hallnet.co.uk
 */


;(function(window, undefined) {
	'use strict';

	var
		autoResize            = true,
		base                  = 10,
		bodyBackground        = '',
		bodyMargin            = 0,
		bodyMarginStr         = '',
		bodyObserver          = null,
		bodyPadding           = '',
		calculateWidth        = false,
		doubleEventList       = {'resize':1,'click':1},
		eventCancelTimer      = 128,
		firstRun              = true,
		height                = 1,
		heightCalcModeDefault = 'bodyOffset',
		heightCalcMode        = heightCalcModeDefault,
		initLock              = true,
		initMsg               = '',
		inPageLinks           = {},
		interval              = 32,
		intervalTimer         = null,
		logging               = false,
		msgID                 = '[iFrameSizer]',  //Must match host page msg ID
		msgIdLen              = msgID.length,
		myID                  = '',
		observer              = null,
		resetRequiredMethods  = {max:1,min:1,bodyScroll:1,documentElementScroll:1},
		resizeFrom            = 'child',
		sendPermit            = true,
		target                = window.parent,
		targetOriginDefault   = '*',
		tolerance             = 0,
		triggerLocked         = false,
		triggerLockedTimer    = null,
		throttledTimer        = 16,
		width                 = 1,
		widthCalcModeDefault  = 'scroll',
		widthCalcMode         = widthCalcModeDefault,
		win                   = window,
		messageCallback       = function(){ warn('MessageCallback function not defined'); },
		readyCallback         = function(){},
		pageInfoCallback      = function(){},
		customCalcMethods     = {
			height: function(){
				warn('Custom height calculation function not defined');
				return document.documentElement.offsetHeight;
			}, 
			width: function(){
				warn('Custom width calculation function not defined');
				return document.body.scrollWidth;
			}
		};


	function addEventListener(el,evt,func){
		/* istanbul ignore else */ // Not testable in phantonJS
		if ('addEventListener' in window){
			el.addEventListener(evt,func, false);
		} else if ('attachEvent' in window){ //IE
			el.attachEvent('on'+evt,func);
		}
	}

	function removeEventListener(el,evt,func){
		/* istanbul ignore else */ // Not testable in phantonJS
		if ('removeEventListener' in window){
			el.removeEventListener(evt,func, false);
		} else if ('detachEvent' in window){ //IE
			el.detachEvent('on'+evt,func);
		}
	}

	function capitalizeFirstLetter(string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
	}

	//Based on underscore.js
	function throttle(func) {
		var
			context, args, result,
			timeout = null,
			previous = 0,
			later = function() {
				previous = getNow();
				timeout = null;
				result = func.apply(context, args);
				if (!timeout) {
					context = args = null;
				}
			};

		return function() {
			var now = getNow();

			if (!previous) {
				previous = now;
			}

			var remaining = throttledTimer - (now - previous);

			context = this;
			args = arguments;

			if (remaining <= 0 || remaining > throttledTimer) {
				if (timeout) {
					clearTimeout(timeout);
					timeout = null;
				}

				previous = now;
				result = func.apply(context, args);

				if (!timeout) {
					context = args = null;
				}

			} else if (!timeout) {
				timeout = setTimeout(later, remaining);
			}

			return result;
		};
	}

	var getNow = Date.now || function() {
		/* istanbul ignore next */ // Not testable in PhantonJS
		return new Date().getTime();
	};

	function formatLogMsg(msg){
		return msgID + '[' + myID + ']' + ' ' + msg;
	}

	function log(msg){
		if (logging && ('object' === typeof window.console)){
			console.log(formatLogMsg(msg));
		}
	}

	function warn(msg){
		if ('object' === typeof window.console){
			console.warn(formatLogMsg(msg));
		}
	}


	function init(){
		readDataFromParent();
		log('Initialising iFrame ('+location.href+')');
		readDataFromPage();
		setMargin();
		setBodyStyle('background',bodyBackground);
		setBodyStyle('padding',bodyPadding);
		injectClearFixIntoBodyElement();
		checkHeightMode();
		checkWidthMode();
		stopInfiniteResizingOfIFrame();
		setupPublicMethods();
		startEventListeners();
		inPageLinks = setupInPageLinks();
		sendSize('init','Init message from host page');
		readyCallback();
	}

	function readDataFromParent(){

		function strBool(str){
			return 'true' === str ? true : false;
		}

		var data = initMsg.substr(msgIdLen).split(':');

		myID               = data[0];
		bodyMargin         = (undefined !== data[1]) ? Number(data[1])   : bodyMargin; //For V1 compatibility
		calculateWidth     = (undefined !== data[2]) ? strBool(data[2])  : calculateWidth;
		logging            = (undefined !== data[3]) ? strBool(data[3])  : logging;
		interval           = (undefined !== data[4]) ? Number(data[4])   : interval;
		autoResize         = (undefined !== data[6]) ? strBool(data[6])  : autoResize;
		bodyMarginStr      = data[7];
		heightCalcMode     = (undefined !== data[8]) ? data[8]           : heightCalcMode;
		bodyBackground     = data[9];
		bodyPadding        = data[10];
		tolerance          = (undefined !== data[11]) ? Number(data[11]) : tolerance;
		inPageLinks.enable = (undefined !== data[12]) ? strBool(data[12]): false;
		resizeFrom         = (undefined !== data[13]) ? data[13]         : resizeFrom;
		widthCalcMode      = (undefined !== data[14]) ? data[14]         : widthCalcMode;
	}

	function readDataFromPage(){
		function readData(){
			var data = window.iFrameResizer;

			log('Reading data from page: ' + JSON.stringify(data));

			messageCallback     = ('messageCallback'         in data) ? data.messageCallback         : messageCallback;
			readyCallback       = ('readyCallback'           in data) ? data.readyCallback           : readyCallback;
			targetOriginDefault = ('targetOrigin'            in data) ? data.targetOrigin            : targetOriginDefault;
			heightCalcMode      = ('heightCalculationMethod' in data) ? data.heightCalculationMethod : heightCalcMode;
			widthCalcMode       = ('widthCalculationMethod'  in data) ? data.widthCalculationMethod  : widthCalcMode;
		}

		function setupCustomCalcMethods(calcMode, calcFunc){
			if ('function' === typeof calcMode) {
				log('Setup custom ' + calcFunc + 'CalcMethod');
				customCalcMethods[calcFunc] = calcMode;
				calcMode = 'custom';
			}

			return calcMode;
		}

		if(('iFrameResizer' in window) && (Object === window.iFrameResizer.constructor)) {
			readData();
			heightCalcMode = setupCustomCalcMethods(heightCalcMode, 'height');
			widthCalcMode  = setupCustomCalcMethods(widthCalcMode,  'width');
		}

		log('TargetOrigin for parent set to: ' + targetOriginDefault);
	}


	function chkCSS(attr,value){
		if (-1 !== value.indexOf('-')){
			warn('Negative CSS value ignored for '+attr);
			value='';
		}
		return value;
	}

	function setBodyStyle(attr,value){
		if ((undefined !== value) && ('' !== value) && ('null' !== value)){
			document.body.style[attr] = value;
			log('Body '+attr+' set to "'+value+'"');
		}
	}

	function setMargin(){
		//If called via V1 script, convert bodyMargin from int to str
		if (undefined === bodyMarginStr){
			bodyMarginStr = bodyMargin+'px';
		}

		setBodyStyle('margin',chkCSS('margin',bodyMarginStr));
	}

	function stopInfiniteResizingOfIFrame(){
		document.documentElement.style.height = '';
		document.body.style.height = '';
		log('HTML & body height set to "auto"');
	}


	function manageTriggerEvent(options){
		function handleEvent(){
			sendSize(options.eventName,options.eventType);
		}

		var listener = {
			add:    function(eventName){
				addEventListener(window,eventName,handleEvent);
			},
			remove: function(eventName){
				removeEventListener(window,eventName,handleEvent);
			}
		};

		if(options.eventNames && Array.prototype.map){
			options.eventName = options.eventNames[0];
			options.eventNames.map(listener[options.method]);
		} else {
			listener[options.method](options.eventName);
		}

		log(capitalizeFirstLetter(options.method) + ' event listener: ' + options.eventType);
	}

	function manageEventListeners(method){
		manageTriggerEvent({method:method, eventType: 'Animation Start',           eventNames: ['animationstart','webkitAnimationStart'] });
		manageTriggerEvent({method:method, eventType: 'Animation Iteration',       eventNames: ['animationiteration','webkitAnimationIteration'] });
		manageTriggerEvent({method:method, eventType: 'Animation End',             eventNames: ['animationend','webkitAnimationEnd'] });
		manageTriggerEvent({method:method, eventType: 'Input',                     eventName:  'input' });
		manageTriggerEvent({method:method, eventType: 'Mouse Up',                  eventName:  'mouseup' });
		manageTriggerEvent({method:method, eventType: 'Mouse Down',                eventName:  'mousedown' });
		manageTriggerEvent({method:method, eventType: 'Orientation Change',        eventName:  'orientationchange' });
		manageTriggerEvent({method:method, eventType: 'Print',                     eventName:  ['afterprint', 'beforeprint'] });
		manageTriggerEvent({method:method, eventType: 'Ready State Change',        eventName:  'readystatechange' });
		manageTriggerEvent({method:method, eventType: 'Touch Start',               eventName:  'touchstart' });
		manageTriggerEvent({method:method, eventType: 'Touch End',                 eventName:  'touchend' });
		manageTriggerEvent({method:method, eventType: 'Touch Cancel',              eventName:  'touchcancel' });
		manageTriggerEvent({method:method, eventType: 'Transition Start',          eventNames: ['transitionstart','webkitTransitionStart','MSTransitionStart','oTransitionStart','otransitionstart'] });
		manageTriggerEvent({method:method, eventType: 'Transition Iteration',      eventNames: ['transitioniteration','webkitTransitionIteration','MSTransitionIteration','oTransitionIteration','otransitioniteration'] });
		manageTriggerEvent({method:method, eventType: 'Transition End',            eventNames: ['transitionend','webkitTransitionEnd','MSTransitionEnd','oTransitionEnd','otransitionend'] });
		if('child' === resizeFrom){
			manageTriggerEvent({method:method, eventType: 'IFrame Resized',        eventName:  'resize' });
		}
	}

	function checkCalcMode(calcMode,calcModeDefault,modes,type){
		if (calcModeDefault !== calcMode){
			if (!(calcMode in modes)){
				warn(calcMode + ' is not a valid option for '+type+'CalculationMethod.');
				calcMode=calcModeDefault;
			}
			log(type+' calculation method set to "'+calcMode+'"');
		}

		return calcMode;
	}

	function checkHeightMode(){
		heightCalcMode = checkCalcMode(heightCalcMode,heightCalcModeDefault,getHeight,'height');
	}

	function checkWidthMode(){
		widthCalcMode = checkCalcMode(widthCalcMode,widthCalcModeDefault,getWidth,'width');
	}

	function startEventListeners(){
		if ( true === autoResize ) {
			manageEventListeners('add');
			setupMutationObserver();
		}
		else {
			log('Auto Resize disabled');
		}
	}

	function stopMsgsToParent(){
		log('Disable outgoing messages');
		sendPermit = false;
	}

	function removeMsgListener(){
		log('Remove event listener: Message');
		removeEventListener(window, 'message', receiver);
	}

	function disconnectMutationObserver(){
		if (null !== bodyObserver){
			/* istanbul ignore next */ // Not testable in PhantonJS
			bodyObserver.disconnect();
		}
	}

	function stopEventListeners(){
		manageEventListeners('remove');
		disconnectMutationObserver();
		clearInterval(intervalTimer);
	}

	function teardown(){
		stopMsgsToParent();
		removeMsgListener();
		if (true === autoResize) stopEventListeners();
	}

	function injectClearFixIntoBodyElement(){
		var clearFix = document.createElement('div');
		clearFix.style.clear   = 'both';
		clearFix.style.display = 'block'; //Guard against this having been globally redefined in CSS.
		document.body.appendChild(clearFix);
	}

	function setupInPageLinks(){

		function getPagePosition (){
			return {
				x: (window.pageXOffset !== undefined) ? window.pageXOffset : document.documentElement.scrollLeft,
				y: (window.pageYOffset !== undefined) ? window.pageYOffset : document.documentElement.scrollTop
			};
		}

		function getElementPosition(el){
			var
				elPosition   = el.getBoundingClientRect(),
				pagePosition = getPagePosition();

			return {
				x: parseInt(elPosition.left,10) + parseInt(pagePosition.x,10),
				y: parseInt(elPosition.top,10)  + parseInt(pagePosition.y,10)
			};
		}

		function findTarget(location){
			function jumpToTarget(target){
				var jumpPosition = getElementPosition(target);

				log('Moving to in page link (#'+hash+') at x: '+jumpPosition.x+' y: '+jumpPosition.y);
				sendMsg(jumpPosition.y, jumpPosition.x, 'scrollToOffset'); // X&Y reversed at sendMsg uses height/width
			}

			var
				hash     = location.split('#')[1] || location, //Remove # if present
				hashData = decodeURIComponent(hash),
				target   = document.getElementById(hashData) || document.getElementsByName(hashData)[0];

			if (undefined !== target){
				jumpToTarget(target);
			} else {
				log('In page link (#' + hash + ') not found in iFrame, so sending to parent');
				sendMsg(0,0,'inPageLink','#'+hash);
			}
		}

		function checkLocationHash(){
			if ('' !== location.hash && '#' !== location.hash){
				findTarget(location.href);
			}
		}

		function bindAnchors(){
			function setupLink(el){
				function linkClicked(e){
					e.preventDefault();

					/*jshint validthis:true */
					findTarget(this.getAttribute('href'));
				}

				if ('#' !== el.getAttribute('href')){
					addEventListener(el,'click',linkClicked);
				}
			}

			Array.prototype.forEach.call( document.querySelectorAll( 'a[href^="#"]' ), setupLink );
		}

		function bindLocationHash(){
			addEventListener(window,'hashchange',checkLocationHash);
		}

		function initCheck(){ //check if page loaded with location hash after init resize
			setTimeout(checkLocationHash,eventCancelTimer);
		}

		function enableInPageLinks(){
			/* istanbul ignore else */ // Not testable in phantonJS
			if(Array.prototype.forEach && document.querySelectorAll){
				log('Setting up location.hash handlers');
				bindAnchors();
				bindLocationHash();
				initCheck();
			} else {
				warn('In page linking not fully supported in this browser! (See README.md for IE8 workaround)');
			}
		}

		if(inPageLinks.enable){
			enableInPageLinks();
		} else {
			log('In page linking not enabled');
		}

		return {
			findTarget:findTarget
		};
	}

	function setupPublicMethods(){
		log('Enable public methods');

		win.parentIFrame = {

			autoResize: function autoResizeF(resize){
				if (true === resize && false === autoResize) {
					autoResize=true;
					startEventListeners();
					//sendSize('autoResize','Auto Resize enabled');
				} else if (false === resize && true === autoResize) {
					autoResize=false;
					stopEventListeners();
				}

				return autoResize;
			},

			close: function closeF(){
				sendMsg(0,0,'close');
				teardown();
			},

			getId: function getIdF(){
				return myID;
			},

			getPageInfo: function getPageInfoF(callback){
				if ('function' === typeof callback){
					pageInfoCallback = callback;
					sendMsg(0,0,'pageInfo');
				} else {
					pageInfoCallback = function(){};
					sendMsg(0,0,'pageInfoStop');
				}
			},

			moveToAnchor: function moveToAnchorF(hash){
				inPageLinks.findTarget(hash);
			},

			reset: function resetF(){
				resetIFrame('parentIFrame.reset');
			},

			scrollTo: function scrollToF(x,y){
				sendMsg(y,x,'scrollTo'); // X&Y reversed at sendMsg uses height/width
			},

			scrollToOffset: function scrollToF(x,y){
				sendMsg(y,x,'scrollToOffset'); // X&Y reversed at sendMsg uses height/width
			},

			sendMessage: function sendMessageF(msg,targetOrigin){
				sendMsg(0,0,'message',JSON.stringify(msg),targetOrigin);
			},

			setHeightCalculationMethod: function setHeightCalculationMethodF(heightCalculationMethod){
				heightCalcMode = heightCalculationMethod;
				checkHeightMode();
			},

			setWidthCalculationMethod: function setWidthCalculationMethodF(widthCalculationMethod){
				widthCalcMode = widthCalculationMethod;
				checkWidthMode();
			},

			setTargetOrigin: function setTargetOriginF(targetOrigin){
				log('Set targetOrigin: '+targetOrigin);
				targetOriginDefault = targetOrigin;
			},

			size: function sizeF(customHeight, customWidth){
				var valString = ''+(customHeight?customHeight:'')+(customWidth?','+customWidth:'');
				//lockTrigger();
				sendSize('size','parentIFrame.size('+valString+')', customHeight, customWidth);
			}
		};
	}

	function initInterval(){
		if ( 0 !== interval ){
			log('setInterval: '+interval+'ms');
			intervalTimer = setInterval(function(){
				sendSize('interval','setInterval: '+interval);
			},Math.abs(interval));
		}
	}

	/* istanbul ignore next */  //Not testable in PhantomJS
	function setupBodyMutationObserver(){
		function addImageLoadListners(mutation) {
			function addImageLoadListener(element){
				if (false === element.complete) {
					log('Attach listeners to ' + element.src);
					element.addEventListener('load', imageLoaded, false);
					element.addEventListener('error', imageError, false);
					elements.push(element);
				}
			}

			if (mutation.type === 'attributes' && mutation.attributeName === 'src'){
				addImageLoadListener(mutation.target);
			} else if (mutation.type === 'childList'){
				Array.prototype.forEach.call(
					mutation.target.querySelectorAll('img'),
					addImageLoadListener
				);
			}
		}

		function removeFromArray(element){
			elements.splice(elements.indexOf(element),1);
		}

		function removeImageLoadListener(element){
			log('Remove listeners from ' + element.src);
			element.removeEventListener('load', imageLoaded, false);
			element.removeEventListener('error', imageError, false);
			removeFromArray(element);
		}

		function imageEventTriggered(event,type,typeDesc){
			removeImageLoadListener(event.target);
			sendSize(type, typeDesc + ': ' + event.target.src, undefined, undefined);
		}

		function imageLoaded(event) {
			imageEventTriggered(event,'imageLoad','Image loaded');
		}

		function imageError(event) {
			imageEventTriggered(event,'imageLoadFailed','Image load failed');
		}

		function mutationObserved(mutations) {
			sendSize('mutationObserver','mutationObserver: ' + mutations[0].target + ' ' + mutations[0].type);

			//Deal with WebKit asyncing image loading when tags are injected into the page
			mutations.forEach(addImageLoadListners);
		}

		function createMutationObserver(){
			var
				target = document.querySelector('body'),

				config = {
					attributes            : true,
					attributeOldValue     : false,
					characterData         : true,
					characterDataOldValue : false,
					childList             : true,
					subtree               : true
				};

			observer = new MutationObserver(mutationObserved);

			log('Create body MutationObserver');
			observer.observe(target, config);

			return observer;
		}

		var
			elements         = [],
			MutationObserver = window.MutationObserver || window.WebKitMutationObserver,
			observer         = createMutationObserver();

		return {
			disconnect: function (){
				if ('disconnect' in observer){
					log('Disconnect body MutationObserver');
					observer.disconnect();
					elements.forEach(removeImageLoadListener);
				}
			}
		};
	}

	function setupMutationObserver(){
		var	forceIntervalTimer = 0 > interval;

		/* istanbul ignore if */ // Not testable in PhantomJS
		if (window.MutationObserver || window.WebKitMutationObserver){
			if (forceIntervalTimer) {
				initInterval();
			} else {
				bodyObserver = setupBodyMutationObserver();
			}
		} else {
			log('MutationObserver not supported in this browser!');
			initInterval();
		}
	}


	// document.documentElement.offsetHeight is not reliable, so
	// we have to jump through hoops to get a better value.
	function getComputedStyle(prop,el) {
		/* istanbul ignore next */  //Not testable in PhantomJS
		function convertUnitsToPxForIE8(value) {
			var PIXEL = /^\d+(px)?$/i;

			if (PIXEL.test(value)) {
				return parseInt(value,base);
			}

			var
				style = el.style.left,
				runtimeStyle = el.runtimeStyle.left;

			el.runtimeStyle.left = el.currentStyle.left;
			el.style.left = value || 0;
			value = el.style.pixelLeft;
			el.style.left = style;
			el.runtimeStyle.left = runtimeStyle;

			return value;
		}

		var retVal = 0;
		el =  el || document.body;

		/* istanbul ignore else */ // Not testable in phantonJS
		if (('defaultView' in document) && ('getComputedStyle' in document.defaultView)) {
			retVal = document.defaultView.getComputedStyle(el, null);
			retVal = (null !== retVal) ? retVal[prop] : 0;
		} else {//IE8
			retVal =  convertUnitsToPxForIE8(el.currentStyle[prop]);
		}

		return parseInt(retVal,base);
	}

	function chkEventThottle(timer){
		if(timer > throttledTimer/2){
			throttledTimer = 2*timer;
			log('Event throttle increased to ' + throttledTimer + 'ms');
		}
	}

	//Idea from https://github.com/guardian/iframe-messenger
	function getMaxElement(side,elements) {
		var
			elementsLength = elements.length,
			elVal          = 0,
			maxVal         = 0,
			Side           = capitalizeFirstLetter(side),
			timer          = getNow();

		for (var i = 0; i < elementsLength; i++) {
			elVal = elements[i].getBoundingClientRect()[side] + getComputedStyle('margin'+Side,elements[i]);
			if (elVal > maxVal) {
				maxVal = elVal;
			}
		}

		timer = getNow() - timer;

		log('Parsed '+elementsLength+' HTML elements');
		log('Element position calculated in ' + timer + 'ms');

		chkEventThottle(timer);

		return maxVal;
	}

	function getAllMeasurements(dimention){
		return [
			dimention.bodyOffset(),
			dimention.bodyScroll(),
			dimention.documentElementOffset(),
			dimention.documentElementScroll()
		];
	}

	function getTaggedElements(side,tag){
		function noTaggedElementsFound(){
			warn('No tagged elements ('+tag+') found on page');
			return height; //current height
		}

		var elements = document.querySelectorAll('['+tag+']');

		return 0 === elements.length ?  noTaggedElementsFound() : getMaxElement(side,elements);
	}

	function getAllElements(){
		return document.querySelectorAll('body *');
	}

	var
		getHeight = {
			bodyOffset: function getBodyOffsetHeight(){
				return  document.body.offsetHeight + getComputedStyle('marginTop') + getComputedStyle('marginBottom');
			},

			offset: function(){
				return getHeight.bodyOffset(); //Backwards compatability
			},

			bodyScroll: function getBodyScrollHeight(){
				return document.body.scrollHeight;
			},

			custom: function getCustomWidth(){
				return customCalcMethods.height();
			},

			documentElementOffset: function getDEOffsetHeight(){
				return document.documentElement.offsetHeight;
			},

			documentElementScroll: function getDEScrollHeight(){
				return document.documentElement.scrollHeight;
			},

			max: function getMaxHeight(){
				return Math.max.apply(null,getAllMeasurements(getHeight));
			},

			min: function getMinHeight(){
				return Math.min.apply(null,getAllMeasurements(getHeight));
			},

			grow: function growHeight(){
				return getHeight.max(); //Run max without the forced downsizing
			},

			lowestElement: function getBestHeight(){
				return Math.max(getHeight.bodyOffset(), getMaxElement('bottom',getAllElements()));
			},

			taggedElement: function getTaggedElementsHeight(){
				return getTaggedElements('bottom','data-iframe-height');
			}
		},

		getWidth = {
			bodyScroll: function getBodyScrollWidth(){
				return document.body.scrollWidth;
			},

			bodyOffset: function getBodyOffsetWidth(){
				return document.body.offsetWidth;
			},

			custom: function getCustomWidth(){
				return customCalcMethods.width();
			},

			documentElementScroll: function getDEScrollWidth(){
				return document.documentElement.scrollWidth;
			},

			documentElementOffset: function getDEOffsetWidth(){
				return document.documentElement.offsetWidth;
			},

			scroll: function getMaxWidth(){
				return Math.max(getWidth.bodyScroll(), getWidth.documentElementScroll());
			},

			max: function getMaxWidth(){
				return Math.max.apply(null,getAllMeasurements(getWidth));
			},

			min: function getMinWidth(){
				return Math.min.apply(null,getAllMeasurements(getWidth));
			},

			rightMostElement: function rightMostElement(){
				return getMaxElement('right', getAllElements());
			},

			taggedElement: function getTaggedElementsWidth(){
				return getTaggedElements('right', 'data-iframe-width');
			}
		};


	function sizeIFrame(triggerEvent, triggerEventDesc, customHeight, customWidth){

		function resizeIFrame(){
			height = currentHeight;
			width  = currentWidth;

			sendMsg(height,width,triggerEvent);
		}

		function isSizeChangeDetected(){
			function checkTolarance(a,b){
				var retVal = Math.abs(a-b) <= tolerance;
				return !retVal;
			}

			currentHeight = (undefined !== customHeight)  ? customHeight : getHeight[heightCalcMode]();
			currentWidth  = (undefined !== customWidth )  ? customWidth  : getWidth[widthCalcMode]();

			return	checkTolarance(height,currentHeight) || (calculateWidth && checkTolarance(width,currentWidth));
		}

		function isForceResizableEvent(){
			return !(triggerEvent in {'init':1,'interval':1,'size':1});
		}

		function isForceResizableCalcMode(){
			return (heightCalcMode in resetRequiredMethods) || (calculateWidth && widthCalcMode in resetRequiredMethods);
		}

		function logIgnored(){
			log('No change in size detected');
		}

		function checkDownSizing(){
			if (isForceResizableEvent() && isForceResizableCalcMode()){
				resetIFrame(triggerEventDesc);
			} else if (!(triggerEvent in {'interval':1})){
				logIgnored();
			}
		}

		var	currentHeight,currentWidth;

		if (isSizeChangeDetected() || 'init' === triggerEvent){
			lockTrigger();
			resizeIFrame();
		} else {
			checkDownSizing();
		}
	}

	var sizeIFrameThrottled = throttle(sizeIFrame);

	function sendSize(triggerEvent, triggerEventDesc, customHeight, customWidth){
		function recordTrigger(){
			if (!(triggerEvent in {'reset':1,'resetPage':1,'init':1})){
				log( 'Trigger event: ' + triggerEventDesc );
			}
		}

		function isDoubleFiredEvent(){
			return  triggerLocked && (triggerEvent in doubleEventList);
		}

		if (!isDoubleFiredEvent()){
			recordTrigger();
			sizeIFrameThrottled(triggerEvent, triggerEventDesc, customHeight, customWidth);
		} else {
			log('Trigger event cancelled: '+triggerEvent);
		}
	}

	function lockTrigger(){
		if (!triggerLocked){
			triggerLocked = true;
			log('Trigger event lock on');
		}
		clearTimeout(triggerLockedTimer);
		triggerLockedTimer = setTimeout(function(){
			triggerLocked = false;
			log('Trigger event lock off');
			log('--');
		},eventCancelTimer);
	}

	function triggerReset(triggerEvent){
		height = getHeight[heightCalcMode]();
		width  = getWidth[widthCalcMode]();

		sendMsg(height,width,triggerEvent);
	}

	function resetIFrame(triggerEventDesc){
		var hcm = heightCalcMode;
		heightCalcMode = heightCalcModeDefault;

		log('Reset trigger event: ' + triggerEventDesc);
		lockTrigger();
		triggerReset('reset');

		heightCalcMode = hcm;
	}

	function sendMsg(height,width,triggerEvent,msg,targetOrigin){
		function setTargetOrigin(){
			if (undefined === targetOrigin){
				targetOrigin = targetOriginDefault;
			} else {
				log('Message targetOrigin: '+targetOrigin);
			}
		}

		function sendToParent(){
			var
				size  = height + ':' + width,
				message = myID + ':' +  size + ':' + triggerEvent + (undefined !== msg ? ':' + msg : '');

			log('Sending message to host page (' + message + ')');
			target.postMessage( msgID + message, targetOrigin);
		}

		if(true === sendPermit){
			setTargetOrigin();
			sendToParent();
		}
	}

	function receiver(event) {
		function isMessageForUs(){
			return msgID === (''+event.data).substr(0,msgIdLen); //''+ Protects against non-string messages
		}

		function initFromParent(){
			function fireInit(){
				initMsg = event.data;
				target  = event.source;

				init();
				firstRun = false;
				setTimeout(function(){ initLock = false;},eventCancelTimer);
			}

			if (document.body){
				fireInit();
			} else {
				log('Waiting for page ready');
				addEventListener(window,'readystatechange',initFromParent);
			}
		}

		function resetFromParent(){
			if (!initLock){
				log('Page size reset by host page');
				triggerReset('resetPage');
			} else {
				log('Page reset ignored by init');
			}
		}

		function resizeFromParent(){
			sendSize('resizeParent','Parent window requested size check');
		}

		function moveToAnchor(){
			var anchor = getData();
			inPageLinks.findTarget(anchor);
		}

		function getMessageType(){
			return event.data.split(']')[1].split(':')[0];
		}

		function getData(){
			return event.data.substr(event.data.indexOf(':')+1);
		}

		function isMiddleTier(){
			return ('iFrameResize' in window);
		}

		function messageFromParent(){
			var msgBody = getData();

			log('MessageCallback called from parent: ' + msgBody );
			messageCallback(JSON.parse(msgBody));
			log(' --');
		}

		function pageInfoFromParent(){
			var msgBody = getData();
			log('PageInfoFromParent called from parent: ' + msgBody );
			pageInfoCallback(JSON.parse(msgBody));
			log(' --');
		}

		function isInitMsg(){
			//Test if this message is from a child below us. This is an ugly test, however, updating
			//the message format would break backwards compatibity.
			return event.data.split(':')[2] in {'true':1,'false':1};
		}

		function callFromParent(){
			switch (getMessageType()){
			case 'reset':
				resetFromParent();
				break;
			case 'resize':
				resizeFromParent();
				break;
			case 'inPageLink':
			case 'moveToAnchor':
				moveToAnchor();
				break;
			case 'message':
				messageFromParent();
				break;
			case 'pageInfo':
				pageInfoFromParent();
				break;
			default:
				if (!isMiddleTier() && !isInitMsg()){
					warn('Unexpected message ('+event.data+')');
				}
			}
		}

		function processMessage(){
			if (false === firstRun) {
				callFromParent();
			} else if (isInitMsg()) {
				initFromParent();
			} else {
				log('Ignored message of type "' + getMessageType() + '". Received before initialization.');
			}
		}

		if (isMessageForUs()){
			processMessage();
		}
	}

	//Normally the parent kicks things off when it detects the iFrame has loaded.
	//If this script is async-loaded, then tell parent page to retry init.
	function chkLateLoaded(){
		if('loading' !== document.readyState){
			window.parent.postMessage('[iFrameResizerChild]Ready','*');
		}
	}

	addEventListener(window, 'message', receiver);
	chkLateLoaded();

	

})(window || {});


/***/ }),

/***/ "a5b03ec781f71e8d0bed":
/***/ (function(module, exports, __webpack_require__) {

var classof = __webpack_require__("1174b5b657e5fc33c6e4");
var ITERATOR = __webpack_require__("79f39f38b3e53642baf4")('iterator');
var Iterators = __webpack_require__("ed94c6ffbb2bc6323c97");
module.exports = __webpack_require__("99ec8b6ce9e8b820539a").getIteratorMethod = function (it) {
  if (it != undefined) return it[ITERATOR]
    || it['@@iterator']
    || Iterators[classof(it)];
};


/***/ }),

/***/ "adc6e7d88332518aa1ec":
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__("ce462d99a45ae2207993"), __esModule: true };

/***/ }),

/***/ "b24a8b38059690cfe907":
/***/ (function(module, exports, __webpack_require__) {

var document = __webpack_require__("f27a607058b9cc8c5827").document;
module.exports = document && document.documentElement;


/***/ }),

/***/ "b96d3c311cc0f6a5337c":
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.9 / 15.2.3.2 Object.getPrototypeOf(O)
var has = __webpack_require__("d1c7bd6e35e95c182000");
var toObject = __webpack_require__("68be1979658d3a49fbce");
var IE_PROTO = __webpack_require__("6b7c061c8481ac422c8d")('IE_PROTO');
var ObjectProto = Object.prototype;

module.exports = Object.getPrototypeOf || function (O) {
  O = toObject(O);
  if (has(O, IE_PROTO)) return O[IE_PROTO];
  if (typeof O.constructor == 'function' && O instanceof O.constructor) {
    return O.constructor.prototype;
  } return O instanceof Object ? ObjectProto : null;
};


/***/ }),

/***/ "bb82a2b96f436781c23d":
/***/ (function(module, exports, __webpack_require__) {

module.exports = !__webpack_require__("07a3448be5f694cdee04") && !__webpack_require__("457939c2e15065e32401")(function () {
  return Object.defineProperty(__webpack_require__("1b58d36ee38e47f1c490")('div'), 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),

/***/ "bc1908f480b427a8a004":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("2becabd90e65528fbb02");
var $Object = __webpack_require__("99ec8b6ce9e8b820539a").Object;
module.exports = function defineProperty(it, key, desc) {
  return $Object.defineProperty(it, key, desc);
};


/***/ }),

/***/ "bdf846462d003d52f301":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("ef9f5790dc2f994efcb7");
var global = __webpack_require__("f27a607058b9cc8c5827");
var hide = __webpack_require__("f9e595817d8b54ee32b8");
var Iterators = __webpack_require__("ed94c6ffbb2bc6323c97");
var TO_STRING_TAG = __webpack_require__("79f39f38b3e53642baf4")('toStringTag');

var DOMIterables = ('CSSRuleList,CSSStyleDeclaration,CSSValueList,ClientRectList,DOMRectList,DOMStringList,' +
  'DOMTokenList,DataTransferItemList,FileList,HTMLAllCollection,HTMLCollection,HTMLFormElement,HTMLSelectElement,' +
  'MediaList,MimeTypeArray,NamedNodeMap,NodeList,PaintRequestList,Plugin,PluginArray,SVGLengthList,SVGNumberList,' +
  'SVGPathSegList,SVGPointList,SVGStringList,SVGTransformList,SourceBufferList,StyleSheetList,TextTrackCueList,' +
  'TextTrackList,TouchList').split(',');

for (var i = 0; i < DOMIterables.length; i++) {
  var NAME = DOMIterables[i];
  var Collection = global[NAME];
  var proto = Collection && Collection.prototype;
  if (proto && !proto[TO_STRING_TAG]) hide(proto, TO_STRING_TAG, NAME);
  Iterators[NAME] = Iterators.Array;
}


/***/ }),

/***/ "be5ff68e5fc2b36ab3fd":
/***/ (function(module, exports, __webpack_require__) {

// call something on iterator step with safe closing on error
var anObject = __webpack_require__("6c3dda553b2c45fd2161");
module.exports = function (iterator, fn, value, entries) {
  try {
    return entries ? fn(anObject(value)[0], value[1]) : fn(value);
  // 7.4.6 IteratorClose(iterator, completion)
  } catch (e) {
    var ret = iterator['return'];
    if (ret !== undefined) anObject(ret.call(iterator));
    throw e;
  }
};


/***/ }),

/***/ "c198a9131d44657c0b4b":
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__("6c3dda553b2c45fd2161");
var isObject = __webpack_require__("f31cab22762bc6afaacb");
var newPromiseCapability = __webpack_require__("a415db3a52e4fe9edb5f");

module.exports = function (C, x) {
  anObject(C);
  if (isObject(x) && x.constructor === C) return x;
  var promiseCapability = newPromiseCapability.f(C);
  var resolve = promiseCapability.resolve;
  resolve(x);
  return promiseCapability.promise;
};


/***/ }),

/***/ "c4bfa6afe83d45b63fe7":
/***/ (function(module, exports) {

module.exports = function () { /* empty */ };


/***/ }),

/***/ "c4f9ccf6d4f9090cfe1b":
/***/ (function(module, exports) {



/***/ }),

/***/ "c9a111f30e434d88d80d":
/***/ (function(module, exports, __webpack_require__) {

"use strict";
// https://github.com/tc39/proposal-promise-finally

var $export = __webpack_require__("d30516674aade65150d3");
var core = __webpack_require__("99ec8b6ce9e8b820539a");
var global = __webpack_require__("f27a607058b9cc8c5827");
var speciesConstructor = __webpack_require__("83b597dad746a364852b");
var promiseResolve = __webpack_require__("c198a9131d44657c0b4b");

$export($export.P + $export.R, 'Promise', { 'finally': function (onFinally) {
  var C = speciesConstructor(this, core.Promise || global.Promise);
  var isFunction = typeof onFinally == 'function';
  return this.then(
    isFunction ? function (x) {
      return promiseResolve(C, onFinally()).then(function () { return x; });
    } : onFinally,
    isFunction ? function (e) {
      return promiseResolve(C, onFinally()).then(function () { throw e; });
    } : onFinally
  );
} });


/***/ }),

/***/ "cb0e5c13ab671db35a76":
/***/ (function(module, exports) {

module.exports = function (done, value) {
  return { value: value, done: !!done };
};


/***/ }),

/***/ "cb274a31409742acdaf8":
/***/ (function(module, exports, __webpack_require__) {

var def = __webpack_require__("30373b8543ac2ca8f199").f;
var has = __webpack_require__("d1c7bd6e35e95c182000");
var TAG = __webpack_require__("79f39f38b3e53642baf4")('toStringTag');

module.exports = function (it, tag, stat) {
  if (it && !has(it = stat ? it : it.prototype, TAG)) def(it, TAG, { configurable: true, value: tag });
};


/***/ }),

/***/ "ce462d99a45ae2207993":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("0b7e406830a62ea861ca");
module.exports = __webpack_require__("99ec8b6ce9e8b820539a").Object.assign;


/***/ }),

/***/ "d137ba7e6a11667e9035":
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.14 / 15.2.3.14 Object.keys(O)
var $keys = __webpack_require__("dfa76e0e7e55e0febf1e");
var enumBugKeys = __webpack_require__("25264dbf4ec8cd0a39ef");

module.exports = Object.keys || function keys(O) {
  return $keys(O, enumBugKeys);
};


/***/ }),

/***/ "d1c7bd6e35e95c182000":
/***/ (function(module, exports) {

var hasOwnProperty = {}.hasOwnProperty;
module.exports = function (it, key) {
  return hasOwnProperty.call(it, key);
};


/***/ }),

/***/ "d1e7c9d71cb07a1681eb":
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__("f27a607058b9cc8c5827");
var navigator = global.navigator;

module.exports = navigator && navigator.userAgent || '';


/***/ }),

/***/ "d2592be7ddc193d42ea4":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _assign = __webpack_require__("adc6e7d88332518aa1ec");

var _assign2 = _interopRequireDefault(_assign);

var _regenerator = __webpack_require__("db849ac7954a482c13e7");

var _regenerator2 = _interopRequireDefault(_regenerator);

var _promise = __webpack_require__("98e8346a38f021750136");

var _promise2 = _interopRequireDefault(_promise);

var _getIterator2 = __webpack_require__("2ae529d75fd1f719d626");

var _getIterator3 = _interopRequireDefault(_getIterator2);

var _asyncToGenerator2 = __webpack_require__("03b43af0ee3f8b9a2130");

var _asyncToGenerator3 = _interopRequireDefault(_asyncToGenerator2);

var _classCallCheck2 = __webpack_require__("7ab4a89ebadbfdecc2bf");

var _classCallCheck3 = _interopRequireDefault(_classCallCheck2);

var _createClass2 = __webpack_require__("4602c3f5fe7ad9e3e91d");

var _createClass3 = _interopRequireDefault(_createClass2);

var _toConsumableArray2 = __webpack_require__("7a079e57c8ffba47bbed");

var _toConsumableArray3 = _interopRequireDefault(_toConsumableArray2);

var _jayPostMessage = __webpack_require__("1c9181382673925a0296");

var _jayPostMessage2 = _interopRequireDefault(_jayPostMessage);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var resources = __webpack_require__("45e4aa9de557cd674741");
__webpack_require__("ef8ff13f7a123fc8d965");
var apiList = [].concat((0, _toConsumableArray3.default)(__webpack_require__("a15eafa3f37bdba875c3").apiList));

var LtcSDKClient = function () {
  function LtcSDKClient() {
    (0, _classCallCheck3.default)(this, LtcSDKClient);

    this.options = {};
    this.messenger = new _jayPostMessage2.default({
      name: self.frameElement.getAttribute('id'),
      project: 'LtcProject',
      children: [],
      type: 'child'
    });
    this.loadResource = [];
  }

  (0, _createClass3.default)(LtcSDKClient, [{
    key: 'createScript',
    value: function createScript(value, resolve, reject) {
      var _this = this;

      var script = document.createElement('script');
      script.src = this.resource[value];
      script.addEventListener('load', function () {
        if (resources[value]) {
          resources[value]();
        }
        resolve(value);
        _this.loadResource[value] = true;
      }, false);

      script.addEventListener('error', function () {
        reject(value);
      }, false);

      document.body.appendChild(script);
    }
  }, {
    key: 'createCss',
    value: function createCss(value, resolve) {
      var link = document.createElement('link');
      link.type = 'text/css';
      link.rel = 'stylesheet';
      link.href = this.resource[value];
      var head = document.getElementsByTagName('head')[0];
      head.appendChild(link);

      resolve();
    }
  }, {
    key: 'load',
    value: function () {
      var _ref = (0, _asyncToGenerator3.default)( /*#__PURE__*/_regenerator2.default.mark(function _callee() {
        var _this2 = this;

        for (var _len = arguments.length, urls = Array(_len), _key = 0; _key < _len; _key++) {
          urls[_key] = arguments[_key];
        }

        var self, _iteratorNormalCompletion, _didIteratorError, _iteratorError, _loop, _iterator, _step;

        return _regenerator2.default.wrap(function _callee$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                self = this;
                _context2.next = 3;
                return self._init();

              case 3:
                _iteratorNormalCompletion = true;
                _didIteratorError = false;
                _iteratorError = undefined;
                _context2.prev = 6;
                _loop = /*#__PURE__*/_regenerator2.default.mark(function _loop() {
                  var value;
                  return _regenerator2.default.wrap(function _loop$(_context) {
                    while (1) {
                      switch (_context.prev = _context.next) {
                        case 0:
                          value = _step.value;
                          _context.next = 3;
                          return new _promise2.default(function (resolve, reject) {
                            if (self.loadResource[value]) {
                              resolve(value);
                            }
                            value.indexOf('.css') >= 0 ? self.createCss(value, resolve) : self.createScript(value, resolve, reject);
                          });

                        case 3:
                        case 'end':
                          return _context.stop();
                      }
                    }
                  }, _loop, _this2);
                });
                _iterator = (0, _getIterator3.default)(urls);

              case 9:
                if (_iteratorNormalCompletion = (_step = _iterator.next()).done) {
                  _context2.next = 14;
                  break;
                }

                return _context2.delegateYield(_loop(), 't0', 11);

              case 11:
                _iteratorNormalCompletion = true;
                _context2.next = 9;
                break;

              case 14:
                _context2.next = 20;
                break;

              case 16:
                _context2.prev = 16;
                _context2.t1 = _context2['catch'](6);
                _didIteratorError = true;
                _iteratorError = _context2.t1;

              case 20:
                _context2.prev = 20;
                _context2.prev = 21;

                if (!_iteratorNormalCompletion && _iterator.return) {
                  _iterator.return();
                }

              case 23:
                _context2.prev = 23;

                if (!_didIteratorError) {
                  _context2.next = 26;
                  break;
                }

                throw _iteratorError;

              case 26:
                return _context2.finish(23);

              case 27:
                return _context2.finish(20);

              case 28:
              case 'end':
                return _context2.stop();
            }
          }
        }, _callee, this, [[6, 16, 20, 28], [21,, 23, 27]]);
      }));

      function load() {
        return _ref.apply(this, arguments);
      }

      return load;
    }()
  }, {
    key: 'getContext',
    value: function getContext() {
      return this.serverData['context'];
    }
  }, {
    key: 'getEditorConfig',
    value: function getEditorConfig() {
      return this.serverData['editorConfig'];
    }
  }, {
    key: 'getFormSerializeObject',
    value: function getFormSerializeObject($e) {
      var o = {};
      var a = $e.serializeArray();
      $.each(a, function () {
        if (o[this.name]) {
          if (!o[this.name].push) {
            o[this.name] = [o[this.name]];
          }
          o[this.name].push(this.value || '');
        } else {
          o[this.name] = this.value || '';
        }
      });

      return o;
    }
  }, {
    key: 'once',
    value: function once(eventName) {
      var args = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      this.messenger.once(eventName, args);
    }
  }, {
    key: 'on',
    value: function on(eventName) {
      var args = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      this.messenger.on(eventName, args);
    }
  }, {
    key: 'emit',
    value: function emit(eventName) {
      var args = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      args = (0, _assign2.default)({
        iframeId: self.frameElement.getAttribute('id')
      }, args);
      this.messenger.sendToParent(eventName, args);
    }
  }, {
    key: 'api',
    value: function api(options, callback) {

      if (apiList.indexOf(options.name) === -1) {
        return false;
      }

      var uuid = this._getUuid();

      this.emit('getApi', (0, _assign2.default)(options, { uuid: uuid }));
      this.once('returnApi_' + uuid, function (results) {
        callback(results);
      });
    }
  }, {
    key: '_init',
    value: function () {
      var _ref2 = (0, _asyncToGenerator3.default)( /*#__PURE__*/_regenerator2.default.mark(function _callee2() {
        var self;
        return _regenerator2.default.wrap(function _callee2$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                self = this;
                return _context3.abrupt('return', new _promise2.default(function (resolve, reject) {
                  if (self.serverData) {
                    resolve();
                  }

                  self.messenger.sendToParent('init');
                  self.messenger.once('initResourceList', function (data) {
                    self.serverData = data;
                    self.resource = data['resource'];
                    document.documentElement.lang = data['context']['lang'];
                    resolve();
                  });
                }));

              case 2:
              case 'end':
                return _context3.stop();
            }
          }
        }, _callee2, this);
      }));

      function _init() {
        return _ref2.apply(this, arguments);
      }

      return _init;
    }()
  }, {
    key: '_s4',
    value: function _s4() {
      return ((1 + Math.random()) * 0x10000 | 0).toString(16).substring(1);
    }
  }, {
    key: '_getUuid',
    value: function _getUuid() {
      return this._s4() + this._s4() + "-" + this._s4() + "-" + this._s4() + "-" + this._s4() + "-" + this._s4() + this._s4() + this._s4();
    }
  }]);
  return LtcSDKClient;
}();

module.exports = window.ltc = new LtcSDKClient();

/***/ }),

/***/ "d30516674aade65150d3":
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__("f27a607058b9cc8c5827");
var core = __webpack_require__("99ec8b6ce9e8b820539a");
var ctx = __webpack_require__("a0ce7130a92293994282");
var hide = __webpack_require__("f9e595817d8b54ee32b8");
var has = __webpack_require__("d1c7bd6e35e95c182000");
var PROTOTYPE = 'prototype';

var $export = function (type, name, source) {
  var IS_FORCED = type & $export.F;
  var IS_GLOBAL = type & $export.G;
  var IS_STATIC = type & $export.S;
  var IS_PROTO = type & $export.P;
  var IS_BIND = type & $export.B;
  var IS_WRAP = type & $export.W;
  var exports = IS_GLOBAL ? core : core[name] || (core[name] = {});
  var expProto = exports[PROTOTYPE];
  var target = IS_GLOBAL ? global : IS_STATIC ? global[name] : (global[name] || {})[PROTOTYPE];
  var key, own, out;
  if (IS_GLOBAL) source = name;
  for (key in source) {
    // contains in native
    own = !IS_FORCED && target && target[key] !== undefined;
    if (own && has(exports, key)) continue;
    // export native or passed
    out = own ? target[key] : source[key];
    // prevent global pollution for namespaces
    exports[key] = IS_GLOBAL && typeof target[key] != 'function' ? source[key]
    // bind timers to global for call from export context
    : IS_BIND && own ? ctx(out, global)
    // wrap global constructors for prevent change them in library
    : IS_WRAP && target[key] == out ? (function (C) {
      var F = function (a, b, c) {
        if (this instanceof C) {
          switch (arguments.length) {
            case 0: return new C();
            case 1: return new C(a);
            case 2: return new C(a, b);
          } return new C(a, b, c);
        } return C.apply(this, arguments);
      };
      F[PROTOTYPE] = C[PROTOTYPE];
      return F;
    // make static versions for prototype methods
    })(out) : IS_PROTO && typeof out == 'function' ? ctx(Function.call, out) : out;
    // export proto methods to core.%CONSTRUCTOR%.methods.%NAME%
    if (IS_PROTO) {
      (exports.virtual || (exports.virtual = {}))[key] = out;
      // export proto methods to core.%CONSTRUCTOR%.prototype.%NAME%
      if (type & $export.R && expProto && !expProto[key]) hide(expProto, key, out);
    }
  }
};
// type bitmap
$export.F = 1;   // forced
$export.G = 2;   // global
$export.S = 4;   // static
$export.P = 8;   // proto
$export.B = 16;  // bind
$export.W = 32;  // wrap
$export.U = 64;  // safe
$export.R = 128; // real proto method for `library`
module.exports = $export;


/***/ }),

/***/ "d582d148ae5db37e805e":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

// https://github.com/tc39/proposal-promise-try
var $export = __webpack_require__("d30516674aade65150d3");
var newPromiseCapability = __webpack_require__("a415db3a52e4fe9edb5f");
var perform = __webpack_require__("5640212163de424af892");

$export($export.S, 'Promise', { 'try': function (callbackfn) {
  var promiseCapability = newPromiseCapability.f(this);
  var result = perform(callbackfn);
  (result.e ? promiseCapability.reject : promiseCapability.resolve)(result.v);
  return promiseCapability.promise;
} });


/***/ }),

/***/ "d6e9c43d4248c3dfedec":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var create = __webpack_require__("14126b102a8a946146ae");
var descriptor = __webpack_require__("4373c1ac6d15a5b2dd1f");
var setToStringTag = __webpack_require__("cb274a31409742acdaf8");
var IteratorPrototype = {};

// 25.1.2.1.1 %IteratorPrototype%[@@iterator]()
__webpack_require__("f9e595817d8b54ee32b8")(IteratorPrototype, __webpack_require__("79f39f38b3e53642baf4")('iterator'), function () { return this; });

module.exports = function (Constructor, NAME, next) {
  Constructor.prototype = create(IteratorPrototype, { next: descriptor(1, next) });
  setToStringTag(Constructor, NAME + ' Iterator');
};


/***/ }),

/***/ "db849ac7954a482c13e7":
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__("18266a2c56780b8d3afe");


/***/ }),

/***/ "dfa37eb698c113f70efb":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var ctx = __webpack_require__("a0ce7130a92293994282");
var $export = __webpack_require__("d30516674aade65150d3");
var toObject = __webpack_require__("68be1979658d3a49fbce");
var call = __webpack_require__("be5ff68e5fc2b36ab3fd");
var isArrayIter = __webpack_require__("7bbcd029dba3160182ef");
var toLength = __webpack_require__("10f5bc3e6ad5971b57b0");
var createProperty = __webpack_require__("5a1b42cd4752706663d8");
var getIterFn = __webpack_require__("a5b03ec781f71e8d0bed");

$export($export.S + $export.F * !__webpack_require__("9192d99c20a30ab204d7")(function (iter) { Array.from(iter); }), 'Array', {
  // 22.1.2.1 Array.from(arrayLike, mapfn = undefined, thisArg = undefined)
  from: function from(arrayLike /* , mapfn = undefined, thisArg = undefined */) {
    var O = toObject(arrayLike);
    var C = typeof this == 'function' ? this : Array;
    var aLen = arguments.length;
    var mapfn = aLen > 1 ? arguments[1] : undefined;
    var mapping = mapfn !== undefined;
    var index = 0;
    var iterFn = getIterFn(O);
    var length, result, step, iterator;
    if (mapping) mapfn = ctx(mapfn, aLen > 2 ? arguments[2] : undefined, 2);
    // if object isn't iterable or it's array with default iterator - use simple case
    if (iterFn != undefined && !(C == Array && isArrayIter(iterFn))) {
      for (iterator = iterFn.call(O), result = new C(); !(step = iterator.next()).done; index++) {
        createProperty(result, index, mapping ? call(iterator, mapfn, [step.value, index], true) : step.value);
      }
    } else {
      length = toLength(O.length);
      for (result = new C(length); length > index; index++) {
        createProperty(result, index, mapping ? mapfn(O[index], index) : O[index]);
      }
    }
    result.length = index;
    return result;
  }
});


/***/ }),

/***/ "dfa76e0e7e55e0febf1e":
/***/ (function(module, exports, __webpack_require__) {

var has = __webpack_require__("d1c7bd6e35e95c182000");
var toIObject = __webpack_require__("9e0642ad0826b9d4cd08");
var arrayIndexOf = __webpack_require__("68a72f4f57d2a9459710")(false);
var IE_PROTO = __webpack_require__("6b7c061c8481ac422c8d")('IE_PROTO');

module.exports = function (object, names) {
  var O = toIObject(object);
  var i = 0;
  var result = [];
  var key;
  for (key in O) if (key != IE_PROTO) has(O, key) && result.push(key);
  // Don't enum bug & hidden keys
  while (names.length > i) if (has(O, key = names[i++])) {
    ~arrayIndexOf(result, key) || result.push(key);
  }
  return result;
};


/***/ }),

/***/ "dff9362b82bd4eafdef8":
/***/ (function(module, exports) {

module.exports = true;


/***/ }),

/***/ "e2544e9d164d5ae98195":
/***/ (function(module, exports, __webpack_require__) {

var toInteger = __webpack_require__("26b2cb950fcc43474957");
var max = Math.max;
var min = Math.min;
module.exports = function (index, length) {
  index = toInteger(index);
  return index < 0 ? max(index + length, 0) : min(index, length);
};


/***/ }),

/***/ "e7ceb28b20870340ea1d":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("bdf846462d003d52f301");
__webpack_require__("9f5e733705cd16698cdf");
module.exports = __webpack_require__("f320218143ef18cc600c");


/***/ }),

/***/ "e82b6e3d752b3eb55e42":
/***/ (function(module, exports) {

module.exports = function (it) {
  if (typeof it != 'function') throw TypeError(it + ' is not a function!');
  return it;
};


/***/ }),

/***/ "ed94c6ffbb2bc6323c97":
/***/ (function(module, exports) {

module.exports = {};


/***/ }),

/***/ "ef8ff13f7a123fc8d965":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__("a50cfb5e0ca3918fbabe");

/***/ }),

/***/ "ef9f5790dc2f994efcb7":
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var addToUnscopables = __webpack_require__("c4bfa6afe83d45b63fe7");
var step = __webpack_require__("cb0e5c13ab671db35a76");
var Iterators = __webpack_require__("ed94c6ffbb2bc6323c97");
var toIObject = __webpack_require__("9e0642ad0826b9d4cd08");

// 22.1.3.4 Array.prototype.entries()
// 22.1.3.13 Array.prototype.keys()
// 22.1.3.29 Array.prototype.values()
// 22.1.3.30 Array.prototype[@@iterator]()
module.exports = __webpack_require__("44cc46f02a04db587394")(Array, 'Array', function (iterated, kind) {
  this._t = toIObject(iterated); // target
  this._i = 0;                   // next index
  this._k = kind;                // kind
// 22.1.5.2.1 %ArrayIteratorPrototype%.next()
}, function () {
  var O = this._t;
  var kind = this._k;
  var index = this._i++;
  if (!O || index >= O.length) {
    this._t = undefined;
    return step(1);
  }
  if (kind == 'keys') return step(0, index);
  if (kind == 'values') return step(0, O[index]);
  return step(0, [index, O[index]]);
}, 'values');

// argumentsList[@@iterator] is %ArrayProto_values% (9.4.4.6, 9.4.4.7)
Iterators.Arguments = Iterators.Array;

addToUnscopables('keys');
addToUnscopables('values');
addToUnscopables('entries');


/***/ }),

/***/ "f27a607058b9cc8c5827":
/***/ (function(module, exports) {

// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
var global = module.exports = typeof window != 'undefined' && window.Math == Math
  ? window : typeof self != 'undefined' && self.Math == Math ? self
  // eslint-disable-next-line no-new-func
  : Function('return this')();
if (typeof __g == 'number') __g = global; // eslint-disable-line no-undef


/***/ }),

/***/ "f31cab22762bc6afaacb":
/***/ (function(module, exports) {

module.exports = function (it) {
  return typeof it === 'object' ? it !== null : typeof it === 'function';
};


/***/ }),

/***/ "f320218143ef18cc600c":
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__("6c3dda553b2c45fd2161");
var get = __webpack_require__("a5b03ec781f71e8d0bed");
module.exports = __webpack_require__("99ec8b6ce9e8b820539a").getIterator = function (it) {
  var iterFn = get(it);
  if (typeof iterFn != 'function') throw TypeError(it + ' is not iterable!');
  return anObject(iterFn.call(it));
};


/***/ }),

/***/ "f9e595817d8b54ee32b8":
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__("30373b8543ac2ca8f199");
var createDesc = __webpack_require__("4373c1ac6d15a5b2dd1f");
module.exports = __webpack_require__("07a3448be5f694cdee04") ? function (object, key, value) {
  return dP.f(object, key, createDesc(1, value));
} : function (object, key, value) {
  object[key] = value;
  return object;
};


/***/ })

/******/ });