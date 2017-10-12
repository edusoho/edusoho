webpackJsonp(["app/js/es-bar/index"],{

/***/ "7150e3e195d39d1d2f69":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var EsBar = function () {
	  function EsBar(prop) {
	    _classCallCheck(this, EsBar);
	
	    this.ele = $(prop.ele);
	    this.init();
	  }
	
	  _createClass(EsBar, [{
	    key: 'init',
	    value: function init() {
	      var _this = this;
	
	      this.initEvent();
	
	      if (_utils.Browser.ie10 || _utils.Browser.ie11 || _utils.Browser.edge) {
	        this.ele.css("margin-right", '16px');
	      }
	
	      if (this.ele.find('[data-toggle="tooltip"]').length > 0) {
	        this.ele.find('[data-toggle="tooltip"]').tooltip({ container: '.es-bar' });
	      }
	
	      this.ele.find(".bar-menu-sns li.popover-btn").popover({
	        placement: 'left',
	        trigger: 'hover',
	        html: true,
	        content: function content() {
	          return $($(this).data('contentElement')).html();
	        }
	      });
	
	      $("body").on('click', '.es-wrap', function () {
	        if ($(".es-bar-main.active").length) {
	          _this.ele.animate({
	            right: '-230px'
	          }, 300).find(".bar-menu-top li.active").removeClass('active');
	        }
	      });
	
	      this.goTop();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this2 = this;
	
	      var $node = this.ele;
	      $node.on('click', '.js-bar-shrink', function (event) {
	        return _this2.onBarBhrink(event);
	      });
	      $node.on('click', '.bar-menu-top li', function (event) {
	        return _this2.onMenuTop(event);
	      });
	      $node.on('click', '.btn-action >a', function (event) {
	        return _this2.onBtnAction(event);
	      });
	    }
	  }, {
	    key: 'onBarBhrink',
	    value: function onBarBhrink(e) {
	      var $this = $(e.currentTarget);
	      $this.parents(".es-bar-main.active").removeClass('active').end().parents(".es-bar").animate({
	        right: '-230px'
	      }, 300);
	      $(".bar-menu-top li.active").removeClass('active');
	    }
	  }, {
	    key: 'onMenuTop',
	    value: function onMenuTop(e) {
	      var $this = $(e.currentTarget);
	
	      // 判断是否登录
	      if (!_utils.isLogin) {
	        this.isNotLogin();
	        return;
	      }
	
	      this.ele.find(".bar-main-body").perfectScrollbar({ wheelSpeed: 50 });
	
	      if ($this.find(".dot")) {
	        $this.find(".dot").remove();
	      }
	
	      if (!$this.hasClass('active')) {
	        $this.siblings(".active").removeClass('active').end().addClass('active').parents(".es-bar").animate({
	          right: '0'
	        }, 300);
	        this.clickBar($this);
	        $($this.data('id')).siblings(".es-bar-main.active").removeClass('active').end().addClass('active');
	      } else {
	        $this.removeClass('active').parents(".es-bar").animate({
	          right: '-230px'
	        }, 300);
	      }
	    }
	  }, {
	    key: 'onBtnAction',
	    value: function onBtnAction(e) {
	      var $this = $(e.currentTarget);
	      var url = $this.data('url');
	
	      $.get(url, function (html) {
	        $this.closest('.es-bar-main').html(html);
	        $(".es-bar .bar-main-body").perfectScrollbar({ wheelSpeed: 50 });
	      });
	    }
	  }, {
	    key: 'clickBar',
	    value: function clickBar($this) {
	      if (typeof $this.find('a').data('url') != 'undefined') {
	        var url = $this.find('a').data('url');
	
	        $.get(url, function (html) {
	          $($this.data('id')).html(html);
	          $(".es-bar .bar-main-body").perfectScrollbar({ wheelSpeed: 50 });
	        });
	      }
	    }
	  }, {
	    key: 'isNotLogin',
	    value: function isNotLogin() {
	      var $loginModal = $("#login-modal");
	
	      $loginModal.modal('show');
	      $.get($loginModal.data('url'), function (html) {
	        $loginModal.html(html);
	      });
	    }
	  }, {
	    key: 'goTop',
	    value: function goTop() {
	      var $gotop = $(".go-top");
	
	      $(window).scroll(function (event) {
	        var scrollTop = $(window).scrollTop();
	
	        if (scrollTop >= 300) {
	          $gotop.addClass('show');
	        } else if ($gotop.hasClass('show')) {
	          $gotop.removeClass('show');
	        }
	      });
	      $gotop.click(function () {
	        return $("body,html").animate({
	          scrollTop: 0
	        }, 300), !1;
	      });
	    }
	  }]);
	
	  return EsBar;
	}();
	
	exports["default"] = EsBar;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _esBar = __webpack_require__("7150e3e195d39d1d2f69");
	
	var _esBar2 = _interopRequireDefault(_esBar);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var esBar = new _esBar2["default"]({
	  ele: '.es-bar'
	});

/***/ })

});
//# sourceMappingURL=index.js.map