import { createVueApp } from 'app/vue/utils/vue-creator';

const routes = [
  {
    path: '/',
    name: 'CloudResourcesLivePlayback',
    component: () => import(/* webpackChunkName: "app/vue/dist/CloudResources" */ 'app/vue/views/teach/cloud_resources/live_playback/index.vue')
  }
];

createVueApp('#app', routes);
