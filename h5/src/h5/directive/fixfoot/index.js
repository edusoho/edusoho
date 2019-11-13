import fixfoot from './fixFoot';

// eslint-disable-next-line func-names
const install = function (Vue) {
  Vue.directive('fixfoot', fixfoot);
};
if (window.Vue) {
  window.fixfoot = fixfoot;
  Vue.use(install); // eslint-disable-line
}
fixfoot.install = install;
export default fixfoot;
