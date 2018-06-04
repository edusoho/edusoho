export default [{
  path: '/learning',
  name: 'learning',
  meta: {
    title: '我的学习'
  },
  component: resolve => require(['@/containers/learning/index.vue'], resolve)
}];
