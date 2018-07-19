import Vue from 'vue';
import axios from 'axios';
import router from '@/router';
import filters from '@/filters';
import utils from '@/utils';
import store from '@/store';
import plugins from '@/plugins';
import EdusohoUI from '@/components';

import 'vant/lib/vant-css/index.css';
import '@/assets/styles/main.scss';
import App from '@/App';
import Api from '@/api';

import {
  Button,
  NavBar,
  Tab,
  Tabs,
  Tabbar,
  TabbarItem,
  Swipe,
  SwipeItem,
  List,
  Field,
  Loading,
  Uploader
} from 'vant';
// 按需引入组件
Vue.component('van-nav-bar', NavBar);
Vue.component('van-tabbar', Tabbar);
Vue.component('van-tabbar-item', TabbarItem);
Vue.component('van-swipe', Swipe);
Vue.component('van-swipe-item', SwipeItem);
Vue.component('van-list', List);
Vue.component('van-button', Button);
Vue.component('van-tab', Tab);
Vue.component('van-tabs', Tabs);
Vue.component('van-field', Field);
Vue.component('van-loading', Loading);
Vue.component('van-uploader', Uploader);

Vue.use(filters);
Vue.use(plugins);
Vue.use(utils);
Vue.use(EdusohoUI);

Vue.config.productionTip = false;

/* eslint-disable no-new */

Api.getSettings({
  query: {
    type: 'wap'
  }
}).then(res => {
  if (!res.enabled) {
    // 如果没有开通微网校，则跳回老版本网校
    window.location.href = axios.defaults.baseURL || 'http://zyc.st.edusoho.cn/';
    return;
  }

  new Vue({
    el: '#app',
    router,
    store,
    components: { App },
    template: '<App/>'
  });
});

