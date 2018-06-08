import Vue from 'vue';
import router from '@/router';
import filters from '@/filters';
import utils from '@/utils';
import store from '@/store';
import plugins from '@/plugins';
import EdusohoUI from '@/components';
import Vant from 'vant';
import 'vant/lib/vant-css/index.css';
import '@/assets/styles/main.scss';
import App from '@/App';

Vue.use(Vant);
Vue.use(filters);
Vue.use(plugins);
Vue.use(utils);
Vue.use(EdusohoUI);

Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>'
});
