// import Vue from 'vue';
// import {itemReport} from 'item-bank-test';
import Report from './report';

// Vue.use(itemReport);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(Report)
}).$mount('#app');