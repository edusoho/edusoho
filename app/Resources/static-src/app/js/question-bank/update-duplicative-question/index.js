import UpdateDuplicativeQuestion from './index.vue';
import qs from 'qs';


Vue.prototype.$qs = qs;


Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

new Vue({
  el: '#app',
  render: createElement => createElement(UpdateDuplicativeQuestion)
})

