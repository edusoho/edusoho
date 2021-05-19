export default [
  {
    path: '/',
    name: 'MultiClass',
    component: () => import('app/vue/views/teach/multi_class/index.vue')
  },
  {
    path: '/create_course',
    name: 'MultiClassCreateCourse',
    component: () => import('app/vue/views/teach/multi_class/create_course/index.vue')
  }
];
