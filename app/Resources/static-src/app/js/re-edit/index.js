import Import from './import';
import ItemImport from 'common/vue/components/item-bank/item-import';

Vue.config.productionTip = false;

Vue.component(ItemImport.name, ItemImport);

new Vue({
  render: createElement => createElement(Import)
}).$mount('#app');