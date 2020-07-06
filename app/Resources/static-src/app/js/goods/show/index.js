import Classroom from './Classroom';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Classroom)
}).$mount('#show-product-page');