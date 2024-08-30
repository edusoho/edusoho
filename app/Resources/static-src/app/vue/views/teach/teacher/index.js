import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'Teacher',
    component: () => import(/* webpackChunkName: "app/vue/dist/Teacher" */ 'app/vue/views/teach/teacher/index.vue')
  }
];

createVueApp('#app', routes);
