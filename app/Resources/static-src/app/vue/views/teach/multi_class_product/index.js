import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'MultiClassProduct',
    component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassProduct" */ 'app/vue/views/teach/multi_class_product/index.vue')
  }
];

createVueApp('#app', routes);
