// import Vue from 'vue';
// import {itemReport} from 'item-bank-test';
import Report from './report';

// Vue.use(itemReport);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Report)
}).$mount('#app');