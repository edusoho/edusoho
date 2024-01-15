import MobileClassroom from './MobileClassroom';
import Vant from 'vant';

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}
Vue.use(Vant);

new Vue({
  render: createElement => createElement(MobileClassroom)
}).$mount('#app');

$('#free').on('click', function(event) {
  window.location.href = $(this).val();
});