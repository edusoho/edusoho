import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'MultiClassInspection',
    component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassInspection" */ 'app/vue/views/teach/multi_class_inspection/index.vue')
  }
];

createVueApp('#app', routes);
