// import Vue from 'vue';
// import { assessmentResult } from 'item-bank-test';
import AssessmentResult from './assessment-result';

// Vue.use(assessmentResult);

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(AssessmentResult)
}).$mount('#app');