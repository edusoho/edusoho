export default [
  {
    path: '/',
    name: 'MultiClassProduct',
    component: () => import(/* webpackChunkName: "app/vue/dist/MultiClassProduct" */ 'app/vue/views/teach/multi_class_product/index.vue')
  }
];
