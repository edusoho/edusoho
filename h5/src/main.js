import Vue from 'vue';
import router from '@/router';
import filters from '@/filters';
// import utils from '@/utils';
import store from '@/store';
import plugins from '@/plugins';
import '@/components';
import Vant from 'vant';
import 'vant/lib/vant-css/index.css';
import '@/assets/styles/main.scss';

Vue.use(Vant);
Vue.use(filters);
Vue.use(plugins);

Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  template: '<router-view></router-view>',
});
