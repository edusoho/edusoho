export default [{
  path: 'find',
  name: 'find',
  meta: {
    title: ''
  },
  component: () => import(/* webpackChunkName: "find" */'@/containers/find/index.vue')
}];
