import Vue from 'common/vue/index.js';
import router from '../../router/class-course/index.js';
import '../../mock/index'

new Vue({
  el: '#app',
  router,
  template: '<router-view></router-view>'
});

