import Vue from 'vue';
import axios from 'axios';
import router from '@admin/router';
import utils from '@/utils';
import store from '@admin/store';

import 'element-ui/lib/theme-chalk/index.css';
import '@admin/styles/main.scss';
import Admin from '@admin/App-admin';

import {
  Select,
  Button,
  Message,
  Upload,
  Input,
  Radio,
  Option,
  Cascader,
  Dialog,
  Autocomplete,
} from 'element-ui';

Vue.use(Input);
Vue.use(Select);
Vue.use(Button);
Vue.use(Upload);
Vue.use(Radio);
Vue.use(Option);
Vue.use(Cascader);
Vue.use(Dialog);
Vue.use(Autocomplete);

Vue.use(utils);

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
