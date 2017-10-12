webpackJsonp(["app/js/order/coupon-item/index"],{

/***/ "9bcf1f0bd4699eb8066c":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Coupon = function () {
	  function Coupon(props) {
	    _classCallCheck(this, Coupon);
	
	    Object.assign(this, {}, props);
	
	    this.$form = $(this.form);
	
	    this.$couponCode = this.$form.find('input[name="couponCode"]');
	    this.$productType = this.$form.find('input[name="targetType"]');
	    this.$productId = this.$form.find('input[name="targetId"]');
	    this.$price = this.$form.find('input[name="price"]');
	
	    this.$errorMessage = this.$form.find('#coupon-error-message');
	
	    this.$deductAmountLabel = this.$form.find('#deduct-amount-label');
	    this.$couponCodeLabel = this.$form.find('#coupon-code-label');
	
	    this.$selectCouponBtn = this.$form.find("#select-coupon-btn");
	
	    this.init();
	  }
	
	  _createClass(Coupon, [{
	    key: 'init',
	    value: function init() {
	      this.initEvent();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      var $form = this.$form;
	
	      $form.on('click', '#use-coupon-btn', function (event) {
	        return _this.useCoupon(event);
	      });
	      $form.on('click', '#cancel-use-coupon-btn', function (event) {
	        return _this.cancelCoupon(event);
	      });
	      $form.on('change', 'input[name="couponCode"]', function (event) {
	        return _this.inputCode(event);
	      });
	
	      this.selectCoupon();
	    }
	  }, {
	    key: 'inputCode',
	    value: function inputCode(event) {
	      var $this = $(event.currentTarget);
	
	      if ($this.val()) {
	        this.errorMessage();
	      }
	    }
	  }, {
	    key: 'useCoupon',
	    value: function useCoupon(event) {
	      var _this2 = this;
	
	      var $this = $(event.currentTarget);
	      var code = this.$couponCode.val();
	      if (!code) {
	        this.errorMessage(this.$couponCode.data('display'));
	        return;
	      }
	
	      $this.button('loading');
	
	      this.validate(event, function (data) {
	        $this.button('reset');
	        if (data.useable == 'no') {
	          _this2.errorMessage(data.message);
	        } else {
	          var priceType = _this2.$form.data('price-type');
	          var coinRate = _this2.$form.data('coin-rate');
	          var coinName = _this2.$form.data('coin-name');
	
	          var deductAmount = data['type'] == 'discount' ? _this2.$price.val() * data['rate'] : data['rate'];
	
	          if (priceType === 'coin') {
	            deductAmount = parseFloat(parseFloat(deductAmount) * parseFloat(coinRate)).toFixed(2) + ' ' + coinName;
	          } else {
	            deductAmount = '￥' + deductAmount;
	          }
	
	          _this2.useCouponAfter(deductAmount, code);
	        }
	      });
	    }
	  }, {
	    key: 'useCouponAfter',
	    value: function useCouponAfter(deductAmount, code) {
	      this.$deductAmountLabel.text(deductAmount);
	      this.$couponCodeLabel.text(code);
	
	      this.toggleShow('use');
	
	      this.$form.trigger('calculatePrice');
	      this.$form.trigger('addPriceItem', ['coupon-price', Translator.trans('order.create.coupon_deduction'), deductAmount]);
	    }
	  }, {
	    key: 'cancelCoupon',
	    value: function cancelCoupon(event) {
	      this.$couponCode.val('');
	      this.$form.trigger('calculatePrice');
	      this.$form.trigger('removePriceItem', ['coupon-price']);
	      this.toggleShow('cancel');
	    }
	  }, {
	    key: 'errorMessage',
	    value: function errorMessage(text) {
	      if (text) {
	        this.$errorMessage.text(text).show();
	        var $parent = this.$errorMessage.parent('.cd-form-group');
	        if (!$parent.hasClass('has-error')) {
	          $parent.addClass('has-error');
	        }
	      } else {
	        this.$errorMessage.text('').hide().parent('.cd-form-group.has-error').removeClass('has-error');
	      }
	    }
	  }, {
	    key: 'validate',
	    value: function validate(event, callback) {
	      var $this = $(event.currentTarget);
	
	      var data = {
	        'code': this.$couponCode.val(),
	        'targetType': this.$productType.val(),
	        'targetId': this.$productId.val(),
	        'price': this.$price.val()
	      };
	
	      $.ajax({
	        url: $this.data('url'),
	        type: 'POST',
	        data: data
	      }).done(function (data) {
	        if (typeof callback === 'function') {
	          callback(data);
	        }
	      });
	    }
	  }, {
	    key: 'toggleShow',
	    value: function toggleShow(type) {
	      var $selectCoupon = this.$form.find('#order-center-coupon__select');
	      var $selectedCoupon = this.$form.find('#order-center-coupon__selected');
	
	      if (type === 'use') {
	        $selectCoupon.hide();
	        $selectedCoupon.show();
	      } else if (type === 'cancel') {
	        $selectCoupon.show();
	        $selectedCoupon.hide();
	      }
	    }
	  }, {
	    key: 'selectCoupon',
	    value: function selectCoupon() {
	      var _this3 = this;
	
	      cd.radio({
	        el: '.js-existing-coupon',
	        cb: function cb(event) {
	          var $this = $(event.currentTarget);
	          var code = $this.data('code');
	          var deductAmount = $this.data('deductAmount');
	          _this3.$couponCode.val(code);
	
	          _this3.$selectCouponBtn.trigger('click');
	
	          _this3.useCouponAfter(deductAmount, code);
	        }
	      });
	    }
	  }]);
	
	  return Coupon;
	}();
	
	exports["default"] = Coupon;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _coupon = __webpack_require__("9bcf1f0bd4699eb8066c");
	
	var _coupon2 = _interopRequireDefault(_coupon);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _coupon2["default"]({
	  form: '#order-create-form'
	});

/***/ })

});
//# sourceMappingURL=index.js.map