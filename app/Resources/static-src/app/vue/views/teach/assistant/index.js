import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'Assistant',
    component: () => import(/* webpackChunkName: "app/vue/dist/Assistant" */ 'app/vue/views/teach/assistant/index.vue')
  }
];

createVueApp('#app', routes);
