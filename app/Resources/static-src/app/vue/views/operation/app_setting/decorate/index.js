import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'AppDecorate',
    component: () => import(/* webpackChunkName: "app/vue/dist/AppSetting" */ 'app/vue/views/operation/app_setting/decorate/index.vue')
  }
];

createVueApp('#app', routes);
