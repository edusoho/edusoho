webpackJsonp(["app/js/search/cloud/index"],{

/***/ "287a080dacda2766cf12":
/***/ (function(module, exports) {

	"use strict";
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CloudSearch = function () {
	  function CloudSearch(options) {
	    _classCallCheck(this, CloudSearch);
	
	    this.$element = $(options.element);
	    this.init();
	  }
	
	  _createClass(CloudSearch, [{
	    key: "init",
	    value: function init() {
	      if (this.$element.find("#search-input-group .form-control").val()) {
	        this.$element.find(".js-btn-clear").show();
	      }
	      echo.init();
	      this.initEvent();
	    }
	  }, {
	    key: "initEvent",
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '.js-btn-clear', function (event) {
	        return _this.onBtnClear(event);
	      });
	      this.$element.on('input propertychange', '#search-input-group .form-control', function (event) {
	        return _this.onSearchInput(event);
	      });
	    }
	  }, {
	    key: "onBtnClear",
	    value: function onBtnClear(event) {
	      var $this = $(event.currentTarget);
	      $this.siblings('input').val('').end().hide();
	    }
	  }, {
	    key: "onSearchInput",
	    value: function onSearchInput(event) {
	      var $this = $(event.currentTarget);
	      var btnClear = $this.siblings('.js-btn-clear');
	
	      if ($this.val()) {
	        btnClear.show();
	      } else {
	        btnClear.hide();
	      }
	    }
	  }]);
	
	  return CloudSearch;
	}();
	
	exports["default"] = CloudSearch;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _cloudSearch = __webpack_require__("287a080dacda2766cf12");
	
	var _cloudSearch2 = _interopRequireDefault(_cloudSearch);
	
	__webpack_require__("7840d638cc48059df0fc");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _cloudSearch2["default"]({
	  element: 'body'
	});

/***/ }),

/***/ "7840d638cc48059df0fc":
/***/ (function(module, exports) {

	'use strict';
	
	$('body').on('click', '.teacher-item .follow-btn', function () {
	  var $btn = $(this);
	
	  $.post($btn.data('url'), function () {
	    var loggedin = $btn.data('loggedin');
	
	    if (loggedin === 1) {
	      $btn.hide();
	      $btn.closest('.teacher-item').find('.unfollow-btn').show();
	    }
	  });
	}).on('click', '.unfollow-btn', function () {
	  var $btn = $(this);
	
	  $.post($btn.data('url'), function () {}).always(function () {
	    $btn.hide();
	    $btn.closest('.teacher-item').find('.follow-btn').show();
	  });
	});

/***/ })

});
//# sourceMappingURL=index.js.map