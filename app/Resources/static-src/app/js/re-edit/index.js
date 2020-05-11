// import Vue from 'vue';
// import { itemImport } from 'item-bank-test';
import Import from './import';

// Vue.use(itemImport);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(Import)
}).$mount('#app');