export default [{
  path: 'my',
  name: 'my',
  meta: {
    title: '我的'
  },
  component: resolve => require(['@/containers/my/index.vue'], resolve)
}];
