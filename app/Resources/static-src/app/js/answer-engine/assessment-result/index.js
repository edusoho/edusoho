// import Vue from 'vue';
// import { assessmentResult } from 'item-bank-test';
import AssessmentResult from './assessment-result';

// Vue.use(assessmentResult);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(AssessmentResult)
}).$mount('#app');