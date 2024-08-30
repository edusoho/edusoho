import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'AppSetting',
    component: () => import(/* webpackChunkName: "app/vue/dist/AppSetting" */ 'app/vue/views/operation/app_setting/index.vue')
  }
];

createVueApp('#app', routes);
