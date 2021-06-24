import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const router = new Router({
  mode: 'hash',
  routes: [
    {
      path: '/',
      name: 'Assistant',
      component: () => import(/* webpackChunkName: "app/vue/dist/Assistant" */ 'app/vue/views/teach/assistant/index.vue')
    }
  ]
})

new Vue({
  el: '#app',
  components: {
    AntConfigProvider
  },
  router,
  template: '<ant-config-provider />'
});

