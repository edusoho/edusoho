// import Vue from 'vue';
// import { itemManage } from 'item-bank-test';
import Item from './item';

// Vue.use(itemManage);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(Item)
}).$mount('#app');
