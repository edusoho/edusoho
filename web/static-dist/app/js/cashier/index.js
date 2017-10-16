webpackJsonp(["app/js/cashier/index"],{

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

/***/ "c0f4981719a2ddce4be9":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _ajax = __webpack_require__("af463f59266a614cffe8");
	
	var _ajax2 = _interopRequireDefault(_ajax);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var tradeModule = function tradeModule(api) {
	  return {
	    get: function get(options) {
	      return (0, _ajax2["default"])(Object.assign({
	        url: api + '/trades/' + options.params.tradeSn
	      }, options));
	    },
	    create: function create(options) {
	      return (0, _ajax2["default"])(Object.assign({
	        url: api + '/trades',
	        type: 'POST'
	      }, options));
	    }
	  };
	};
	
	exports["default"] = tradeModule;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _form = __webpack_require__("d2551c5247eab259ba5a");
	
	var _form2 = _interopRequireDefault(_form);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _form2["default"]({
	  element: '#cashier-form'
	});

/***/ }),

/***/ "8d6b1145d2f0f7c079ac":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _payment = __webpack_require__("4d4dc8c99e38b826f59e");
	
	var _payment2 = _interopRequireDefault(_payment);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var AlipayLegacyExpress = function (_BasePayment) {
	  _inherits(AlipayLegacyExpress, _BasePayment);
	
	  function AlipayLegacyExpress() {
	    _classCallCheck(this, AlipayLegacyExpress);
	
	    return _possibleConstructorReturn(this, (AlipayLegacyExpress.__proto__ || Object.getPrototypeOf(AlipayLegacyExpress)).apply(this, arguments));
	  }
	
	  _createClass(AlipayLegacyExpress, [{
	    key: 'afterTradeCreated',
	    value: function afterTradeCreated(res) {
	      var options = this.getOptions();
	      if (options.showConfirmModal) {
	        window.open(res.payUrl, '_blank');
	        this.showConfirmModal(res.tradeSn);
	      } else {
	        location.href = res.payUrl;
	      }
	    }
	  }]);
	
	  return AlipayLegacyExpress;
	}(_payment2["default"]);
	
	exports["default"] = AlipayLegacyExpress;

/***/ }),

/***/ "e2fcc42cde2f41b58be2":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _payment = __webpack_require__("4d4dc8c99e38b826f59e");
	
	var _payment2 = _interopRequireDefault(_payment);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var AlipayLegacyWap = function (_BasePayment) {
		_inherits(AlipayLegacyWap, _BasePayment);
	
		function AlipayLegacyWap() {
			_classCallCheck(this, AlipayLegacyWap);
	
			return _possibleConstructorReturn(this, (AlipayLegacyWap.__proto__ || Object.getPrototypeOf(AlipayLegacyWap)).apply(this, arguments));
		}
	
		_createClass(AlipayLegacyWap, [{
			key: 'afterTradeCreated',
			value: function afterTradeCreated(res) {
				location.href = res.payUrl;
			}
		}, {
			key: 'customParams',
			value: function customParams(params) {
				params['app_pay'] = 'Y';
				return params;
			}
		}]);
	
		return AlipayLegacyWap;
	}(_payment2["default"]);
	
	exports["default"] = AlipayLegacyWap;

/***/ }),

