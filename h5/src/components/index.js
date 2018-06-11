import EPanel from './panel';
import EFooter from './footer';

export default {
  install(Vue) {
    Vue.component('e-panel', EPanel);
    Vue.component('e-footer', EFooter);
  }
};
