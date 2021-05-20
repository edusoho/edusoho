import Vue from 'common/vue';
import Router from 'vue-router';
import routes from 'app/vue/router/teach/multi_class/index.js';

const router = new Router({
  mode: 'hash',
  routes
})

window.CKEDITOR_BASEPATH = app.basePath + '/static-dist/libs/es-ckeditor/';

new Vue({
  el: '#app',
  router,
  template: '<router-view></router-view>'
});

