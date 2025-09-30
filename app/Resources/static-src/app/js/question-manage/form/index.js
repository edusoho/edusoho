import Item from './item';
import ItemManage from 'common/vue/components/item-bank/item-manage';

Vue.config.productionTip = false;

Vue.component(ItemManage.name, ItemManage);

new Vue({
  render: createElement => createElement(Item)
}).$mount('#app');
