import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/',
    name: 'TeacherCertificate',
    component: () => import(/* webpackChunkName: "app/vue/dist/TeacherCertificate" */ 'app/vue/views/teacher/certificate/index.vue')
  }
];

const router = new Router({
  mode: 'hash',
  routes
});

new Vue({
  el: '#app',
  components: {
    AntConfigProvider
  },
  router,
  template: '<ant-config-provider />'
});

