import MobileCourse from './MobileCourse';
import Vant from 'vant';

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}
Vue.use(Vant);

new Vue({
  render: createElement => createElement(MobileCourse)
}).$mount('#app');


