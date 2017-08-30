import Coin from './coin';
import notify from 'common/notify';

class CashierForm {
  constructor($form) {
    this.$container = $form;

    this.validator = this.$container.validate();

    this.initEvent();
    this.initCoin();
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
    this.$container.on('click', '.check', function () {
      let $this = $(this);
      if (!$this.hasClass('active') && !$this.hasClass('disabled')) {
        $this.addClass('active').siblings().removeClass('active');
        $("input[name='payment']").val($this.attr("id"));
      }

    });

  }
}


new CashierForm($('#cashier-form'));




