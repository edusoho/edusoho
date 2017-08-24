class Coupon {
  constructor(props) {
    this.$element = props.element;
    this.$showDeductAmount = this.$element.find('#show-deduct-amount');
    this.$noUseCouponCode = this.$element.find('#no-use-coupon-code');
    this.$couponCode = this.$element.find("input[name='couponCode']");
    this.$selectCoupon = this.$element.find('#coupon-select');
    this.initEvent();
  }

  initEvent() {
    const $element = this.$element;
    $element.on('change', '#coupon-select', event => this.couponSelectEvent(event));
    $element.on('click', '#change-coupon-code', event => this.showChangeCoupon(event));
    $element.on('click', '#cancel-coupon', event => this.cancelCoupon(event));

    this.$selectCoupon.trigger('change');
  }

  couponSelectEvent(event) {
    const $this = $(event.currentTarget);
    const coupon = $this.find('option:selected');
    const val = $this.val();

    if (!val) {
      this.selectEmptyCoupon();
      return;
    }

    this.setCoupon(val);
    this.showDeductAmount(coupon.data('deductAmount'));
    this.$noUseCouponCode.hide();
  }

  showChangeCoupon(event) {
    const $this = $(event.currentTarget);
    this.showDeductAmount();
    this.showCouponCode();

    $('#code-notify').text("").removeClass('alert-success');
    this.setCoupon().focus();
    
  }

  showDeductAmount(amount = this.$showDeductAmount.data('placeholder')) {
    //显示优惠码优惠的金额
    this.$showDeductAmount.text(amount);
  }

  showCouponCode() {
    //显示手动输入优惠码框,隐藏select
    $('#coupon-code').show();
    $('#select-coupon-box').hide();
  }

  hideCouponCode() {
    //隐藏手动输入优惠码框，显示select
    $('#coupon-code').hide();
    $('#select-coupon-box').show();
  }

  setCoupon(value = '') {
    //设置选择的优惠码code
    this.$couponCode.val(value);
    return this.$couponCode;
  }

  cancelCoupon(event) {
    this.hideCouponCode();
    this.$selectCoupon.trigger('change');
  }

  selectEmptyCoupon() {
    this.$noUseCouponCode.show();
    this.setCoupon();
    this.showDeductAmount();
  }
}


new Coupon({
  element: $('#coupon-deducts')
});