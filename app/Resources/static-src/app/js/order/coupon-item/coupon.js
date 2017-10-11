import notify from 'common/notify';

class Coupon {
  constructor(props) {
    Object.assign(this, {
      
    }, props);

    this.$element = $(this.element);
    this.$form = $(this.form);

    this.$couponCode = this.$element.find('input[name="couponCode"]');
    this.$productType = this.$form.find('input[name="targetType"]');
    this.$productId = this.$form.find('input[name="targetId"]');
    this.$price = this.$form.find('input[name="price"]');
    this.$errorMessage = this.$form.find('#coupon-error-message');
    this.$deductAmount = this.$form.find('#deduct-amount');

    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    const $element = this.$element;

    $element.on('click', '#use-coupon-btn', event => this.useCoupon(event));
    $element.on('click', '#cancel-use-coupon-btn', event => this.cancelCoupon(event));
    $element.on('change', 'input[name="couponCode"]', event => this.inputCode(event));

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

        let deductAmount = (data['type'] == 'discount') ? this.$price.val() * data['rate'] : data['rate'];
        
        if (priceType === 'coin') {
          deductAmount = parseFloat(parseFloat(deductAmount) * parseFloat(coinRate)).toFixed(2) + ' ' + coinName;
        } else {
          deductAmount = '￥' + deductAmount;
        }

        this.$deductAmount.text(deductAmount);

        this.$form.find('#coupon-code').text(code);
        this.toggleShow('use');

        this.$form.trigger('calculatePrice');
        this.$form.trigger('addPriceItem', ['coupon-price', Translator.trans('order.create.coupon_deduction'), deductAmount]);
      }
    })
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
    const $selectCoupon = this.$element.find('#order-center-coupon__select');
    const $selectedCoupon = this.$element.find('#order-center-coupon__selected');

    if (type === 'use') {
      $selectCoupon.hide();
      $selectedCoupon.show();
    } else if (type === 'cancel') {
      $selectCoupon.show();
      $selectedCoupon.hide();
    }
  }

  selectCoupon() {
    const $couponCode = this.$element.find("input[name='couponCode']");
    const $selectCouponBtn = this.$element.find("#select-coupon-btn");
    const $useCouponBtn = this.$element.find('#use-coupon-btn')
    
    cd.radio({
      el: '.js-existing-coupon',
      cb(event) {
        const $this = $(event.currentTarget);
        const code = $this.data('code');
        $couponCode.val(code);

        $selectCouponBtn.trigger('click');
        $useCouponBtn.trigger('click');
      }
    });
  }

}

export default Coupon;