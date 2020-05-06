import Vue from 'vue';
import { itemReview } from 'item-bank-test';
import Review from './review';

Vue.use(itemReview);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Review)
}).$mount('#app');