/***/ "c55e05a178f0ee906431":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _payment = __webpack_require__("4d4dc8c99e38b826f59e");
	
	var _payment2 = _interopRequireDefault(_payment);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var ConfirmModal = function () {
	  function ConfirmModal() {
	    _classCallCheck(this, ConfirmModal);
	
	    this.$container = $('body');
	    this.modalID = 'cashier-confirm-modal';
	    this.tradeSn = '';
	
	
	    var template = '\n      <div id="' + this.modalID + '" class="modal">\n        <div class="modal-dialog cd-modal-dialog">\n          <div class="modal-content">\n            <div class="modal-header">\n              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">\n                <i class="cd-icon cd-icon-close"></i>\n              </button>\n              <h4 class="modal-title">' + Translator.trans('cashier.confirm.title') + '</h4>\n            </div>\n            <div class="modal-body">\n              <p>\n              ' + Translator.trans('cashier.confirm.desc') + '\n              </p>\n            </div>\n            <div class="modal-footer">\n              <a class="btn cd-btn cd-btn-flat-default cd-btn-lg" data-dismiss="modal">' + Translator.trans('cashier.confirm.pick_again') + '</a>\n              <a class="btn cd-btn cd-btn-primary cd-btn-lg js-confirm-btn">' + Translator.trans('cashier.confirm.success') + '</a>\n            </div>\n          </div>\n        <div>  \n      </div>\n    ';
	
	    if (this.$container.find('#' + this.modalID).length === 0) {
	      this.$container.append(template);
	    }
	
	    $('body').on('click', '.js-confirm-btn', this.checkIsPaid.bind(this));
	  }
	
	  _createClass(ConfirmModal, [{
	    key: 'checkIsPaid',
	    value: function checkIsPaid() {
	      var _this = this;
	
	      _payment2["default"].getTrade(this.tradeSn).then(function (res) {
	        if (res.isPaid) {
	          location.href = res.paidSuccessUrl;
	        } else {
	          (0, _notify2["default"])('danger', Translator.trans('cashier.confirm.fail_message'));
	          $('#' + _this.modalID).modal('hide');
	        }
	      });
	    }
	  }, {
	    key: 'show',
	    value: function show(tradeSn) {
	      $('#' + this.modalID).modal('show');
	      this.tradeSn = tradeSn;
	    }
	  }]);
	
	  return ConfirmModal;
	}();
	
	exports["default"] = ConfirmModal;

/***/ }),

/***/ "11e5fc7e9f7d4f25c86a":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _alipay_legacy_wap = __webpack_require__("e2fcc42cde2f41b58be2");
	
	var _alipay_legacy_wap2 = _interopRequireDefault(_alipay_legacy_wap);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var LianlianpayWap = function (_AlipayLegacyWap) {
	  _inherits(LianlianpayWap, _AlipayLegacyWap);
	
	  function LianlianpayWap() {
	    _classCallCheck(this, LianlianpayWap);
	
	    return _possibleConstructorReturn(this, (LianlianpayWap.__proto__ || Object.getPrototypeOf(LianlianpayWap)).apply(this, arguments));
	  }
	
	  return LianlianpayWap;
	}(_alipay_legacy_wap2["default"]);
	
	exports["default"] = LianlianpayWap;

/***/ }),

/***/ "986a76353ac203061fe7":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _alipay_legacy_express = __webpack_require__("8d6b1145d2f0f7c079ac");
	
	var _alipay_legacy_express2 = _interopRequireDefault(_alipay_legacy_express);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var LianlianpayWeb = function (_AlipayLegacyExpress) {
	  _inherits(LianlianpayWeb, _AlipayLegacyExpress);
	
	  function LianlianpayWeb() {
	    _classCallCheck(this, LianlianpayWeb);
	
	    return _possibleConstructorReturn(this, (LianlianpayWeb.__proto__ || Object.getPrototypeOf(LianlianpayWeb)).apply(this, arguments));
	  }
	
	  return LianlianpayWeb;
	}(_alipay_legacy_express2["default"]);
	
	exports["default"] = LianlianpayWeb;

/***/ }),

