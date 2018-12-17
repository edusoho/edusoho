export default [{
  path: '/',
  name: 'find',
  meta: {
    title: '',
    index: 0,
    keepAlive: true
  },
  component: () => import(/* webpackChunkName: "find" */'@/containers/find/index.vue')
}];
