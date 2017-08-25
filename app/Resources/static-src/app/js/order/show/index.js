class Order {
    constructor(props) {
       this.$element = props.element;
       this.initEvent();

    }

    initEvent() {
      this.$element.on('priceCalculate', event => this.priceCalculate(event));
    }

    priceCalculate() {
      $.get(this.$element.data('priceCalculate'), this.$element.serialize(), function(data){
      })
    }

    
}

new Order(
  {
    element: $('#order-create-form')
  }
);