/***/ "4d4dc8c99e38b826f59e":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _api = __webpack_require__("5eb223de522186da1dd9");
	
	var _api2 = _interopRequireDefault(_api);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _confirm = __webpack_require__("c55e05a178f0ee906431");
	
	var _confirm2 = _interopRequireDefault(_confirm);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var BasePayment = function () {
		function BasePayment() {
			_classCallCheck(this, BasePayment);
		}
	
		_createClass(BasePayment, [{
			key: 'setOptions',
			value: function setOptions(options) {
				this.options = options;
			}
		}, {
			key: 'getOptions',
			value: function getOptions() {
				return this.options;
			}
		}, {
			key: 'showConfirmModal',
			value: function showConfirmModal(tradeSn) {
				if (!this.confirmModal) {
					this.confirmModal = new _confirm2["default"]();
				}
	
				this.confirmModal.show(tradeSn);
			}
		}, {
			key: 'pay',
			value: function pay(params) {
	
				var trade = this.createTrade(params);
				if (trade.paidSuccessUrl) {
					location.href = trade.paidSuccessUrl;
				} else {
					this.afterTradeCreated(trade);
				}
			}
		}, {
			key: 'afterTradeCreated',
			value: function afterTradeCreated(res) {}
		}, {
			key: 'customParams',
			value: function customParams(params) {
				return params;
			}
		}, {
			key: 'filterParams',
			value: function filterParams(postParams) {
				var params = {
					gateway: postParams.gateway,
					type: postParams.type,
					orderSn: postParams.orderSn,
					coinAmount: postParams.coinAmount,
					amount: postParams.amount,
					openid: postParams.openid,
					payPassword: postParams.payPassword
				};
	
				console.log(params);
				params = this.customParams(params);
	
				Object.keys(params).forEach(function (k) {
					return !params[k] && params[k] !== undefined && delete params[k];
				});
	
				return params;
			}
		}, {
			key: 'createTrade',
			value: function createTrade(postParams) {
	
				var params = this.filterParams(postParams);
	
				var trade = null;
	
				_api2["default"].trade.create({ data: params, async: false, promise: false }).done(function (res) {
					trade = res;
				}).error(function (res) {
					(0, _notify2["default"])('danger', Translator.trans('cashier.pay.error_message'));
				});
	
				return trade;
			}
		}], [{
			key: 'getTrade',
			value: function getTrade(tradeSn) {
				var orderSn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	
				var params = {};
	
				if (tradeSn) {
					params.tradeSn = tradeSn;
				}
	
				if (orderSn) {
					params.orderSn = orderSn;
				}
	
				return _api2["default"].trade.get({
					params: params
				});
			}
		}]);
	
		return BasePayment;
	}();
	
	exports["default"] = BasePayment;

/***/ }),

/***/ "d2551c5247eab259ba5a":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _coin = __webpack_require__("0462758757283f323cc5");
	
	var _coin2 = _interopRequireDefault(_coin);
	
	var _sdk = __webpack_require__("1af657f7645917c6310d");
	
	var _sdk2 = _interopRequireDefault(_sdk);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CashierForm = function () {
	  function CashierForm(props) {
	    _classCallCheck(this, CashierForm);
	
	    this.$form = $(props.element);
	    this.$priceList = this.$form.find('#order-center-price-list');
	
	    this.validator = this.$form.validate();
	
	    this.initEvent();
	    this.initCoin();
	
	    this.paySdk = new _sdk2["default"]();
	  }
	
	  _createClass(CashierForm, [{
	    key: 'initCoin',
	    value: function initCoin() {
	      var $coin = $('#coin-use-section');
	      if ($coin.length > 0) {
	        this.coin = new _coin2["default"]({
	          $coinContainer: $coin,
	          cashierForm: this,
	          $form: this.$form
	        });
	      }
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      var $form = this.$form;
	
	      $form.on('click', '.js-pay-type', function (event) {
	        return _this.switchPayType(event);
	      });
	      $form.on('click', '.js-pay-btn', function (event) {
	        return _this.payOrder(event);
	      });
	      $form.on('addPriceItem', function (event, id, title, price) {
	        return _this.addPriceItem(event, id, title, price);
	      });
	      $form.on('removePriceItem', function (event, id) {
	        return _this.removePriceItem(event, id);
	      });
	      $form.on('changeCoinPrice', function (event, price) {
	        return _this.changeCoinPrice(event, price);
	      });
	    }
	  }, {
	    key: 'payOrder',
	    value: function payOrder(event) {
	      var $form = this.$form;
	
	      if ($form.valid()) {
	        var $btn = $(event.currentTarget);
	        $btn.button('loading');
	        var params = this.formDataToObject($form);
	
	        params.payAmount = $form.find('.js-pay-price').text();
	        this.paySdk.pay(params);
	        $btn.button('reset');
	      }
	    }
	  }, {
	    key: 'switchPayType',
	    value: function switchPayType(event) {
	      var $this = $(event.currentTarget);
	      if (!$this.hasClass('active')) {
	        $this.addClass('active').siblings().removeClass('active');
	        $("input[name='payment']").val($this.attr("id"));
	      }
	    }
	  }, {
	    key: 'calcPayPrice',
	    value: function calcPayPrice(coinAmount) {
	      var _this2 = this;
	
	      $.post(this.$form.data('priceUrl'), {
	        coinAmount: coinAmount
	      }).done(function (res) {
	        _this2.$form.find('.js-pay-price').text(res.data);
	      });
	    }
	  }, {
	    key: 'formDataToObject',
	    value: function formDataToObject($form) {
	      var params = {},
	          formArr = $form.serializeArray();
	      for (var index in formArr) {
	        params[formArr[index].name] = formArr[index].value;
	      }
	
	      return params;
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
	    key: 'changeCoinPrice',
	    value: function changeCoinPrice(event, price) {
	      var $payCoin = this.$form.find('.js-pay-coin');
	      $payCoin.text(price);
	    }
	  }]);
	
	  return CashierForm;
	}();
	
	exports["default"] = CashierForm;

/***/ }),

