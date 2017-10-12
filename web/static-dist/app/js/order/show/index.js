webpackJsonp(["app/js/order/show/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _order = __webpack_require__("17afaf5ff8b43dbeaf62");
	
	var _order2 = _interopRequireDefault(_order);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _order2["default"]({
	  element: '#order-create-form'
	});

/***/ }),

/***/ "17afaf5ff8b43dbeaf62":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Order = function () {
	  function Order(props) {
	    _classCallCheck(this, Order);
	
	    this.$element = $(props.element);
	    this.$realpayPrice = this.$element.find('#realpay-price');
	    this.$priceList = this.$element.find('#order-center-price-list');
	
	    this.init();
	  }
	
	  _createClass(Order, [{
	    key: 'init',
	    value: function init() {
	      this.initEvent();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('calculatePrice', function (event) {
	        return _this.calculatePrice(event);
	      });
	      this.$element.on('addPriceItem', function (event, id, title, price) {
	        return _this.addPriceItem(event, id, title, price);
	      });
	      this.$element.on('removePriceItem', function (event, id) {
	        return _this.removePriceItem(event, id);
	      });
	
	      this.$element.trigger('calculatePrice');
	      this.validate();
	    }
	  }, {
	    key: 'calculatePrice',
	    value: function calculatePrice() {
	      var _this2 = this;
	
	      var formData = this.$element.serializeArray();
	      $.get(this.$element.data('priceCalculate'), formData, function (data) {
	        _this2.$realpayPrice.text(data);
	      });
	    }
	  }, {
	    key: 'hasPriceItem',
	    value: function hasPriceItem(event, id) {
	      var $priceItem = $('#' + id);
	      if ($priceItem.length) {
	        return true;
	      }
	
	      return false;
	    }
	  }, {
	    key: 'addPriceItem',
	    value: function addPriceItem(event, id, title, price) {
	      var $priceItem = $('#' + id);
	
	      if (this.hasPriceItem(event, id)) {
	        $priceItem.remove();
	      }
	
	      var html = '\n      <div class="order-center-price" id="' + id + '">\n        <div class="order-center-price__title">' + title + '</div>\n        <div class="order-center-price__content">-' + price + '</div>\n      </div>\n    ';
	
	      this.$priceList.append(html);
	    }
	  }, {
	    key: 'removePriceItem',
	    value: function removePriceItem(event, id) {
	      var $priceItem = $('#' + id);
	
	      if (this.hasPriceItem(event, id)) {
	        $priceItem.remove();
	      }
	    }
	  }, {
	    key: 'validate',
	    value: function validate() {
	      this.$element.submit(function (event) {
	        $('#order-create-btn').button('loading');
	        return true;
	      });
	    }
	  }]);
	
	  return Order;
	}();
	
	exports["default"] = Order;

/***/ })

});
//# sourceMappingURL=index.js.map