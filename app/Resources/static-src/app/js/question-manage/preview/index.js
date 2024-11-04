import Item from './item';
import ItemPreview from 'common/vue/components/item-bank/item-preview';
import { Browser } from 'common/utils';
import 'app/common/katex-render';

Vue.config.productionTip = false;

Vue.component(ItemPreview.name, ItemPreview);

new Vue({
  render: createElement => createElement(Item)
}).$mount('#app');


if (Browser.ie || Browser.ie10|| Browser.ie11 || Browser.edge) {
  $('.modal').on('hide.bs.modal', function() {
    window.location.reload();
  });
}
