import Vue from 'vue';
import axios from 'axios';
import router from '@admin/router';
import utils from '@/utils';
import store from '@admin/store';

import 'element-ui/lib/theme-chalk/index.css';
import '@admin/styles/main.scss';
import Admin from '@admin/App-admin';
// import VueCropper from 'vue-cropper';

import {
  Button,
  // Select,
  Message,
  Upload,
  Input
} from 'element-ui';

Vue.component(Input.name, Input);
Vue.component(Button.name, Button);
// Vue.component(Select.name, Select);
Vue.use(Upload);
Vue.use(utils);
// Vue.use(VueCropper);

Vue.prototype.$message = Message;

Vue.config.productionTip = false;

/* eslint-disable no-new */

new Vue({
  el: '#app-admin',
  router,
  store,
  components: { Admin },
  template: '<Admin/>'
});
