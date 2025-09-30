import AssessmentResult from './assessment-result';
import AssessmentResultComponent from 'common/vue/components/item-bank/assessment-result';

jQuery.support.cors = true;

Vue.config.productionTip = false;

Vue.component(AssessmentResultComponent.name, AssessmentResultComponent);

new Vue({
  render: createElement => createElement(AssessmentResult)
}).$mount('#app');