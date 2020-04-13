import Vue from 'vue';
import itemBank from 'item-bank-test';
import Answer from './answer';

Vue.use(itemBank);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Answer)
}).$mount('#app');