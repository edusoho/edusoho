import UpdateDuplicativeQuestion from './index.vue';
import ItemManage from 'common/vue/components/item-bank/item-manage';
import qs from 'qs';

Vue.prototype.$qs = qs;

Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

Vue.component(ItemManage.name, ItemManage);

new Vue({
  el: '#app',
  render: createElement => createElement(UpdateDuplicativeQuestion)
});
