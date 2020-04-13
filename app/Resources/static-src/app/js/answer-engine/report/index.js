import Vue from 'vue';
import itemBank from 'item-bank-test';
import Report from './report';

Vue.use(itemBank);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Report)
}).$mount('#app');