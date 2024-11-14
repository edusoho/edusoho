import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'Overview',
    component: () => import(/* webpackChunkName: "app/vue/dist/Overview" */ 'app/vue/views/teach/overview/index.vue')
  },
  {
    path: '/over_time',
    name: 'Overtime',
    component: () => import(/* webpackChunkName: "app/vue/dist/Overtime" */ 'app/vue/views/teach/overview/overtime/index.vue')
  },
  {
    path: '/inspection',
    name: 'MultiClassInspection',
    component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassInspection" */ 'app/vue/views/teach/multi_class_inspection/index.vue')
  }
];

createVueApp('#app', routes);
