import Coin from 'app/js/cashier/coin';
import notify from 'common/notify';
import PaySDK from 'app/js/cashier/pay/sdk';

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
      self.paySdk.pay();
      // if ($form.valid()) {
      //
      //   let $modal = $('#modal');
      //   $.post($form.attr('action'), $form.serialize(), resp => {
      //     $modal.html('');
      //     if (resp.showQrcode) {
      //       $modal.load(resp.redirectUrl).modal('show');
      //
      //     } else if (resp.isPaid) {
      //       location.href = resp.redirectUrl;
      //     } else {
      //       //display modal
      //       let url = $form.find('.js-pay-btn').data('url');
      //       $modal.load(url).modal('show');
      //       window.open(resp.redirectUrl);
      //     }
      //
      //   }).fail(resp => {
      //     notify('danger', Translator.trans('cashier.pay.error_message'));
      //   });
      // }

    });
  }
}


new CashierForm($('#cashier-form'));




