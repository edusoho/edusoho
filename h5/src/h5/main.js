import Vue from 'vue';
import router from '@/router';
import filters from '@/filters';
import utils from '@/utils';
import store from '@/store';
import i18n from '@/lang';
import Cookies from 'js-cookie';
import plugins from '@/plugins';
import EdusohoUI from '@/components';
import '@/assets/styles/main.scss';
import '@/assets/styles/tailwind.css';
import App from '@/App';
import Api from '@/api';
import VueClipboard from 'vue-clipboard2';
import wapSdk from 'wap-sdk';
import moment from 'moment';
import {
  Row,
  Col,
  Button,
  NavBar,
  Tab,
  Tabs,
  Tabbar,
  Dialog,
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
  Lazyload,
  Checkbox,
  CheckboxGroup,
  Radio,
  RadioGroup,
  Panel,
  ActionSheet,
  Switch,
  Loading,
  PullRefresh,
  Overlay,
  Search,
  CountDown,
  Form,
  Area,
  DatetimePicker,
  Picker,
  Icon,
  DropdownMenu,
  DropdownItem,
  Divider,
  Empty,
  CellGroup,
  Cascader,
  TreeSelect,
  Image,
  Progress,
  NoticeBar,
} from 'vant';
import { handleCourse, handleSite, handleStorage, handleUgc, handleVip, handleWap, handleGoods } from "./handleSettings";
// 按需引入组件
Vue.component('van-nav-bar', NavBar);
Vue.component('van-tabbar', Tabbar);
Vue.component('van-tabbar-item', TabbarItem);
Vue.component('van-swipe', Swipe);
Vue.component('van-swipe-item', SwipeItem);
Vue.component('van-list', List);
Vue.component('van-button', Button);
Vue.component('van-dialog', Dialog);
Vue.component('van-tab', Tab);
Vue.component('van-tabs', Tabs);
Vue.component('van-field', Field);
Vue.component('van-uploader', Uploader);
Vue.component('van-rate', Rate);
Vue.component('van-cell', Cell);
Vue.component('van-checkbox', Checkbox);
Vue.component('van-checkbox-group', CheckboxGroup);
Vue.component('van-radio', Radio);
Vue.component('van-radio-group', RadioGroup);
Vue.component('van-panel', Panel);
Vue.component('van-pull-refresh', PullRefresh);
Vue.component('van-overlay', Overlay);
Vue.component('van-search', Search);
Vue.component('van-count-down', CountDown);
Vue.component('van-divider', Divider);
Vue.component('van-cell-group', CellGroup);
Vue.component('van-cascader', Cascader);
Vue.component('van-tree-select', TreeSelect);
Vue.component('van-image', Image);
Vue.component('van-progress', Progress);
Vue.component('van-notice-bar', NoticeBar);

Vue.use(ActionSheet);
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
Vue.use(Checkbox);
Vue.use(CheckboxGroup);
Vue.use(Radio);
Vue.use(RadioGroup);
Vue.use(Panel);
Vue.use(Tab)
  .use(Tabs)
  .use(Dialog)
  .use(Switch)
  .use(PullRefresh)
  .use(Loading)
  .use(Form)
  .use(Area)
  .use(DatetimePicker)
  .use(Picker);
Vue.use(VueClipboard);
Vue.use(Icon);
Vue.use(DropdownMenu);
Vue.use(DropdownItem);
Vue.use(wapSdk);
Vue.use(Empty);
Vue.use(Cascader);
Vue.use(TreeSelect);
Vue.config.productionTip = false;

Vue.prototype.$moment = moment;
Vue.prototype.$cookie = Cookies;
Vue.prototype.$version = require('../../package.json').version;
Vue.config.ignoredElements = ['wx-open-subscribe'];

Api.getAllSettings({
  params: {
    types: ['wap', 'site', 'ugc', 'locale', 'storage', 'vip', 'course', 'goods']
  }
}).then(async (res) => {
  handleSite(res.site)
  handleUgc(res.ugc)
  handleStorage(res.storage)
  handleVip(res.vip)
  handleCourse(res.course)
  handleGoods(res.goods)
  await Promise.all([
    store.dispatch('setDrpSwitch'),
    store.dispatch('setCouponSwitch'),
    handleWap(res.wap)
  ])

  new Vue({
    router,
    store,
    i18n,
    render: h => h(App),
  }).$mount('#app');
})

