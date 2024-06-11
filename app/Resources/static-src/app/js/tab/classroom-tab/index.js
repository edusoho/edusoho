import Tab from './index.vue';
import { isMobileDevice } from 'common/utils';
import Axios from 'axios';
import Vue from 'common/vue';

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/vnd.edusoho.v2+json',
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
  },
});

Vue.prototype.$axios = axios;

jQuery.support.cors = true;

if (isMobileDevice()) {
  $('body, html').css({ 'height': '100%', 'overflow':'auto'});
}

Vue.config.productionTip = false;

new Vue({
  render: createElement => createElement(Tab)
}).$mount('#app');
