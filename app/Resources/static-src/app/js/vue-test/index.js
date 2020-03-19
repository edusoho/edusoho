import Vue from 'vue';
import itemBank from 'item-bank-test';
Vue.use(itemBank);
import App from './test.vue';

Vue.config.productionTip = false;
new Vue({
  render: h => h(App)
}).$mount('#app');