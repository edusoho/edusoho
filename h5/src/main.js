import Vue from 'vue';
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
  Uploader,
  CouponCell,
  Popup
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
Vue.use(Popup);
Vue.use(plugins);
Vue.use(utils);
Vue.use(EdusohoUI);
Vue.use(CouponCell);

Vue.config.productionTip = false;

/* eslint-disable no-new */

Api.getSettings({
  query: {
    type: 'wap'
  }
}).then(res => {
  if (parseInt(res.version, 10) !== 2) {
    // 如果没有开通微网校，则跳回老版本网校 TODO
    const hashStr = location.hash;
    const getPathNameByHash = hash => {
      const hasQuery = hash.indexOf('?');
      if (hasQuery === -1) return hash.slice(1);
      return hash.match(/#.*\?/g)[0].slice(1, -1);
    };
    window.location.href = location.origin + getPathNameByHash(hashStr);
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

