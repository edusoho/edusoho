import Vue from 'vue';
import { itemImport } from 'item-bank-test';
import Import from './import';

Vue.use(itemImport);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Import)
}).$mount('#app');