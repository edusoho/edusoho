import Goods from './Goods';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Goods,{
    props: {
      currentUserId: $('#show-product-page').data('currentUserId')
    },
  })
}).$mount('#show-product-page');