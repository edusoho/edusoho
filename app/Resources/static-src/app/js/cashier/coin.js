export default class Coin {
  constructor($coinContainer, cashierForm) {
    this.$container = $coinContainer;
    this.cashierFrom = cashierForm;
    this.maxCoinInput = this.$container.data('maxAllowCoin') > this.$container.data('coinBalance') ? this.$container.data('coinBalance') : this.$container.data('maxAllowCoin');
    this.initEvent();
  }

  validate() {

  }

  initEvent() {
    let self = this;
    this.$container.on('blur', '.js-coin-amount', event => {
      let $this = $(event.currentTarget);
      let inputCoinNum = $this.val();
      if(isNaN(inputCoinNum) || inputCoinNum <= 0){
        $this.val(0);
        self.hidePasswordInput();
      }

      if ($this.val() > self.maxCoinInput) {
        $this.val(self.maxCoinInput);
      }

      if ($this.val() > 0) {
        self.showPasswordInput();
        self.cashierFrom.calcPayPrice($this.val());
      }

    });

  }

  showPasswordInput() {
    this.$container.find('[name="payPassword"]').rules('add', { required: true, passwordCheck: true});
    this.$container.find('.js-pay-password').closest('div').show();
  }

  hidePasswordInput() {
    this.$container.find('[name="payPassword"]').rules('remove','required passwordCheck');
    this.$container.find('.js-pay-password').closest('div').hide();
  }

}
