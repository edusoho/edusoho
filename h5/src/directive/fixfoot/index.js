import fixfoot from './fixFoot.js';

const install = function (Vue) {
  Vue.directive('fixfoot', fixfoot);
};
if (window.Vue) {
  window.fixfoot = fixfoot;
  Vue.use(install); // eslint-disable-line
}
fixfoot.install = install;
export default fixfoot;
