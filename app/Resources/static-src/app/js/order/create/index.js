import notify from 'common/notify';

let utils = {
  divition(x, y) {
    return Math.round(Math.round(x * 1000) / Math.round(y * 1000) * 1000) / 1000;
  },
  multiple(x, y) {
    return Math.round(Math.round(x * 100) * Math.round(y * 100)) / 10000;
  },
  subtract(x, y) {
    return Math.round(Math.round(x * 1000) - Math.round(y * 1000)) / 1000;
  },
  moneyFormatFloor(value) {
    // 转化成字符串
    let tempValue = value + '';
    tempValue = parseInt(Math.round(tempValue * 1000));
    // 抹去最后１位
    tempValue = parseInt(tempValue / 10) * 10 / 1000;
    return tempValue.toFixed(2);
  },
  moneyFormatCeil(value) {
    let tempValue = value + '';
    tempValue = parseFloat(tempValue).toFixed(3);
    let length = tempValue.length;
    if (tempValue.substr(length - 1, 1) === '0') {
      return this.moneyFormatFloor(tempValue);
    }
    return this.moneyFormatFloor(parseFloat(tempValue) + 0.01);
  }
};


class OrderCreate {
  constructor(props) {
    this.element = $(props.element);
    this.submitBtn = '#order-create-btn';
    this.validator = null;
    this.coinSetting = JSON.parse(this.element.find('.js-coin-setting ').text());
    this.init();
  }

  init() {
    this.initEvent();
    this.validator = this.element.validate({
      currentDom: this.submitBtn,
    });

    let couponDefaultSelect = $('#coupon-select').val();
    if (couponDefaultSelect != '') {
      let couponCode = $('[role="coupon-code-input"]');
      couponCode.val(couponDefaultSelect);
      $('button[role="coupon-use"]').trigger('click');
    }

    let totalPrice = parseFloat($('[role="total-price"]').text());
    let _this = this;
    if ($('[role="coinNum"]').length > 0) {
      let coinNum = $('[role="coinNum"]').val();
      if (isNaN(coinNum) || coinNum <= 0) {
        $(this).val('0.00');
        _this.coinPriceZero();
      } else {
        _this.showPayPassword();
      }

      if (_this.coinSetting.price_type == 'RMB') {
        let discount = utils.divition(coinNum, _this.coinSetting.cash_rate);
        if (totalPrice < discount) {
          discount = totalPrice;
        }
        $('[role="cash-discount"]').text(utils.moneyFormatFloor(discount));
        totalPrice = utils.subtract(totalPrice, discount);
      } else {
        $('[role="cash-discount"]').text(utils.moneyFormatFloor(coinNum));
        totalPrice = utils.subtract(totalPrice, coinNum);
      }
    } else {
      $('[role="cash-discount"]').text('0.00');
    }

    this.shouldPay(totalPrice);

    if ($('#js-order-create-sms-btn').length > 0) {
      let self = this;

      $('#js-order-create-sms-btn').click(function(e) {

        let coinToPay = $('#coinPayAmount').val();
        if (coinToPay && (coinToPay.length > 0) && (!isNaN(coinToPay)) && (coinToPay > 0) && ($('#js-order-create-sms-btn').length > 0)) {
          $('#payPassword').trigger('change');
          if ($('[role="password-input"]').find('span[class="text-danger"]').length > 0) {
            e.stopPropagation();
          }

          if (self.validator && self.validator.form()) {
            let $this = $(this);
            let url = $this.data('url');
            let $target = $($this.attr('data-target'));
            $target.modal().load(url);
          }

        } else {
          e.stopPropagation();
          $('#order-create-form').submit();
        }
      });
    }
  }

  initEvent() {
    const $node = this.element;
    $node.on('blur', '[role="coinNum"]', event => this.coinNumEvent(event));
    $node.on('click', '#coupon-code-btn', event => this.couponCodeEvent(event));
    $node.on('click', '[role="cancel-coupon"]', event => this.couponCancelEvent(event));
    $node.on('click', 'button[role="coupon-use"]', event => this.couponUseEvent(event));
    $node.on('change', '#coupon-select', event => this.couponSelectEvent(event));
    $node.on('click', this.submitBtn, event => this.formSubmitEvent(event));
  }

  formSubmitEvent(event) {
    if (this.validator && this.validator.form()) {
      this.element.submit();
    }
  }

  couponSelectEvent(event) {
    const $this = $(event.currentTarget);
    const coupon = $this.children('option:selected');
    if (coupon.data('code') == '') {
      $('[role=no-use-coupon-code]').show();
      $('[role="cancel-coupon"]').trigger('click');
      return;
    } else {
      $('[role=no-use-coupon-code]').hide();
    }
    let couponCode = $('[role="coupon-code-input"]');
    couponCode.val(coupon.data('code'));
    $('button[role="coupon-use"]').trigger('click');
    $('[role="code-notify"]').removeClass('alert-success');
  }

