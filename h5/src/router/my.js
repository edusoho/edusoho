export default [{
  path: '/my/orders',
  name: 'my',
  meta: {
    title: '我的',
    index: 3,
    keepAlive: true
  },
  component: () => import(/* webpackChunkName: "my" */'@/containers/my/index.vue')
}];
