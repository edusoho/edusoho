export default [{
  path: '/my',
  name: 'my',
  component: resolve => require(['@/containers/my/index.vue'], resolve),
}];
