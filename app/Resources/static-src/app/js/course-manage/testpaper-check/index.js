import Header from './index.vue';
import { Button, Input, TreeSelect, FormModel } from 'ant-design-vue';
import Axios from 'axios';
import qs from 'qs';

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/vnd.edusoho.v2+json',
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
  },
});


Vue.prototype.$axios = axios;
Vue.prototype.$qs = qs;

Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});

Vue.use(Input)
Vue.use(Button)
Vue.use(FormModel)
Vue.use(TreeSelect)

new Vue({
  el: '#app',
  render: createElement => createElement(Header)
})