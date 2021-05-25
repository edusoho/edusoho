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
  template: `
    <keep-alive v-if="!$route.meta.isAlive">
      <router-view></router-view>
    </keep-alive>
    <router-view v-else></router-view>
  `
});

