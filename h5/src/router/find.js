export default [{
  path: '/',
  name: 'find',
  meta: {
    title: ''
  },
  component: () => import(/* webpackChunkName: "find" */'@/containers/find/index.vue')
}];