/***/ "a5753a9f265083dbf9c8":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _payment = __webpack_require__("4d4dc8c99e38b826f59e");
	
	var _payment2 = _interopRequireDefault(_payment);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var WechatPayJs = function (_BasePayment) {
	  _inherits(WechatPayJs, _BasePayment);
	
	  function WechatPayJs() {
	    _classCallCheck(this, WechatPayJs);
	
	    return _possibleConstructorReturn(this, (WechatPayJs.__proto__ || Object.getPrototypeOf(WechatPayJs)).apply(this, arguments));
	  }
	
	  _createClass(WechatPayJs, [{
	    key: 'afterTradeCreated',
	    value: function afterTradeCreated(res) {
	      location.href = '/pay/center/wxpay?tradeSn=' + res.tradeSn;
	    }
	  }]);
	
	  return WechatPayJs;
	}(_payment2["default"]);
	
	exports["default"] = WechatPayJs;

/***/ }),

/***/ "02ad171abc9cada5bff7":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _payment = __webpack_require__("4d4dc8c99e38b826f59e");
	
	var _payment2 = _interopRequireDefault(_payment);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var WechatPayMweb = function (_BasePayment) {
		_inherits(WechatPayMweb, _BasePayment);
	
		function WechatPayMweb() {
			_classCallCheck(this, WechatPayMweb);
	
			return _possibleConstructorReturn(this, (WechatPayMweb.__proto__ || Object.getPrototypeOf(WechatPayMweb)).apply(this, arguments));
		}
	
		_createClass(WechatPayMweb, [{
			key: 'afterTradeCreated',
			value: function afterTradeCreated(res) {
				location.href = res.mwebUrl;
				this.startInterval(res.tradeSn);
			}
		}, {
			key: 'startInterval',
			value: function startInterval(tradeSn) {
				window.intervalWechatId = setInterval(this.checkIsPaid.bind(this, tradeSn), 2000);
			}
		}, {
			key: 'checkIsPaid',
			value: function checkIsPaid(tradeSn) {
				_payment2["default"].getTrade(tradeSn).then(function (res) {
					if (res.isPaid) {
						location.href = res.paidSuccessUrl;
					}
				});
			}
		}]);
	
		return WechatPayMweb;
	}(_payment2["default"]);
	
	exports["default"] = WechatPayMweb;

/***/ }),

/***/ "fef17b80bef935ad2682":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _payment = __webpack_require__("4d4dc8c99e38b826f59e");
	
	var _payment2 = _interopRequireDefault(_payment);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }
	
	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }
	
	var WechatPayNative = function (_BasePayment) {
	  _inherits(WechatPayNative, _BasePayment);
	
	  function WechatPayNative() {
	    _classCallCheck(this, WechatPayNative);
	
	    var _this = _possibleConstructorReturn(this, (WechatPayNative.__proto__ || Object.getPrototypeOf(WechatPayNative)).call(this));
	
	    _this.$container = $('body');
	    _this.modalID = 'wechat-qrcode-modal';
	
	
	    var template = '\n      <div id="' + _this.modalID + '" class="modal">\n        <div class="modal-dialog cd-modal-dialog cd-modal-dialog-sm">\n          <div class="modal-content">\n          \n            <div class="modal-header">\n              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">\n                <i class="cd-icon cd-icon-close"></i>\n              </button>\n              <h4 class="modal-title">' + Translator.trans('cashier.wechat_pay') + '</h4>\n            </div>\n            \n            <div class="modal-body">\n              <div class="text-center">\n                <img class="cd-mb16 js-qrcode-img" src="">\n                <div class="cd-mb16">\n                  ' + Translator.trans('cashier.wechat_pay.scan_qcode_pay_tips') + '\n                </div>\n                <div class="cd-text-danger cd-mb32 js-pay-amount" style="font-size:16px;"></div>\n              </div>\n            </div>\n            \n          </div>\n        </div>\n      </div>\n    ';
	
	    if (_this.$container.find('#' + _this.modalID).length === 0) {
	      _this.$container.append(template);
	    }
	
	    _this.$container.find('#' + _this.modalID).on('hidden.bs.modal', function () {
	      clearInterval(window.intervalWechatId);
	    });
	    return _this;
	  }
	
	  _createClass(WechatPayNative, [{
	    key: 'afterTradeCreated',
	    value: function afterTradeCreated(res) {
	      var $modal = this.$container.find('#' + this.modalID);
	      $modal.find('.js-qrcode-img').attr('src', res.qrcodeUrl);
	      $modal.find('.js-pay-amount').text('￥' + res.cash_amount);
	      $modal.modal('show');
	      this.startInterval(res.tradeSn);
	    }
	  }, {
	    key: 'startInterval',
	    value: function startInterval(tradeSn) {
	      window.intervalWechatId = setInterval(this.checkIsPaid.bind(this, tradeSn), 2000);
	    }
	  }, {
	    key: 'checkIsPaid',
	    value: function checkIsPaid(tradeSn) {
	      _payment2["default"].getTrade(tradeSn).then(function (res) {
	        if (res.isPaid) {
	          location.href = res.paidSuccessUrl;
	        }
	      });
	    }
	  }]);
	
	  return WechatPayNative;
	}(_payment2["default"]);
	
	exports["default"] = WechatPayNative;

