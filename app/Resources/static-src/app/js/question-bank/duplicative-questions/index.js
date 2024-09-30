import DuplicativeQuestions from './index.vue';
import ItemPreview from 'common/vue/components/item-bank/item-preview';
import qs from 'qs';

Vue.prototype.$qs = qs;

Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

Vue.filter('stripTags', function(text) {
  return text.replace(/<[^>]*>/g, '');
});

Vue.component(ItemPreview.name, ItemPreview);

new Vue({
  el: '#app',
  render: createElement => createElement(DuplicativeQuestions)
});
