export default class Coin {
  constructor(props) {
    this.$container = props.$coinContainer;
    this.cashierForm = props.cashierForm;
    this.$form = props.$form;

    this.coinRate = this.$container.data('coin-rate');
    this.maxCoinInput = parseFloat(this.$container.data('maxAllowCoin')) > parseFloat(this.$container.data('coinBalance')) ? parseFloat(this.$container.data('coinBalance')) : parseFloat(this.$container.data('maxAllowCoin'));
    this.initEvent();
  }

  initEvent() {
    this.$form.on('change', '.js-coin-amount', event => this.changeAmount(event));
  }

  changeAmount(event) {
    let $this = $(event.currentTarget);
    let inputCoinNum = parseFloat($this.val());
    if (isNaN(inputCoinNum) || inputCoinNum <= 0) {
      inputCoinNum = 0;
      $this.val(inputCoinNum);
      this.removePasswordValidate();
      
      this.$form.trigger('removePriceItem', ['coin-price']);
      this.cashierForm.calcPayPrice(inputCoinNum);
    }

    if (inputCoinNum > this.maxCoinInput) {
      inputCoinNum = this.maxCoinInput;
      $this.val(inputCoinNum);
    }

    if (inputCoinNum > 0) {
      this.addPasswordValidate();
      let coinName = this.$form.data('coin-name');
      this.$form.trigger('addPriceItem', ['coin-price', coinName + Translator.trans('order.create.minus'), '￥' + parseFloat(inputCoinNum / this.coinRate).toFixed(2) ]);
      this.cashierForm.calcPayPrice(inputCoinNum);
    }
  }

  addPasswordValidate() {
    this.$container.find('[name="payPassword"]').rules('add', 'required passwordCheck');
  }

  removePasswordValidate() {
    this.$container.find('[name="payPassword"]').rules('remove', 'required passwordCheck');
  }
}
