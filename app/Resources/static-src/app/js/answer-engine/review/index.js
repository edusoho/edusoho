// import Vue from 'vue';
// import { itemReview } from 'item-bank-test';
import Review from './review';

// Vue.use(itemReview);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(Review)
}).$mount('#app');