class Order {
    constructor(props) {
       this.$element = props.element;
       this.$priceShow = this.$element.find('#price-show');
       this.initEvent();

    }

    initEvent() {
      this.$element.on('priceCalculate', event => this.priceCalculate(event));
    }

    priceCalculate() {
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