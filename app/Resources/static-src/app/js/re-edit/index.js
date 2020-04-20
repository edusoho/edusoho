import Vue from 'vue';
import itemBank from 'item-bank-test';
import Import from './import';

Vue.use(itemBank);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Import)
}).$mount('#app');