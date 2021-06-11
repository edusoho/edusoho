export default [
  {
    path: '/',
    name: 'Assistant',
    component: () => import(/* webpackChunkName: "app/vue/dist/Assistant" */ 'app/vue/views/teach/assistant/index.vue')
  }
];