/***/ }),

/***/ "af463f59266a614cffe8":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var ajax = function ajax(options) {
	
	  var defaultOptions = {
	    async: true,
	    promise: true
	  };
	
	  options = Object.assign(defaultOptions, options);
	
	  var parameter = {
	    type: options.type || 'GET',
	    url: options.url || '',
	    dataType: options.dataType || 'json',
	    async: options.async,
	    beforeSend: function beforeSend(request) {
	      request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
	      request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
	    }
	  };
	
	  if (options.data) {
	    Object.assign(parameter, {
	      data: options.data
	    });
	  }
	
	  if (options.promise) {
	    return Promise.resolve($.ajax(parameter));
	  } else {
	    return $.ajax(parameter);
	  }
	};
	
	exports["default"] = ajax;

/***/ }),

/***/ "5eb223de522186da1dd9":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _course = __webpack_require__("f876a6f7a3b814e5ae39");
	
	var _course2 = _interopRequireDefault(_course);
	
	var _classroom = __webpack_require__("1b3e3e6763be2a155f42");
	
	var _classroom2 = _interopRequireDefault(_classroom);
	
	var _trade = __webpack_require__("c0f4981719a2ddce4be9");
	
	var _trade2 = _interopRequireDefault(_trade);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var API_URL_PREFIX = '/api'; /**
	                              * 使用说明
	                              * 
	                              * import Api from 'common/api';
	                              * 
	                              * Api.course.create({
	                              *    params: {},
	                              *    data: {}
	                              * }).then((res) => {
	                              *    // 请求成功
	                              *    console.log('res', res);
	                              * }).catch((res) => {
	                              *   // 异常捕获
	                              *   console.log('catch', res.responseJSON.error.message);
	                              * })
	                              */
	
	var Api = {
	  // 课程模块
	  course: (0, _course2["default"])(API_URL_PREFIX),
	  // 班级模块
	  classroom: (0, _classroom2["default"])(API_URL_PREFIX),
	  trade: (0, _trade2["default"])(API_URL_PREFIX)
	};
	
	exports["default"] = Api;

/***/ }),

/***/ "1b3e3e6763be2a155f42":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _ajax = __webpack_require__("af463f59266a614cffe8");
	
	var _ajax2 = _interopRequireDefault(_ajax);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var classroomModule = function classroomModule(api) {
	  return {
	    join: function join(options) {
	      return (0, _ajax2["default"])(Object.assign({
	        url: api + '/classrooms/' + options.params.classroomId + '/members',
	        type: 'POST'
	      }, options));
	    }
	  };
	};
	
	exports["default"] = classroomModule;

/***/ }),

/***/ "f876a6f7a3b814e5ae39":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _ajax = __webpack_require__("af463f59266a614cffe8");
	
	var _ajax2 = _interopRequireDefault(_ajax);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var courseModule = function courseModule(api) {
	  return {
	    get: function get(options) {
	      return (0, _ajax2["default"])(Object.assign({
	        url: api + '/courses/' + options.params.courseId
	      }, options));
	    }
	  };
	};
	
	exports["default"] = courseModule;

