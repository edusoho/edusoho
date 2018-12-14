import Vue from 'vue';
import axios from 'axios';
import router from '@admin/router';
import utils from '@/utils';
import store from '@admin/store';

import 'vant/lib/swipe/style';
import 'vant/lib/swipe-cell/style';
import 'vant/lib/swipe-item/style';

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
  Tag,
  Autocomplete,
  Tooltip,
  Loading,
  Dropdown,
  DropdownItem,
  DropdownMenu
} from 'element-ui';

import {
  Swipe,
  SwipeItem,
} from 'vant';
// 按需引入组件
Vue.component('van-swipe', Swipe);
Vue.component('van-swipe-item', SwipeItem);

Vue.use(Loading);
Vue.use(Input);
Vue.use(Select);
Vue.use(Button);
Vue.use(Upload);
Vue.use(Radio);
Vue.use(Option);
Vue.use(Cascader);
Vue.use(Dialog);
Vue.use(Tag);
Vue.use(Autocomplete);
Vue.use(Tooltip);
Vue.use(utils);
Vue.use(Dropdown);
Vue.use(DropdownItem);
Vue.use(DropdownMenu);

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
