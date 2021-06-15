export default [
  {
    path: '/',
    name: 'Teacher',
    component: () => import(/* webpackChunkName: "app/vue/dist/Teacher" */ 'app/vue/views/teach/teacher/index.vue')
  }
];
