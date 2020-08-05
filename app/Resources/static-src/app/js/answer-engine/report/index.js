import Report from './report';
import { isMobileDevice } from 'common/utils';

jQuery.support.cors = true;

if (isMobileDevice()) {
  $('body, html').css({ 'height': '100%', 'overflow': 'auto' });
}

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}


new Vue({
  render: createElement => createElement(Report)
}).$mount('#app');