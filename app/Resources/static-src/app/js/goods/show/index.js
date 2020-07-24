import Goods from './Goods';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Goods,{
    props: {
      currentUserId: $('#show-product-page').data('currentUserId'),
      targetId: $('#show-product-page').data('targetId'),
      isUserLogin: $('#show-product-page').data('isUserLogin'),
    },
  })
}).$mount('#show-product-page');