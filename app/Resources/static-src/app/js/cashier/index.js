import Coin from './coin';
import PaySDK from './pay/sdk';

class CashierForm {

  constructor($form) {
    this.$container = $form;

    this.validator = this.$container.validate();

    this.initEvent();
    this.initCoin();

    this.paySdk = new PaySDK();
  }

  initCoin() {
    let $coin = $('#coin-use-section');
    if ($coin.length > 0) {
      this.coin = new Coin($coin, this);
    }
  }

  calcPayPrice(coinAmount) {

    let self = this;
    $.post(this.$container.data('priceUrl'), {
      coinAmount: coinAmount
    }, resp => {
      self.$container.find('.js-pay-price').text(resp.data);
    });

  }

  initEvent() {
    // 支付方式切换
    this.$container.on('click', '.check', event => {
      let $this = $(event.currentTarget);
      if (!$this.hasClass('active') && !$this.hasClass('disabled')) {
        $this.addClass('active').siblings().removeClass('active');
        $("input[name='payment']").val($this.attr("id"));
      }
    });

    let $form = this.$container;
    let self = this;
    $form.on('click', '.js-pay-btn', event => {

      if ($form.valid()) {

        let params = self.formDataToObject($form);

        params.payAmount = self.$container.find('.js-pay-price').text();
        self.paySdk.pay(params);
      }

    });
  }

  formDataToObject($form) {

    let params = {},
      formArr = $form.serializeArray();
    for (let index in formArr) {
      params[formArr[index].name] = formArr[index].value;
    }

    return params;
  }
}


new CashierForm($('#cashier-form'));




