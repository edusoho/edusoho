import Vue from 'vue';
import itemBank from 'item-bank-test';
import Item from './item';

Vue.use(itemBank);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Item)
}).$mount('#app');
