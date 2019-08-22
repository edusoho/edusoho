import foot from './fixFoot.js';
import Vue from 'vue';

const install = function (Vue) {
  Vue.directive('foot', foot);
};
window.foot = foot;
Vue.use(foot);

foot.install = install;
export default foot;
