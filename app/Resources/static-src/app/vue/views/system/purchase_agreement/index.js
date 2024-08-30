import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'PurchaseAgreementSettings',
    component: () => import(/* webpackChunkName: "app/vue/dist/PurchaseAgreementSettings" */ 'app/vue/views/system/purchase_agreement/index.vue')
  }
];

createVueApp('#app', routes);
