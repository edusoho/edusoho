export default [{
  path: '/my/courses/learning',
  name: 'learning',
  meta: {
    title: '我的学习',
    index: 2,
    keepAlive: true
  },
  component: () => import(/* webpackChunkName: "learning" */'@/containers/learning/index.vue')
}];
