class Coupon {
  constructor(props) {
    this.$element = props.element;
    this.$showDeductAmount = this.$element.find('#show-deduct-amount');
    this.$noUseCouponCode = this.$element.find('#no-use-coupon-code');
    this.$couponCode = this.$element.find("input[name='couponCode']");
    this.$selectCoupon = this.$element.find('#coupon-select');
    this.$couponNotify =  this.$element.find('#code-notify');
    this.initEvent();
  }

  initEvent() {
    const $element = this.$element;
    $element.on('change', '#coupon-select', event => this.couponSelect(event));
    $element.on('click', '#change-coupon-code', event => this.showChangeCoupon(event));
    $element.on('click', '#cancel-coupon', event => this.cancelCoupon(event));
    $element.on('click', '#check-coupon', event => this.checkCoupon(event));

    this.$selectCoupon.trigger('change');
  }

  couponSelect(event) {
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
    $.get($('#order-create-form').data('priceCalculate'),$('#order-create-form').serialize(), function(data){
      console.log(data);
    })
    console.log(444);
  }

  showChangeCoupon(event) {
    const $this = $(event.currentTarget);
    this.showDeductAmount();
    this.showCouponCode();

    this.$couponNotify.text("").removeClass('alert-success');
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
    this.checkCoupon();
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

  checkCoupon() {
    let self = this;
    let code = this.$couponCode.val();
    if (!this.$productType) {
      this.$productType = $("input[name='targetType']");
    }
    if (!this.$productId) {
      this.$productId = $("input[name='targetId']");
    }
    if (!code) {
        self.$couponNotify.css("display","none");
        return;
    }
    $.post($('#check-coupon').data('url'),{'code': code,'targetType':this.$productType.val(),'targetId': this.$productId.val()} ,function(data){
        if(data.useable == 'no'){
          self.$couponNotify.addClass('alert-danger').text(data.message).css("display","inline-block");
        } else {
          let text = data['type'] == 'discount' ? Translator.trans('order.create.use_discount_coupon_hint', {rate: data['rate']}) : Translator.trans('order.create.use_price_coupon_hint', {rate: data['rate']});
          self.$couponNotify.removeClass('alert-danger').addClass("alert-success").text(text).css("display","inline-block");
        }
    })
  }
}


new Coupon({
  element: $('#coupon-deducts')
});