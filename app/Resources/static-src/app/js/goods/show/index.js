import Course from './Goods';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Course)
}).$mount('#show-product-page');