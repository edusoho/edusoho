import Vue from 'common/vue';
import Router from 'vue-router'
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const router = new Router({
  mode: 'hash',
  routes: [
    {
      path: '/',
      name: 'MultiClassProduct',
      component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassProduct" */ 'app/vue/views/teach/multi_class_product/index.vue')
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
})

