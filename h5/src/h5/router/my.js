export default [
  {
    path: '/my/orders',
    name: 'my',
    meta: {
      i18n: true,
      title: 'title.my'
    },
    component: () => import(/* webpackChunkName: "my" */ '@/containers/my/index.vue')
  }
];
