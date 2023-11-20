import ListHandel from './index.vue';
import Axios from 'axios';
import Vue from 'common/vue';
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

new Vue({
  el: '#app',
  render: createElement => createElement(ListHandel)
})