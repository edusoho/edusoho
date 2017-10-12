webpackJsonp(["app/js/cashier/mobile/index"],{

/***/ "0462758757283f323cc5":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Coin = function () {
	  function Coin(props) {
	    _classCallCheck(this, Coin);
	
	    this.$container = props.$coinContainer;
	    this.cashierForm = props.cashierForm;
	    this.$form = props.$form;
	    this.priceType = this.$container.data('priceType');
	    this.coinRate = this.$container.data('coinRate');
	    this.maxCoinInput = this.$container.data('maxAllowCoin') > this.$container.data('coinBalance') ? this.$container.data('coinBalance') : this.$container.data('maxAllowCoin');
	    this.initEvent();
	  }
	
	  _createClass(Coin, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$form.on('change', '.js-coin-amount', function (event) {
	        return _this.changeAmount(event);
	      });
	    }
	  }, {
	    key: 'changeAmount',
	    value: function changeAmount(event) {
	      var $this = $(event.currentTarget);
	      var inputCoinNum = $this.val();
	      $this.val(parseFloat(inputCoinNum).toFixed(2));
	
	      if (isNaN(inputCoinNum) || inputCoinNum <= 0) {
	        inputCoinNum = 0;
	        $this.val(parseFloat(inputCoinNum).toFixed(2));
	        this.removePasswordValidate();
	
	        this.$form.trigger('removePriceItem', ['coin-price']);
	        this.cashierForm.calcPayPrice(inputCoinNum);
	      }
	      if (inputCoinNum > this.maxCoinInput) {
	        inputCoinNum = this.maxCoinInput;
	        $this.val(parseFloat(inputCoinNum).toFixed(2));
	      }
	
	      if (inputCoinNum > 0) {
	        this.addPasswordValidate();
	        var coinName = this.$form.data('coin-name');
	        var price = 0.00;
	        if (this.priceType === 'coin') {
	          price = parseFloat(inputCoinNum).toFixed(2) + ' ' + coinName;
	
	          var originalPirce = parseFloat(this.$container.data('maxAllowCoin'));
	          var coinPrice = parseFloat(originalPirce - inputCoinNum).toFixed(2) + ' ' + coinName;;
	          this.$form.trigger('changeCoinPrice', [coinPrice]);
	        } else {
	          price = '￥' + parseFloat(inputCoinNum / this.coinRate).toFixed(2);
	        }
	        this.$form.trigger('addPriceItem', ['coin-price', coinName + Translator.trans('order.create.minus'), price]);
	        this.cashierForm.calcPayPrice(inputCoinNum);
	      }
	    }
	  }, {
	    key: 'addPasswordValidate',
	    value: function addPasswordValidate() {
	      this.$container.find('[name="payPassword"]').rules('add', 'required passwordCheck');
	    }
	  }, {
	    key: 'removePasswordValidate',
	    value: function removePasswordValidate() {
	      this.$container.find('[name="payPassword"]').rules('remove', 'required passwordCheck');
	    }
	  }]);
	
	  return Coin;
	}();
	
	exports["default"] = Coin;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _coin = __webpack_require__("0462758757283f323cc5");
	
	var _coin2 = _interopRequireDefault(_coin);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CashierForm = function () {
	  function CashierForm($form) {
	    _classCallCheck(this, CashierForm);
	
	    this.$container = $form;
	
	    this.validator = this.$container.validate();
	
	    this.initEvent();
	    this.initCoin();
	  }
	
	  _createClass(CashierForm, [{
	    key: 'initCoin',
	    value: function initCoin() {
	      var $coin = $('#coin-use-section');
	      if ($coin.length > 0) {
	        this.coin = new _coin2["default"]($coin, this);
	      }
	    }
	  }, {
	    key: 'calcPayPrice',
	    value: function calcPayPrice(coinAmount) {
	
	      var self = this;
	      $.post(this.$container.data('priceUrl'), {
	        coinAmount: coinAmount
	      }, function (resp) {
	        self.$container.find('.js-pay-price').text(resp.data);
	      });
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      // 支付方式切换
	      this.$container.on('click', '.check', function (event) {
	        var $this = $(event.currentTarget);
	        if (!$this.hasClass('active') && !$this.hasClass('disabled')) {
	          $this.addClass('active').siblings().removeClass('active');
	          $("input[name='payment']").val($this.attr("id"));
	        }
	      });
	
	      var $form = this.$container;
	      $form.on('click', '.js-pay-btn', function (event) {
	        if ($form.valid()) {
	          $form.submit();
	        }
	      });
	    }
	  }]);
	
	  return CashierForm;
	}();
	
	new CashierForm($('#cashier-form'));

/***/ })

});
//# sourceMappingURL=index.js.map