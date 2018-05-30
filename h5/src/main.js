import Vue from 'vue';
import Mint from 'mint-ui';
import App from '@/App';
import router from '@/router';
import filters from '@/filters';
// import utils from '@/utils';
import store from '@/store';
import plugins from '@/plugins';
import '@/components';

Vue.use(filters);
Vue.use(plugins);
Vue.use(Mint);

Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>',
});
