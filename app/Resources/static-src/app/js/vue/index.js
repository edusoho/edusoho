import Vue from 'vue/dist/vue.esm.js';
const App = require('./App.vue');
import app from './test.vue';
Vue.config.productionTip = false;

new Vue({
  render: h => h(App),
}).$mount('#app');