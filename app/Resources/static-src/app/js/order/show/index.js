class Order {
    constructor(props) {
       this.$element = props.element;
       this.$priceShow = this.$element.find('#price-show');
       this.initEvent();
    }

    initEvent() {
      this.$element.on('calculatePrice', event => this.calculatePrice(event));
      this.$element.trigger('calculatePrice');
      this.validate();
    }

    calculatePrice() {
      let self = this;
      let formData = this.$element.serializeArray();
      $.get(this.$element.data('priceCalculate'), formData, function(data){
          self.$priceShow.text(data);
      })
    }

    validate() {
       this.$element.submit( event => {
         $('#order-create-btn').button('loading');
         return true;
       });
    }
    
}

new Order(
  {
    element: $('#order-create-form')
  }
);