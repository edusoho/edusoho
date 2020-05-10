// import Vue from 'vue';
// import { assessmentPreview } from 'item-bank-test';
import ShowAssessment from './show-assessment';

// Vue.use(assessmentPreview);

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(ShowAssessment)
}).$mount('#app');