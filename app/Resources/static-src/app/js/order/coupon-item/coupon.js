import notify from 'common/notify';

class Coupon {
  constructor(props) {
    Object.assign(this, {
      
    }, props);

    this.$form = $(this.form);

    this.$couponCode = this.$form.find('input[name="couponCode"]');
    this.$productType = this.$form.find('input[name="targetType"]');
    this.$productId = this.$form.find('input[name="targetId"]');
    this.$price = this.$form.find('input[name="price"]');

    this.$errorMessage = this.$form.find('#coupon-error-message');

    this.$deductAmountLabel = this.$form.find('#deduct-amount-label');
    this.$couponCodeLabel = this.$form.find('#coupon-code-label');

    this.$selectCouponBtn = this.$form.find('#select-coupon-btn');

    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    const $form = this.$form;

    $form.on('click', '#use-coupon-btn', event => this.useCoupon(event));
    $form.on('click', '#cancel-use-coupon-btn', event => this.cancelCoupon(event));
    $form.on('change', 'input[name="couponCode"]', event => this.inputCode(event));

    this.selectCoupon();
  }

  inputCode(event) {
    const $this = $(event.currentTarget);

    if ($this.val()) {
      this.errorMessage();
    }
  }

  useCoupon(event) {
    const $this = $(event.currentTarget);
    const code = this.$couponCode.val();
    if (!code) {
      this.errorMessage(this.$couponCode.data('display'));
      return;
    }

    $this.button('loading');

    this.validate(event, (data) => {
      $this.button('reset');
      if (data.useable == 'no') {
        this.errorMessage(data.message);
      } else {
        const priceType = this.$form.data('price-type');
        const coinRate = this.$form.data('coin-rate');
        const coinName = this.$form.data('coin-name');

        let deductAmount = (data['type'] == 'discount') ? this.$price.val() * (1 - data['rate'] / 10) : data['rate'];
        
        if (priceType === 'coin') {
          deductAmount = parseFloat(parseFloat(deductAmount) * parseFloat(coinRate)).toFixed(2) + ' ' + coinName;
        } else {
          deductAmount = '￥' + parseFloat(deductAmount).toFixed(2);
        }

        this.useCouponAfter(deductAmount, code);
      }
    });
  }

  useCouponAfter(deductAmount, code) {
    this.$deductAmountLabel.text(deductAmount);
    this.$couponCodeLabel.text(code);

    this.toggleShow('use');

    this.$form.trigger('calculatePrice');
    this.$form.trigger('addPriceItem', ['coupon-price', Translator.trans('order.create.coupon_deduction'), deductAmount]);
  }

  cancelCoupon(event) {
    this.$couponCode.val('');
    this.$form.trigger('calculatePrice');
    this.$form.trigger('removePriceItem', ['coupon-price']);
    this.toggleShow('cancel');
  }

  errorMessage(text) {
    if (text) {
      this.$errorMessage.text(text).show();
      let $parent = this.$errorMessage.parent('.cd-form-group');
      if (!$parent.hasClass('has-error')) {
        $parent.addClass('has-error');
      }
    } else {
      this.$errorMessage.text('').hide().parent('.cd-form-group.has-error').removeClass('has-error');
    }
  }

  validate(event, callback) {
    const $this = $(event.currentTarget);

    const data = {
      'code': this.$couponCode.val(),
      'targetType': this.$productType.val(),
      'targetId': this.$productId.val(),
      'price': this.$price.val()
    };

    $.ajax({
      url: $this.data('url'),
      type: 'POST',
      data,
    }).done((data) => {
      if (typeof callback === 'function') {
        callback(data);
      }
    });
  }

  toggleShow(type) {
    const $selectCoupon = this.$form.find('#order-center-coupon__select');
    const $selectedCoupon = this.$form.find('#order-center-coupon__selected');

    if (type === 'use') {
      $selectCoupon.hide();
      $selectedCoupon.show();
    } else if (type === 'cancel') {
      $selectCoupon.show();
      $selectedCoupon.hide();
    }
  }

  selectCoupon() {
    cd.radio({
      el: '.js-existing-coupon',
    }).on('change', (event) => {
      const $this = $(event.currentTarget);
      const code = $this.data('code');
      const deductAmount = $this.data('deductAmount');
      this.$couponCode.val(code);

      this.$selectCouponBtn.trigger('click');

      this.useCouponAfter(deductAmount, code);
    });
  }

}

export default Coupon;