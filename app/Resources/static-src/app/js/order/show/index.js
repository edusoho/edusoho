class Order {
    constructor(props) {
       this.$element = props.element;
       this.$priceShow = this.$element.find('#price-show');
       this.initEvent();

    }

    initEvent() {
      this.$element.on('calculatePrice', event => this.calculatePrice(event));
      this.$element.trigger('calculatePrice');
    }

    calculatePrice() {
      let self = this;
      $.get(this.$element.data('priceCalculate'), this.$element.serialize(), function(data){
          self.$priceShow.text(data);
      })
    }

    
}

new Order(
  {
    element: $('#order-create-form')
  }
);