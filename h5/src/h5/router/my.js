export default [{
  path: '/my/orders',
  name: 'my',
  meta: {
    title: '我的'
  },
  component: () => import(/* webpackChunkName: "my" */'@/containers/my/index.vue')
}];
