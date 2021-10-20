import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/',
    name: 'ClassroomManageLiveStatistics',
    component: () => import(/* webpackChunkName: "app/vue/dist/ClassroomManageLiveStatistics" */ 'app/vue/views/classroom_manage/live_statistics/index.vue')
  },
  {
    path: '/details',
    name: 'ClassroomManageLiveStatisticsDetails',
    component: () => import(/* webpackChunkName: "app/vue/dist/ClassroomManageLiveStatistics" */ 'app/vue/views/classroom_manage/live_statistics/details/index.vue')
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

