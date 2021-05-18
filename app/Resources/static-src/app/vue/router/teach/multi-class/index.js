import Vue from 'common/vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'ClassCourse',
    component: () => import('app/vue/views/teach/multi-class/index.vue')
  }
];

const router = new VueRouter({
  routes
});

export default router;
