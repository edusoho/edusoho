import Vue from 'vue';
import itemBank from 'item-bank-test';
import ShowAssessment from './show-assessment';

Vue.use(itemBank);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(ShowAssessment)
}).$mount('#app');