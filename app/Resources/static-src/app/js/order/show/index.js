class Order {
  constructor(props) {
    this.$element = $(props.element);
    this.$realpayPrice = this.$element.find('#realpay-price');
    this.$priceList = this.$element.find('#order-center-price-list');

    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.$element.on('calculatePrice', event => this.calculatePrice(event));
    this.$element.on('addPriceItem', (event, id, title, price) => this.addPriceItem(event, id, title, price));

    this.$element.trigger('calculatePrice');
    this.validate();
  }

  calculatePrice() {
    
    let formData = this.$element.serializeArray();
    $.get(this.$element.data('priceCalculate'), formData, (data) => {
      this.$realpayPrice.text(data);
    })
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

  validate() {
    this.$element.submit( event => {
      $('#order-create-btn').button('loading');
      return true;
    });
  }
}

new Order({
  element: '#order-create-form'
});