  couponUseEvent(event) {
    let data = {};
    let couponCode = $('[role="coupon-code-input"]');
    data.code = couponCode.val();

    if (data.code == '') {
      $('[role="coupon-price-input"]').find('[role=\'price\']').text('0.00');
      return;
    }

    data.targetType = couponCode.data('targetType');
    data.targetId = couponCode.data('targetId');

    let totalPrice = parseFloat($('[role="total-price"]').text());

    data.amount = totalPrice;
    let _this = this;
    $.post('/' + data.targetType + '/' + data.targetId + '/coupon/check', data, function(data) {
      $('[role="code-notify"]').css('display', 'inline-block');
      if (data.useable == 'no') {

        $('[role=no-use-coupon-code]').show();
        $('[role="code-notify"]').removeClass('alert-success').addClass('alert-danger').html(Translator.trans('order.create.useless_hint'));

      } else if (data.useable == 'yes') {
        $('[role=no-use-coupon-code]').hide();

        if (data['type'] == 'discount') {
          $('[role="code-notify"]').removeClass('alert-danger').addClass('alert-success').text(Translator.trans('order.create.use_discount_coupon_hint', { rate: data['rate'] }));
        } else {
          $('[role="code-notify"]').removeClass('alert-danger').addClass('alert-success').text(Translator.trans('order.create.use_price_coupon_hint', { rate: data['rate'] }));
        }

        $('[role="coupon-price"]').find('[role=\'price\']').text(utils.moneyFormatFloor(data.decreaseAmount));

        $('[role="coupon-code-verified"]').val(couponCode.val());
      }

      _this.conculatePrice();
    });
  }

  couponCancelEvent(event) {
    if ($('#coupon-select').val() != '') {
      let couponDefaultSelect = $('#coupon-select').val();
      let couponCode = $('[role="coupon-code-input"]');
      couponCode.val(couponDefaultSelect);
      $('button[role="coupon-use"]').trigger('click');
    }

    $('[role="coupon-code"]').hide();
    // $('[role="no-use-coupon-code"]').show();
    $('#coupon-code-btn').show();
    $('[role="null-coupon-code"]').show();
    $('[role="code-notify"]').hide();
    $('[role="coupon-price"]').find('[role=\'price\']').text('0.00');
    $('[role="code-notify"]').text('');
    $('[role="coupon-code"]').val('');
    $(this).hide();
    $('[role="coupon-code-verified"]').val('');
    $('[role="coupon-code-input"]').val('');

    this.conculatePrice();
  }

  coinNumEvent(event) {
    const $this = $(event.currentTarget);
    let coinNum = $this.val();
    coinNum = Math.round(coinNum * 100) / 100;
    $this.val(coinNum);

    if (isNaN(coinNum) || coinNum <= 0) {
      $this.val('0.00');
      this.coinPriceZero();
    } else {
      this.showPayPassword();
    }
    this.conculatePrice();
  }

  couponCodeEvent(event) {
    const $this = $(event.currentTarget);
    // $('[role="cancel-coupon"]').trigger('click');
    $('[role="coupon-price"]').find('[role=\'price\']').text('0.00');
    $('[role="code-notify"]').text('').removeClass('alert-success');
    $('[role="coupon-code"]').val('');
    $('[role="cancel-coupon"]').hide();
    $('[role="coupon-code-verified"]').val('');
    $('[role="coupon-code-input"]').val('');
    this.conculatePrice();
    $('[role="coupon-code"]').show();
    $('[role="coupon-code-input"]').focus();
    // $('[role="no-use-coupon-code"]').hide();
    $('[role="cancel-coupon"]').show();
    $('[role="null-coupon-code"]').hide();

    // $('[role="code-notify"]').show();
    $this.hide();
  }

  afterCouponPay(totalPrice) {
    let couponTotalPrice = $('[role="coupon-price"]').find('[role=\'price\']').text();
    if ($.trim(couponTotalPrice) == '' || isNaN(couponTotalPrice)) {
      couponTotalPrice = 0;
    }
    if (totalPrice < couponTotalPrice) {
      couponTotalPrice = totalPrice;
    }
    totalPrice = utils.subtract(totalPrice, couponTotalPrice);
    return totalPrice;
  }

