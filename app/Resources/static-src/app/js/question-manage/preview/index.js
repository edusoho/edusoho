// import Vue from 'vue';
// import { itemPreview } from 'item-bank-test';
import Item from './item';

// Vue.use(itemPreview);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Item)
}).$mount('#app');
