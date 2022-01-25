import Vue from 'common/vue';
import Router from 'vue-router';
import AntConfigProvider from 'app/vue/views/components/AntConfigProvider.vue';

const routes = [
  {
    path: '/',
    name: 'PurchaseAgreementSettings',
    component: () => import(/* webpackChunkName: "app/vue/dist/PurchaseAgreementSettings" */ 'app/vue/views/system/purchase_agreement/index.vue')
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

