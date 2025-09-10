export default [
  {
    path: '/my/courses/learning',
    name: 'learning',
    meta: {
      i18n: true,
      title: 'learning.title',
    },
    component: () =>
      import(
        /* webpackChunkName: "learning" */ '@/containers/learning/index.vue'
      ),
  },
];
