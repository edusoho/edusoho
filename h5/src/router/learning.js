export default [{
  path: '/learning',
  name: 'learning',
  meta: {
    title: '我的学习'
  },
  component: () => import(/* webpackChunkName: "learning" */'@/containers/learning/index.vue')
}];
