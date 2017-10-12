webpackJsonp(["app/js/order/show/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Order = function () {
	  function Order(props) {
	    _classCallCheck(this, Order);
	
	    this.$element = props.element;
	    this.$priceShow = this.$element.find('#price-show');
	    this.initEvent();
	  }
	
	  _createClass(Order, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('calculatePrice', function (event) {
	        return _this.calculatePrice(event);
	      });
	      this.$element.trigger('calculatePrice');
	      this.validate();
	    }
	  }, {
	    key: 'calculatePrice',
	    value: function calculatePrice() {
	      var self = this;
	      var formData = this.$element.serializeArray();
	      $.get(this.$element.data('priceCalculate'), formData, function (data) {
	        self.$priceShow.text(data);
	      });
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
	
	new Order({
	  element: $('#order-create-form')
	});

/***/ })
]);
//# sourceMappingURL=index.js.map