export default [{
  path: 'my',
  name: 'my',
  meta: {
    title: '我的'
  },
  component: () => import(/* webpackChunkName: "my" */'@/containers/my/index.vue')
}];
