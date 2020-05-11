// import Vue from 'vue';
// import { itemPreview } from 'item-bank-test';
import Item from './item';

// Vue.use(itemPreview);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(Item)
}).$mount('#app');