/***/ }),

/***/ "1af657f7645917c6310d":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _wechatpay_native = __webpack_require__("fef17b80bef935ad2682");
	
	var _wechatpay_native2 = _interopRequireDefault(_wechatpay_native);
	
	var _alipay_legacy_express = __webpack_require__("8d6b1145d2f0f7c079ac");
	
	var _alipay_legacy_express2 = _interopRequireDefault(_alipay_legacy_express);
	
	var _alipay_legacy_wap = __webpack_require__("e2fcc42cde2f41b58be2");
	
	var _alipay_legacy_wap2 = _interopRequireDefault(_alipay_legacy_wap);
	
	var _lianlianpay_wap = __webpack_require__("11e5fc7e9f7d4f25c86a");
	
	var _lianlianpay_wap2 = _interopRequireDefault(_lianlianpay_wap);
	
	var _lianlianpay_web = __webpack_require__("986a76353ac203061fe7");
	
	var _lianlianpay_web2 = _interopRequireDefault(_lianlianpay_web);
	
	var _wechatpay_js = __webpack_require__("a5753a9f265083dbf9c8");
	
	var _wechatpay_js2 = _interopRequireDefault(_wechatpay_js);
	
	var _wechatpay_mweb = __webpack_require__("02ad171abc9cada5bff7");
	
	var _wechatpay_mweb2 = _interopRequireDefault(_wechatpay_mweb);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var PaySDK = function () {
	  function PaySDK() {
	    _classCallCheck(this, PaySDK);
	  }
	
	  _createClass(PaySDK, [{
	    key: 'pay',
	    value: function pay(params) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	
	      var gateway = this.getGateway(params['payment'], params['isMobile'], params['openid']);
	      params.gateway = gateway;
	      var paySdk = null;
	      switch (gateway) {
	        case 'WechatPay_Native':
	          paySdk = this.wpn ? this.wpn : this.wpn = new _wechatpay_native2["default"]();
	          break;
	        case 'WechatPay_MWeb':
	          paySdk = this.wpm ? this.wpm : this.wpm = new _wechatpay_mweb2["default"]();
	          break;
	        case 'WechatPay_Js':
	          paySdk = this.wpj ? this.wpj : this.wpj = new _wechatpay_js2["default"]();
	          break;
	        case 'Alipay_LegacyExpress':
	          paySdk = this.ale ? this.ale : this.ale = new _alipay_legacy_express2["default"]();
	          break;
	        case 'Alipay_LegacyWap':
	          paySdk = this.alw ? this.alw : this.alw = new _alipay_legacy_wap2["default"]();
	          break;
	        case 'Lianlian_Wap':
	          paySdk = this.llwp ? this.llwp : this.llwp = new _lianlianpay_wap2["default"]();
	          break;
	        case 'Lianlian_Web':
	          paySdk = this.llwb ? this.llwb : this.llwb = new _lianlianpay_web2["default"]();
	          break;
	      }
	
	      paySdk.options = Object.assign({
	        'showConfirmModal': 1
	      }, options);
	
	      paySdk.pay(params);
	    }
	  }, {
	    key: 'getGateway',
	    value: function getGateway(payment, isMobile, openid) {
	      var gateway = '';
	      switch (payment) {
	        case 'wechat':
	          if (openid > 0) {
	            gateway = 'WechatPay_Js';
	          } else if (isMobile) {
	            gateway = 'WechatPay_MWeb';
	          } else {
	            gateway = 'WechatPay_Native';
	          }
	          break;
	
	        case 'alipay':
	          if (isMobile) {
	            gateway = 'Alipay_LegacyWap';
	          } else {
	            gateway = 'Alipay_LegacyExpress';
	          }
	          break;
	
	        case 'lianlianpay':
	          if (isMobile) {
	            gateway = 'Lianlian_Wap';
	          } else {
	            gateway = 'Lianlian_Web';
	          }
	          break;
	      }
	
	      return gateway;
	    }
	  }]);
	
	  return PaySDK;
	}();
	
	exports["default"] = PaySDK;

/***/ })

});
//# sourceMappingURL=index.js.map