import Vue from 'vue';
import itemBank from 'item-bank-test';
import AssessmentResult from './assessment-result';

Vue.use(itemBank);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(AssessmentResult)
}).$mount('#app');