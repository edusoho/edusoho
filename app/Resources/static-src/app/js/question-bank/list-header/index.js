import ListHandel from './index.vue';
import { Button, Dropdown, Select, FormModel } from 'ant-design-vue';
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

Vue.use(Button)
Vue.use(Select)
Vue.use(Dropdown)
Vue.use(FormModel)
new Vue({
  el: '#app',
  render: createElement => createElement(ListHandel)
})