import Vue from 'vue/dist/vue.esm.js';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'ClassCourse',
    component: () => import('../views/class-course/index.vue')
  }
];

const router = new VueRouter({
  routes
});

export default router;