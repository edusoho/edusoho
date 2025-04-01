import Answer from './answer';
import {Popover} from '@codeages/design-vue';
import ItemEngine from 'common/vue/components/item-bank/item-engine';
import InspectionControl from 'common/vue/components/item-bank/inspection-control';
import { isMobileDevice } from 'common/utils';

jQuery.support.cors = true;

if (isMobileDevice()) {
  $('body, html').css({ 'height': '100%', 'overflow':'auto'});
}

Vue.config.productionTip = false;

Vue.component(ItemEngine.name, ItemEngine);
Vue.component(InspectionControl.name, InspectionControl);
Vue.use(Popover);

new Vue({
  render: createElement => createElement(Answer)
}).$mount('#app');
