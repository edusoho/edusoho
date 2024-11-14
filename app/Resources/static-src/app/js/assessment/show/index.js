import ShowAssessment from './show-assessment';
import AssessmentPreview from 'common/vue/components/item-bank/assessment-preview';

Vue.config.productionTip = false;

Vue.component(AssessmentPreview.name, AssessmentPreview);

new Vue({
  render: createElement => createElement(ShowAssessment)
}).$mount('#app');