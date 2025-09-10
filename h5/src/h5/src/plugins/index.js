import Vue from "vue";
import VueAwesomeSwiper from "vue-awesome-swiper";
import "../styles/iconfont/font.scss";
import "../styles/index.scss";
import {
  Checkbox,
  CheckboxGroup,
  Radio,
  RadioGroup,
  Toast,
  Dialog,
  Field,
  Popup,
  CountDown,
  Collapse,
  CollapseItem,
  Loading
} from "vant";
Vue.component("van-checkbox", Checkbox);
Vue.component("van-checkbox-group", CheckboxGroup);
Vue.component("van-radio", Radio);
Vue.component("van-radio-group", RadioGroup);
Vue.component("van-field", Field);
Vue.component("van-popup", Popup);
Vue.component("van-count-down", CountDown);
Vue.component("van-collapse", Collapse);
Vue.component("van-collapse-item", CollapseItem);
Vue.component("van-loading", Loading);
Vue.use(VueAwesomeSwiper /* { default options with global component } */);
Vue.use(Toast).use(Dialog);
