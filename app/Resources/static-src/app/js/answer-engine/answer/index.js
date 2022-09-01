import Answer from './answer';
import { isMobileDevice } from 'common/utils';
import { Modal } from 'ant-design-vue';

jQuery.support.cors = true;

if (isMobileDevice()) {
  $('body, html').css({ 'height': '100%', 'overflow':'auto'});
}

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

Vue.use(Modal);

new Vue({
  render: createElement => createElement(Answer)
}).$mount('#app');
