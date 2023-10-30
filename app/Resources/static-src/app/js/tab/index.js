import Tab from './Tab.vue';
import { isMobileDevice } from 'common/utils';
import Axios from 'axios';

Vue.prototype.$axios = Axios;

jQuery.support.cors = true;

if (isMobileDevice()) {
  $('body, html').css({ 'height': '100%', 'overflow':'auto'});
}

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

new Vue({
  render: createElement => createElement(Tab)
}).$mount('#app');
