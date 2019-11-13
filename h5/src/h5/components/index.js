import EPanel from './panel';
import EFooter from './footer';
import ELoading from './loading';

export default {
  install(Vue) {
    Vue.component('e-panel', EPanel);
    Vue.component('e-footer', EFooter);
    Vue.component('e-loading', ELoading);
  }
};
