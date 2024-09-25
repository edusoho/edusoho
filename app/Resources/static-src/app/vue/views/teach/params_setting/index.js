import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'Setting',
    component: () => import(/* webpackChunkName: "app/vue/dist/Setting" */ 'app/vue/views/teach/params_setting/index.vue')
  }
];

createVueApp('#app', routes);
