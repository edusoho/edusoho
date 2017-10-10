import Coin from './coin';
import PaySDK from './pay/sdk';

class CashierForm {
  constructor(props) {
    this.$form = $(props.element);
    this.$priceList = this.$form.find('#order-center-price-list');

    this.validator = this.$form.validate();

    this.initEvent();
    this.initCoin();

    this.paySdk = new PaySDK();
  }

  initCoin() {
    let $coin = $('#coin-use-section');
    if ($coin.length > 0) {
      this.coin = new Coin({
        $coinContainer: $coin,
        cashierForm: this,
        $form: this.$form
      });
    }
  }

  initEvent() {
    let $form = this.$form;

    $form.on('click', '.js-pay-type', event => this.switchPayType(event));
    $form.on('click', '.js-pay-btn', event => this.payOrder(event));
    $form.on('addPriceItem', (event, id, title, price) => this.addPriceItem(event, id, title, price));
  }

  payOrder(event) {
    let $form = this.$form;

    if ($form.valid()) {
      let $btn = $(event.currentTarget);
      $btn.button('loading');
      let params = this.formDataToObject($form);

      params.payAmount = $form.find('.js-pay-price').text();
      this.paySdk.pay(params);
      $btn.button('reset');
    }
  }

  switchPayType(event) {
    let $this = $(event.currentTarget);
    if (!$this.hasClass('active')) {
      $this.addClass('active').siblings().removeClass('active');
      $("input[name='payment']").val($this.attr("id"));
    }
  }

  calcPayPrice(coinAmount) {
    $.post(this.$form.data('priceUrl'), {
      coinAmount
    }).done((res) => {
      this.$form.find('.js-pay-price').text(res.data);
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

  addPriceItem(event, id, title, price) {
    let html = `
      <div class="order-center-price" id="${id}">
        <div class="order-center-price__title">${title}</div>
        <div class="order-center-price__content">-${price}</div>
      </div>
    `;

    let $priceItem = $(`#${id}`);
    if ($priceItem.length) {
      $priceItem.remove();
      if (!price) {
        return;
      }
    }

    this.$priceList.append(html);
  }
}


new CashierForm({
  element: '#cashier-form'
});




