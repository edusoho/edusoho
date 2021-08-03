import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const router = new Router({
  mode: 'hash',
  routes: [
    {
      path: '/',
      name: 'Overview',
      component: () => import(/* webpackChunkName: "app/vue/dist/Overview" */ 'app/vue/views/teach/overview/index.vue')
    },
    {
      path: '/over_time',
      name: 'Overtime',
      component: () => import(/* webpackChunkName: "app/vue/dist/Overtime" */ 'app/vue/views/teach/overview/overtime/index.vue')
    }
  ]
})

new Vue({
  el: '#app',
  router,
  components: {
    AntConfigProvider
  },
  template: `<ant-config-provider />`
});

