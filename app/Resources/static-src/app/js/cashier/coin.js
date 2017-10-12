export default class Coin {
  constructor(props) {
    this.$container = props.$coinContainer;
    this.cashierForm = props.cashierForm;
    this.$form = props.$form;
    this.priceType = this.$container.data('priceType');
    this.coinRate = this.$container.data('coinRate');
    this.maxCoinInput = this.$container.data('maxAllowCoin') > this.$container.data('coinBalance') ? 
                        this.$container.data('coinBalance') : this.$container.data('maxAllowCoin');
    this.initEvent();
  }

  initEvent() {
    this.$form.on('change', '.js-coin-amount', event => this.changeAmount(event));
  }

  changeAmount(event) {
    let $this = $(event.currentTarget);
    let inputCoinNum = $this.val();
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
      let coinName = this.$form.data('coin-name');
      let price = 0.00;
      if (this.priceType === 'coin') {
        price = parseFloat(inputCoinNum).toFixed(2) + ' ' + coinName;

        let originalPirce = parseFloat(this.$container.data('maxAllowCoin'));
        let coinPrice = parseFloat(originalPirce - inputCoinNum).toFixed(2) + ' ' + coinName;;
        this.$form.trigger('changeCoinPrice', [coinPrice]);
      } else {
        price = '￥' + parseFloat(inputCoinNum / this.coinRate).toFixed(2);
      }
      this.$form.trigger('addPriceItem', ['coin-price', coinName + Translator.trans('order.create.minus'), price]);
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
