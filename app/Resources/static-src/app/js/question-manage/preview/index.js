import Item from './item';
import { Browser } from 'common/utils';

Vue.config.productionTip = false;
if (app.lang == 'en') {
  const locale = local.default;
  itemBank.default.install(Vue, {locale});
}

new Vue({
  render: createElement => createElement(Item)
}).$mount('#app');


if (Browser.ie || Browser.ie10|| Browser.ie11 || Browser.edge) {
  $('.modal').on('hide.bs.modal', function() {
    window.location.reload();
  });
}
