import Vue from 'vue';
import itemBank from 'item-bank-test';
import Review from './review';

Vue.use(itemBank);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Review)
}).$mount('#app');