export default [
  {
    path: '/my',
    name: 'my',
    meta: {
      i18n: true,
      hideTitle: true,
      title: 'title.me'
    },
    component: () => import(/* webpackChunkName: "my" */ '@/containers/my/index.vue')
  },
  {
    path: '/my/order',
    name: 'myOrder',
    meta: {
      i18n: true,
      title: 'title.myOrder'
    },
    component: () => import(/* webpackChunkName: "myOrder" */ '@/containers/order/orders.vue')
  },
  {
    path: '/my/activity',
    name: 'myActivity',
    meta: {
      i18n: true,
      title: 'title.myActivity'
    },
    component: () => import(/* webpackChunkName: "myActivity" */ '@/containers/my/activity/index.vue')
  }
];
