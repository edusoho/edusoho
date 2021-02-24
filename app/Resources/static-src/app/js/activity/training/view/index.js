import Axios from 'axios';
import ElementUI from 'element-ui';
import Info from './index.vue';
import qs from 'qs';

const axios = Axios.create({
  headers: {},
});

Vue.prototype.$axios = axios;
Vue.prototype.$qs = qs;
Vue.use(ElementUI);
let $app = $('#app');
new Vue({
  el: '#app',
  render: createElement => createElement(Info, {
    props: {
      info: $app.data("info"),
      bindInfo: $app.data("bindinfo")
    }
  })
});