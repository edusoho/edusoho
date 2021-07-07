import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/',
    name: 'ClassroomManageWrongQuestion',
    component: () => import(/* webpackChunkName: "app/vue/dist/ClassroomManageWrongQuestion" */ 'app/vue/views/classroom_manage/wrong_question/index.vue')
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

