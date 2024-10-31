import Report from './report';
import ItemReport from 'common/vue/components/item-bank/item-report';
import { isMobileDevice } from 'common/utils';

jQuery.support.cors = true;

if (isMobileDevice()) {
  $('body, html').css({ 'height': '100%', 'overflow': 'auto' });
}

Vue.config.productionTip = false;

Vue.component(ItemReport.name, ItemReport);

new Vue({
  render: createElement => createElement(Report)
}).$mount('#app');