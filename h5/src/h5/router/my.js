export default [
  {
    path: '/my/orders',
    name: 'my',
    meta: {
      i18n: true,
      title: 'title.me'
    },
    component: () => import(/* webpackChunkName: "my" */ '@/containers/my/index.vue')
  }
];