  afterCoinPay(coinNum) {
    let accountCash = $('[role="accountCash"]').text();

    if (accountCash == '' || isNaN(accountCash) || parseFloat(accountCash) == 0) {
      this.coinPriceZero();
      return 0;
    }

    let coin = Math.round(accountCash * 1000) > Math.round(coinNum * 1000) ? coinNum : accountCash;

    if (this.coinSetting.price_type == 'RMB') {
      let totalPrice = parseFloat($('[role="total-price"]').text());
      let cashDiscount = Math.round(utils.moneyFormatFloor(utils.divition(coin, this.coinSetting.cash_rate)) * 100) / 100;

      if (totalPrice < cashDiscount) {
        cashDiscount = totalPrice;
      }

      $('[role="cash-discount"]').text(utils.moneyFormatFloor(cashDiscount));
    } else {
      $('[role="cash-discount"]').text(utils.moneyFormatFloor(coin));
    }
    return coin;

  }

  getMaxCoinCanPay(totalCoinPrice) {
    let maxCoin = parseFloat($('[role="maxCoin"]').text());
    let maxCoinCanPay = totalCoinPrice < maxCoin ? totalCoinPrice : maxCoin;
    let myCashAccount = $('[role="accountCash"]');

    if (myCashAccount.length > 0) {
      let myCash = parseFloat(myCashAccount.text() * 100) / 100;
      maxCoinCanPay = maxCoinCanPay < myCash ? maxCoinCanPay : myCash;
    }

    return maxCoinCanPay;
  }

  shouldPay(totalPrice) {
    totalPrice = Math.round(totalPrice * 1000) / 1000;

    if (this.coinSetting.price_type == 'RMB') {
      totalPrice = utils.moneyFormatCeil(totalPrice);
      $('[role="pay-rmb"]').text(totalPrice);
      $('input[name="shouldPayMoney"]').val(totalPrice);

    } else {
      let payRmb = utils.moneyFormatCeil(utils.divition(totalPrice, this.coinSetting.cash_rate));
      let shouldPayMoney = Math.round(payRmb * 100) / 100;

      $('[role="pay-coin"]').text(totalPrice);
      $('[role="pay-rmb"]').text(shouldPayMoney);
      $('input[name="shouldPayMoney"]').val(shouldPayMoney);
    }
  }

  conculatePrice() {
    let totalPrice = parseFloat($('[role="total-price"]').text()),
      totalCoinPrice = 0,
      cashDiscount = $('[role="cash-discount"]').text(),
      coinNumPay = $('[role="coinNum"]').val();

    totalPrice = this.afterCouponPay(totalPrice);

    let cashModel = this.coinSetting.cash_model;

    switch (cashModel) {
    case 'none':
      totalPrice = totalPrice >= 0 ? totalPrice : 0;
      this.shouldPay(totalPrice);
      break;
    case 'deduction':
      totalCoinPrice = utils.multiple(totalPrice, this.coinSetting.cash_rate);
      totalCoinPrice = utils.moneyFormatCeil(totalCoinPrice);
      var maxCoinCanPay = this.getMaxCoinCanPay(totalCoinPrice);

      if (maxCoinCanPay <= parseFloat(coinNumPay)) {
        coinNumPay = maxCoinCanPay;
      }

      $('[role="coinNum"]').val(coinNumPay);

      if (coinNumPay == 0) {
        this.coinPriceZero();
      }

      if (coinNumPay && $('[name="payPassword"]').length > 0) {
        coinNumPay = this.afterCoinPay(coinNumPay);
        totalPrice = utils.subtract(totalPrice, cashDiscount);

      } else {
        $('[role="coinNum"]').val(0);
        $('[role="cash-discount"]').text('0.00');
      }

      totalPrice = totalPrice >= 0 ? totalPrice : 0;
      this.shouldPay(totalPrice);
      break;
    case 'currency':
      totalCoinPrice = totalPrice;

      if (totalCoinPrice <= parseFloat(coinNumPay)) {
        coinNumPay = totalCoinPrice;
      }

      $('[role="coinNum"]').val(coinNumPay);

      if (coinNumPay == 0) {
        this.coinPriceZero();
      }

      if (coinNumPay && $('[name="payPassword"]').length > 0) {
        coinNumPay = this.afterCoinPay(coinNumPay);
        totalPrice = utils.subtract(totalPrice, cashDiscount);
      } else {
        $('[role="coinNum"]').val(0);
        $('[role="cash-discount"]').text('0.00');
      }

      totalPrice = totalPrice >= 0 ? totalPrice : 0;
      this.shouldPay(totalPrice);
      break;
    }
  }

  coinPriceZero() {
    $('[role="coinNum"]').val(0);
    $('[role="cash-discount"]').data('defaultValue');
    $('[role=\'password-input\']').hide();
    $('[name="payPassword"]').rules('remove', 'required es_remote');
  }

  showPayPassword() {
    $('[role=\'password-input\']').show();
    $('[name="payPassword"]').rules('add', { required: true, es_remote: true });
  }
}

new OrderCreate({
  element: '#order-create-form'
});