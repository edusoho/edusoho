export default class Coin {
  constructor(props) {
    this.$container = props.$coinContainer;
    this.cashierForm = props.cashierForm;
    this.$form = props.$form;
    this.priceType = this.$container.data('priceType');
    this.coinRate = this.$container.data('coinRate');
    this.maxCoinInput = this.$container.data('maxAllowCoin') > this.$container.data('coinBalance') ?
      this.$container.data('coinBalance') : this.$container.data('maxAllowCoin');

    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.$form.on('change', '.js-coin-amount', event => this.changeAmount(event));
  }

  changeAmount(event) {
    let $this = $(event.currentTarget);
    let inputCoinNum = $this.val();

    if (parseFloat(inputCoinNum) > parseFloat(this.maxCoinInput)) {
      inputCoinNum = this.maxCoinInput;
    }

    if (isNaN(parseFloat(inputCoinNum)) || parseFloat(inputCoinNum) <= 0) {
      inputCoinNum = 0;
      $this.val('');
      this.removePasswordValidate();

      this.$form.trigger('removePriceItem', ['coin-price']);

      if ($('.js-no-payment').length) {
        $('.js-no-payment').attr('disabled', 'disabled');
        $('.js-no-payment').addClass('cd-btn-default');
        $('.js-no-payment').removeClass('cd-btn-primary');
      }

      this.cashierForm.calcPayPrice(inputCoinNum);
      return;
    }

    $this.val(parseFloat(inputCoinNum).toFixed(2));

    this.addPasswordValidate();
    let coinName = this.$form.data('coin-name');
    let price = 0.00;
    if (this.priceType === 'coin') {
      price = parseFloat(inputCoinNum).toFixed(2) + ' ' + coinName;

      let originalPirce = parseFloat(this.$container.data('maxAllowCoin'));
      let coinPrice = parseFloat(originalPirce - inputCoinNum).toFixed(2) + ' ' + coinName;
      this.$form.trigger('changeCoinPrice', [coinPrice]);
    } else {
      price = '￥' + parseFloat(inputCoinNum / this.coinRate).toFixed(2);
    }
    this.$form.trigger('addPriceItem', ['coin-price', coinName + Translator.trans('order.create.minus'), price]);

    if ($('.js-no-payment').length) {
      $('.js-no-payment').attr('disabled', 'disabled');
      $('.js-no-payment').addClass('cd-btn-default');
      $('.js-no-payment').removeClass('cd-btn-primary');
    }

    this.cashierForm.calcPayPrice(inputCoinNum);
  }

  addPasswordValidate() {
    this.$container.find('[name="payPassword"]').rules('add', 'required es_remote');
  }

  removePasswordValidate() {
    this.$container.find('[name="payPassword"]').rules('remove', 'required es_remote');
  }
}