import Vue from 'vue';
import router from '@/router';
import filters from '@/filters';
import utils from '@/utils';
import store from '@/store';
import plugins from '@/plugins';
import EdusohoUI from '@/components';
import whiteList from '@/router/config/white-list';

import '@/assets/styles/main.scss';
import App from '@/App';
import Api from '@/api';
import {
  Row,
  Col,
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
  Uploader,
  Popup,
  Rate,
  Cell,
  Tag,
  Toast,
  Lazyload
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
Vue.component('van-uploader', Uploader);
Vue.component('van-rate', Rate);
Vue.component('van-cell', Cell);

Vue.use(filters);
Vue.use(Row);
Vue.use(Col);
Vue.use(Tag);
Vue.use(Popup);
Vue.use(plugins);
Vue.use(utils);
Vue.use(EdusohoUI);
Vue.use(Lazyload);
Vue.use(Toast);
Vue.config.productionTip = false;

/* eslint-disable no-new */

Api.getSettings({
  query: {
    type: 'wap'
  }
}).then(res => {
  const hashStr = location.hash;
  const getPathNameByHash = hash => {
    const hasQuery = hash.indexOf('?');
    if (hasQuery === -1) return hash.slice(1);
    return hash.match(/#.*\?/g)[0].slice(1, -1);
  };

  // 获取指定参数
  const GetUrlParam = paraName => {
    const url = document.location.toString();
    const arrObj = url.split('?');
    if (arrObj.length > 1) {
      const arrPara = arrObj[1].split('&');
      let arr;
      for (let i = 0; i < arrPara.length; i += 1) {
        arr = arrPara[i].split('=');
        if (arr != null && arr[0] === paraName) {
          return arr[1];
        }
      }
      return '';
    }
    return '';
  };

  const isWhiteList = whiteList.includes(getPathNameByHash(hashStr));

  const hashParamArray = getPathNameByHash(hashStr).split('/');
  const hashHasToken = hashParamArray.includes('loginToken');
  const courseId = hashParamArray[hashParamArray.indexOf('course') + 1];

  if (hashHasToken) {
    const tokenIndex = hashParamArray.indexOf('loginToken');
    const tokenFromUrl = hashParamArray[tokenIndex + 1];
    store.state.token = tokenFromUrl;
    localStorage.setItem('token', tokenFromUrl);
    if (courseId) {
      window.location.href = `${location.origin}/h5/index.html#/course/${courseId}?backUrl=%2F`;
    }
  }

  const hasToken = window.localStorage.getItem('token');
  if (!hasToken && Number(GetUrlParam('needLogin'))) {
    window.location.href = `${location.origin}/h5/index.html#/login?redirect=/course/${
      courseId}&backUrl=%2F&account=${GetUrlParam('account')}`;
  }

  // 已登录状态直接跳转详情页
  if (hasToken && Number(GetUrlParam('needLogin'))) {
    window.location.href = `${location.href}&backUrl=%2F`;
  }

  if (!isWhiteList) {
    if (parseInt(res.version, 10) !== 2) {
      // 如果没有开通微网校，则跳回老版本网校 TODO
      window.location.href = location.origin + getPathNameByHash(hashStr);
      return;
    }
  }

  new Vue({
    el: '#app',
    router,
    store,
    components: { App },
    template: '<App/>'
  });
}).catch(err => {
  console.log(err.message);
});

