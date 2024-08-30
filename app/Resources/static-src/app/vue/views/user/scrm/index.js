import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'UserScrm',
    component: () => import(/* webpackChunkName: "app/vue/dist/UserScrm" */ 'app/vue/views/user/scrm/index.vue')
  }
];

createVueApp('#app', routes);
