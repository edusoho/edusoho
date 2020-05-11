// import Vue from 'vue';
// import { itemEngine, inspectionControl} from 'item-bank-test';
import Answer from './answer';

// Vue.use(itemEngine);
// Vue.use(inspectionControl);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

new Vue({
  render: createElement => createElement(Answer)
}).$mount('#app');