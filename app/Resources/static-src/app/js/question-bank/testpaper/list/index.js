import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/list',
    name: 'list',
    component: () => import('app/js/question-bank/testpaper/list/list.vue')
  },
  {
    path: '/testPaperCreate',
    name: 'testPaperCreate',
    component: () => import( 'app/js/question-bank/testpaper/list/testCreate.vue')
  }
];
const router = new Router({
  routes
});

Vue.config.productionTip = false;

new Vue({
  el: '#app',
  components: {
    AntConfigProvider
  },
  router,
  template: '<ant-config-provider />'
});