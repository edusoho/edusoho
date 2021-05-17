import Vue from 'vue/dist/vue.esm.js';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'ClassCourse',
    component: () => import('../../../views/teach/class-course/views/index.vue')
  }
];

const router = new VueRouter({
  routes
});

export default router;
