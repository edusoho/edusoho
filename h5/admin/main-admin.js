import Vue from 'vue';
import axios from 'axios';
import router from '@admin/router';
import utils from '@/utils';
import store from '@admin/store';
import EdusohoUI from '@/components';

import '@admin/styles/main.scss';
import Admin from '@admin/App-admin';
// import filters from '@/filters';
// import VueCropper from 'vue-cropper';


Vue.use(utils);
Vue.use(EdusohoUI);
// Vue.use(filters);
// Vue.use(VueCropper);

Vue.config.productionTip = false;

/* eslint-disable no-new */

new Vue({
  el: '#app-admin',
  router,
  store,
  components: { Admin },
  template: '<Admin/>'
});
