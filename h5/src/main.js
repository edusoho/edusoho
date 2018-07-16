import Vue from 'vue';
import router from '@/router';
import filters from '@/filters';
import utils from '@/utils';
import store from '@/store';
import plugins from '@/plugins';
import EdusohoUI from '@/components';
// import Vant from 'vant';
import 'vant/lib/vant-css/index.css';
import '@/assets/styles/main.scss';
import App from '@/App';

// Vue.use(Vant);
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
new Vue({
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>'
});
