export default class Coin {
  constructor(props) {
    this.$container = props.$coinContainer;
    this.cashierForm = props.cashierForm;
    this.$form = props.$form;

    this.coinRate = this.$container.data('coin-rate');
    this.maxCoinInput = this.$container.data('maxAllowCoin') > this.$container.data('coinBalance') ? this.$container.data('coinBalance') : this.$container.data('maxAllowCoin');
    this.initEvent();
  }

  initEvent() {
    this.$container.on('blur', '.js-coin-amount', event => this.changeAmount(event));
  }

  changeAmount(event) {
    let $this = $(event.currentTarget);
    let inputCoinNum = $this.val();

    if (isNaN(inputCoinNum) || inputCoinNum <= 0) {
      $this.val(0);
      this.removePasswordValidate();
      
      this.$form.trigger('addPriceItem', ['coin-price']);
      this.cashierForm.calcPayPrice($this.val());
    }

    if ($this.val() > this.maxCoinInput) {
      $this.val(this.maxCoinInput);
    }

    if ($this.val() > 0) {
      this.addPasswordValidate();
      this.$form.trigger('addPriceItem', ['coin-price', '虚拟币抵扣', '￥' + parseFloat($this.val() / this.coinRate).toFixed(2) ]);
      this.cashierForm.calcPayPrice($this.val());
    }
  }

  addPasswordValidate() {
    this.$container.find('[name="payPassword"]').rules('add', 'required passwordCheck');
  }

  removePasswordValidate() {
    this.$container.find('[name="payPassword"]').rules('remove', 'required passwordCheck');
  }
}
