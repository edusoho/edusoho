import Course from './Course';

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Course)
}).$mount('#show-product-page');