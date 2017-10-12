webpackJsonp(["app/js/order/coupon-item/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Coupon = function () {
	  function Coupon(props) {
	    _classCallCheck(this, Coupon);
	
	    this.$element = props.element;
	    this.$showDeductAmount = this.$element.find('#show-deduct-amount');
	    this.$noUseCouponCode = this.$element.find('#no-use-coupon-code');
	    this.$couponCode = this.$element.find("input[name='couponCode']");
	    this.$selectCoupon = this.$element.find('#coupon-select');
	    this.$couponNotify = this.$element.find('#code-notify');
	    this.$form = $('#order-create-form');
	    this.initEvent();
	    this.init();
	  }
	
	  _createClass(Coupon, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      var $element = this.$element;
	      $element.on('change', '#coupon-select', function (event) {
	        return _this.couponSelect(event);
	      });
	      $element.on('click', '#change-coupon-code', function (event) {
	        return _this.showChangeCoupon(event);
	      });
	      $element.on('click', '#cancel-coupon', function (event) {
	        return _this.cancelCoupon(event);
	      });
	      $element.on('click', '#use-coupon', function (event) {
	        return _this.useCoupon(event);
	      });
	      this.$form.on('submit', function (event) {
	        return _this.formSubmit(event);
	      });
	    }
	  }, {
	    key: 'init',
	    value: function init() {
	      if (this.$selectCoupon.length > 0) {
	        this._setCoupon(this.$selectCoupon.val(), false);
	      }
	
	      this._showDeductAmount();
	    }
	  }, {
	    key: 'couponSelect',
	    value: function couponSelect(event) {
	      var $this = $(event.currentTarget);
	      var coupon = $this.find('option:selected');
	      var val = $this.val();
	
	      if (!val) {
	        this._selectEmptyCoupon();
	        return;
	      }
	
	      this._setCoupon(val);
	    }
	  }, {
	    key: 'showChangeCoupon',
	    value: function showChangeCoupon(event) {
	      var $this = $(event.currentTarget);
	
	      this._showCouponCode();
	      this._setCoupon().focus();
	    }
	  }, {
	    key: 'useCoupon',
	    value: function useCoupon() {
	      this._setCoupon(this.$couponCode.val());
	    }
	  }, {
	    key: '_checkCoupon',
	    value: function _checkCoupon() {
	      var self = this;
	      var code = this.$couponCode.val();
	      if (!this.$productType) {
	        this.$productType = $("input[name='targetType']");
	      }
	      if (!this.$productId) {
	        this.$productId = $("input[name='targetId']");
	      }
	      if (!code) {
	        self.$couponNotify.css("display", "none");
	        self._formValidatePass();
	        return;
	      }
	      var data = {
	        'code': code,
	        'targetType': this.$productType.val(),
	        'targetId': this.$productId.val(),
	        'price': $("input[name='price']").val()
	      };
	
	      $.ajax({
	        url: $('#use-coupon').data('url'),
	        async: false,
	        type: 'POST',
	        data: data,
	        success: function success(data) {
	          if (data.useable == 'no') {
	            self.$couponNotify.addClass('alert-danger').text(data.message).css("display", "inline-block");
	            self._showDeductAmount();
	            self._formValidateReject();
	          } else {
	            var text = data['type'] == 'discount' ? Translator.trans('order.create.use_discount_coupon_hint', { rate: data['rate'] }) : Translator.trans('order.create.use_price_coupon_hint', { rate: data['rate'] });
	            self.$couponNotify.removeClass('alert-danger').addClass("alert-success").text(text).css("display", "inline-block");
	            self._showDeductAmount(data.deduct_amount_format);
	            self._formValidatePass();
	          }
	        }
	      });
	    }
	  }, {
	    key: 'cancelCoupon',
	    value: function cancelCoupon(event) {
	      this._hideCouponCode();
	      if (this.$selectCoupon.length) {
	        this.$selectCoupon.trigger('change');
	      } else {
	        this._setCoupon();
	      }
	    }
	  }, {
	    key: 'formSubmit',
	    value: function formSubmit() {
	      this._checkCoupon();
	      if (this.formValidate === false) {
	        this.$form.find('#order-create-btn').button('reset');
	        return false;
	      }
	    }
	  }, {
	    key: '_showDeductAmount',
	    value: function _showDeductAmount() {
	      var amount = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.$showDeductAmount.data('placeholder');
	
	      //显示优惠码优惠的金额
	      this.$showDeductAmount.text(amount);
	    }
	  }, {
	    key: '_showCouponCode',
	    value: function _showCouponCode() {
	      //显示手动输入优惠码框,隐藏select，去除右侧优惠金额信息展示
	      $('#coupon-code').show();
	      $('#select-coupon-box').hide();
	      this._showDeductAmount();
	    }
	  }, {
	    key: '_hideCouponCode',
	    value: function _hideCouponCode() {
	      //隐藏手动输入优惠码框，显示select
	      $('#coupon-code').hide();
	      $('#select-coupon-box').show();
	    }
	  }, {
	    key: '_selectEmptyCoupon',
	    value: function _selectEmptyCoupon() {
	      this._setCoupon();
	      this._showDeductAmount();
	    }
	  }, {
	    key: '_setCoupon',
	    value: function _setCoupon() {
	      var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var triggerCaculate = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	
	      //设置选择的优惠码code
	      this.$couponCode.val(value);
	      !value ? this.$noUseCouponCode.show() : this.$noUseCouponCode.hide();
	      this._checkCoupon();
	      if (triggerCaculate) {
	        this._calculatePrice();
	      }
	      return this.$couponCode;
	    }
	  }, {
	    key: '_formValidatePass',
	    value: function _formValidatePass() {
	      this.formValidate = true;
	    }
	  }, {
	    key: '_formValidateReject',
	    value: function _formValidateReject() {
	      this.formValidate = false;
	    }
	  }, {
	    key: '_calculatePrice',
	    value: function _calculatePrice() {
	      if (this.formValidate === false) {
	        this.$couponCode.attr('disabled', 'disabled');
	      }
	
	      this.$form.trigger('calculatePrice');
	      this.$couponCode.removeAttr('disabled');
	    }
	  }]);
	
	  return Coupon;
	}();
	
	new Coupon({
	  element: $('#coupon-deducts')
	});

/***/ })
]);
//# sourceMappingURL=index.js.map