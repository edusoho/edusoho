import Course from './course';
import EPanel from './panel';

export default {
  install(Vue) {
    Vue.component('e-course', Course);
    Vue.component('e-panel', EPanel);
  }
};
