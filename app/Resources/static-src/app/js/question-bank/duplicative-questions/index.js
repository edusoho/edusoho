import DuplicativeQuestions from './index.vue';
import qs from 'qs';


Vue.prototype.$qs = qs;


Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

Vue.filter('stripTags', function(text) {
  return text.replace(/<[^>]*>/g, '');
});

new Vue({
  el: '#app',
  render: createElement => createElement(DuplicativeQuestions)
})

