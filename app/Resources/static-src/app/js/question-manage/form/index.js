import Vue from 'vue';
import { itemManage } from 'item-bank-test';
import Item from './item';

Vue.use(itemManage);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Item)
}).$mount('#app');
